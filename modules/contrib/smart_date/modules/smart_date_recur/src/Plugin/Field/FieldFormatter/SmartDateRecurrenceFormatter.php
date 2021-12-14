<?php

namespace Drupal\smart_date_recur\Plugin\Field\FieldFormatter;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\smart_date\Entity\SmartDateFormat;
use Drupal\smart_date\Plugin\Field\FieldFormatter\SmartDateDefaultFormatter;
use Drupal\smart_date\SmartDateTrait;
use Drupal\smart_date_recur\Entity\SmartDateRule;
use Drupal\smart_date_recur\SmartDateRecurTrait;

/**
 * Plugin for a recurrence-optimized formatter for 'smartdate' fields.
 *
 * This formatter renders the start time range using <time> elements, with
 * recurring dates given special formatting.
 *
 * @FieldFormatter(
 *   id = "smartdate_recurring",
 *   label = @Translation("Recurring"),
 *   field_types = {
 *     "smartdate"
 *   }
 * )
 */
class SmartDateRecurrenceFormatter extends SmartDateDefaultFormatter {

  use SmartDateTrait;
  use SmartDateRecurTrait;

  /**
   * The parent entity on which the dates exist.
   *
   * @var mixed
   */
  protected $entity;

  /**
   * The configuration, particularly for the augmenters.
   *
   * @var array
   */
  protected $sharedSettings = [];

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'past_display' => '2',
      'upcoming_display' => '2',
      'show_next' => FALSE,
      'current_upcoming' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // Use the upstream settings form, which gives us a control to override the
    // timezone.
    $form = parent::settingsForm($form, $form_state);

    // Ask the user to choose how many past and upcoming instances to display.
    $form['past_display'] = [
      '#type' => 'number',
      '#title' => $this->t('Recent Instances'),
      '#description' => $this->t('Specify how many recent instances to display'),
      '#default_value' => $this->getSetting('past_display'),
    ];

    $form['upcoming_display'] = [
      '#type' => 'number',
      '#title' => $this->t('Upcoming Instances'),
      '#description' => $this->t('Specify how many upcoming instances to display'),
      '#default_value' => $this->getSetting('upcoming_display'),
    ];

    $form['show_next'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show next instance separately'),
      '#description' => $this->t('Isolate the next instance to make it more obvious'),
      '#default_value' => $this->getSetting('show_next'),
      '#states' => [
        // Show this option only if at least one upcoming value will be shown.
        'invisible' => [
          [':input[name$="[settings_edit_form][settings][upcoming_display]"]' => ['filled' => FALSE]],
          [':input[name$="[settings_edit_form][settings][upcoming_display]"]' => ['value' => '0']],
        ],
      ],
    ];

    $form['current_upcoming'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Treat current events as upcoming'),
      '#description' => $this->t('Otherwise, they will be treated as being in the past.'),
      '#default_value' => $this->getSetting('current_upcoming'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->getSetting('timezone_override') === ''
      ? $this->t('No timezone override.')
      : $this->t('Timezone overridden to %timezone.', [
        '%timezone' => $this->getSetting('timezone_override'),
      ]);

    $summary[] = $this->t('Smart date format: %format.', [
      '%format' => $this->getSetting('format'),
    ]);

    return $summary;
  }

  /**
   * Explicitly declare support for the Date Augmenter API.
   *
   * @return array
   *   The keys and labels for the sets of configuration.
   */
  public function supportsDateAugmenter() {
    // Return an array of configuration sets to use.
    return [
      'instances' => $this->t('Individual Dates'),
      'rule' => $this->t('Recurring Rule'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    // TODO: intellident switching between retrieval methods
    // Look for a defined format and use it if specified.
    $timezone_override = $this->getSetting('timezone_override') ?: NULL;
    $format_label = $this->getSetting('format');
    $add_classes = $this->getSetting('add_classes');
    $time_wrapper = $this->getSetting('time_wrapper');
    if ($format_label) {
      $format = SmartDateFormat::load($format_label);
      $settings = $format->getOptions();
    }
    else {
      $settings = [
        'separator' => $this->getSetting('separator'),
        'join' => $this->getSetting('join'),
        'time_format' => $this->getSetting('time_format'),
        'time_hour_format' => $this->getSetting('time_hour_format'),
        'date_format' => $this->getSetting('date_format'),
        'date_first' => $this->getSetting('date_first'),
        'ampm_reduce' => $this->getSetting('ampm_reduce'),
        'allday_label' => $this->getSetting('allday_label'),
      ];
    }

    // Look for the Date Augmenter plugin manager service.
    $instance_augmenters = [];
    $rule_augmenters = [];
    if (!empty(\Drupal::hasService('plugin.manager.dateaugmenter'))) {
      $dateAugmenterManager = \Drupal::service('plugin.manager.dateaugmenter');
      // TODO: Support custom entities.
      $config = $this->getThirdPartySettings('date_augmenter');
      $this->sharedSettings = $config;
      $instance_augmenters = $dateAugmenterManager->getActivePlugins($config['instances']);
      $rule_augmenters = $dateAugmenterManager->getActivePlugins($config['rule']);
      $this->entity = $items->getEntity();
    }

    $rrules = [];
    foreach ($items as $delta => $item) {
      $timezone = $item->timezone ? $item->timezone : $timezone_override;
      if (empty($item->value) || empty($item->end_value)) {
        continue;
      }
      if (empty($item->rrule)) {
        // No rule so include the item directly.
        $elements[$delta] = static::formatSmartDate($item->value, $item->end_value, $settings, $timezone);
        if ($add_classes) {
          $this->addRangeClasses($elements[$delta]);
        }
        if ($time_wrapper) {
          $this->addTimeWrapper($elements[$delta], $item->value, $item->end_value, $timezone);
        }
        if ($instance_augmenters) {
          $this->augmentOutput($elements[$delta], $instance_augmenters, $item->value, $item->end_value, $item->timezone, $delta);
        }
      }
      else {
        // Uses a rule, so use a placeholder instead.
        if (!isset($rrules[$item->rrule])) {
          $elements[$delta] = $item->rrule;
          $rrules[$item->rrule]['delta'] = $delta;
        }
        // Add this instance to our array of instances for the rule.
        $rrules[$item->rrule]['instances'][] = $item;
      }
    }
    foreach ($rrules as $rrid => $rrule_collected) {
      $rrule_output = [
        '#theme' => 'smart_date_recurring_formatter',
      ];
      $instances = $rrule_collected['instances'];
      if (empty($instances)) {
        continue;
      }
      $delta = $rrule_collected['delta'];
      // Retrieve the text of the rrule.
      $rrule = SmartDateRule::load($rrid);
      if (empty($rrule)) {
        continue;
      }
      $rrule_output['#rule_text']['rule'] = $rrule->getTextRule();
      if ($rule_augmenters) {
        $repeats = $rrule->getRule();
        $start = $instances[0]->getValue();
        // Grab the end value of the last instance.
        $ends = $instances[array_key_last($instances)]->getValue()['end_value'];
        $this->augmentOutput($rrule_output['#rule_text'], $rule_augmenters, $start['value'], $start['end_value'], $start['timezone'], $delta, $repeats, $ends, 'rule');
      }

      // Get the specified number of past instances.
      $past_display = $this->getSetting('past_display');

      if (in_array($rrule->get('freq')->getString(), ['MINUTELY', 'HOURLY'])) {
        $within_day = TRUE;
      }
      else {
        $within_day = FALSE;
      }

      if ($within_day) {
        // Output for dates recurring within a day.
        // Group the instances into days first.
        $instance_dates = [];
        $instances_nested = [];
        $comparison_date = 'Ymd';
        $comparison_format = $this->settingsFormatNoTime($settings);
        $comparison_format['date_format'] = $comparison_date;
        // Group instances into days, make array of dates.
        foreach ($instances as $instance) {
          $this_comparison_date = static::formatSmartDate($instance->value, $instance->end_value, $comparison_format, $timezone, 'string');
          $instance_dates[$this_comparison_date] = (int) $this_comparison_date;
          $instances_nested[$this_comparison_date][] = $instance;
        }
        $instances = array_values($instances_nested);
        $next_index = $this->findNextInstanceByDay(array_values($instance_dates), (int) date($comparison_date));
      }
      else {
        // Output for other recurrences frequencies.
        // Find the 'next' instance after now.
        $next_index = $this->findNextInstance($instances);
      }

      // Display past instances if set and at least one instances in the past.
      if ($past_display && $next_index) {
        if ($next_index == -1) {
          $begin = count($instances) - $past_display;
        }
        else {
          $begin = $next_index - $past_display;
        }
        if ($begin < 0) {
          $begin = 0;
        }
        $past_instances = array_slice($instances, $begin, $past_display, TRUE);
        $rrule_output['#past_display'] = [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
        ];
        if ($within_day) {
          $items = $this->formatWithinDay($past_instances, $settings);
        }
        else {
          $items = [];
          foreach ($past_instances as $key => $item) {
            $items[$key] = static::formatSmartDate($item->value, $item->end_value, $settings, $item->timezone);
            if ($add_classes) {
              $this->addRangeClasses($items[$key]);
            }
            if ($time_wrapper) {
              $this->addTimeWrapper($items[$key], $item->value, $item->end_value, $item->timezone);
            }
            if ($instance_augmenters) {
              $this->augmentOutput($items[$key], $instance_augmenters, $item->value, $item->end_value, $item->timezone, $key);
            }
          }
        }
        foreach ($items as $item) {
          $rrule_output['#past_display']['#items'][] = [
            '#children' => $item,
            '#theme' => 'container',
          ];
        }
      }
      $upcoming_display = $this->getSetting('upcoming_display');
      // Display upcoming instances if set and at least one instance upcoming.
      if ($upcoming_display && $next_index < count($instances) && $next_index != -1) {
        $upcoming_instances = array_slice($instances, $next_index, $upcoming_display, TRUE);
        $rrule_output['#upcoming_display'] = [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
        ];
        if ($within_day) {
          $items = $this->formatWithinDay($upcoming_instances, $settings);
        }
        else {
          $items = [];
          foreach ($upcoming_instances as $key => $item) {
            $items[$key] = static::formatSmartDate($item->value, $item->end_value, $settings, $item->timezone);
            if ($add_classes) {
              $this->addRangeClasses($items[$key]);
            }
            if ($time_wrapper) {
              $this->addTimeWrapper($items[$key], $item->value, $item->end_value, $item->timezone);
            }
            if ($instance_augmenters) {
              $this->augmentOutput($items[$key], $instance_augmenters, $item->value, $item->end_value, $item->timezone, $key);
            }
          }
        }
        foreach ($items as $item) {
          $rrule_output['#upcoming_display']['#items'][] = [
            '#children' => $item,
            '#theme' => 'container',
          ];
        }
        if ($this->getSetting('show_next')) {
          $rrule_output['#next_display'] = array_shift($rrule_output['#upcoming_display']['#items']);
        }
      }
      $elements[$delta] = $rrule_output;
    }

    return $elements;
  }

  /**
   * Helper function to find the next instance from now in a provided range.
   */
  protected function findNextInstance(array $instances) {
    $next_index = -1;
    $time = time();
    $current_upcoming = $this->getSetting('current_upcoming');
    foreach ($instances as $index => $instance) {
      $date_compare = ($current_upcoming) ? $instance->end_value : $instance->value;
      if ($date_compare > $time) {
        $next_index = $index;
        break;
      }
    }
    return $next_index;
  }

  /**
   * Helper function to find the next instance from now in a provided range.
   */
  protected function findNextInstanceByDay(array $dates, $today) {
    $next_index = -1;
    foreach ($dates as $index => $date) {
      if ($date >= $today) {
        $next_index = $index;
        break;
      }
    }
    return $next_index;
  }

  /**
   * Apply any configured augmenters.
   *
   * @param array $output
   *   Render array of output.
   * @param array $augmenters
   *   The augmenters that have been configured.
   * @param int $start_ts
   *   The start of the date range.
   * @param int $end_ts
   *   The end of the date range.
   * @param string $timezone
   *   The timezone to use.
   * @param int $delta
   *   The field delta being formatted.
   * @param string $repeats
   *   An optional RRULE string containing recurrence details.
   * @param string $ends
   *   An optional string to specify the end of the last instance.
   * @param string $type
   *   The set of configuration to use.
   */
  private function augmentOutput(array &$output, array $augmenters, $start_ts, $end_ts, $timezone, $delta, $repeats = '', $ends = '', $type = 'instances') {
    if (!$augmenters) {
      return;
    }

    foreach ($augmenters as $augmenter_id => $augmenter) {
      $augmenter->augmentOutput(
        $output,
        DrupalDateTime::createFromTimestamp($start_ts),
        DrupalDateTime::createFromTimestamp($end_ts),
        [
          'timezone' => $timezone,
          'allday' => static::isAllDay($start_ts, $end_ts, $timezone),
          'entity' => $this->entity,
          'settings' => $this->sharedSettings[$type]['settings'][$augmenter_id] ?? [],
          'delta' => $delta,
          'formatter' => $this,
          'repeats' => $repeats,
          'ends' => empty($ends) ? $ends : DrupalDateTime::createFromTimestamp($ends),
          'field_name' => $this->fieldDefinition->getName(),
        ]
      );
    }

  }

}
