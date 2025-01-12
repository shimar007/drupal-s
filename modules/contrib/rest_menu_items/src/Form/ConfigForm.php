<?php

namespace Drupal\rest_menu_items\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The class that generates the config form of the module.
 */
class ConfigForm extends ConfigFormBase {

  /**
   * The menu storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $menuStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->menuStorage = $container->get('entity_type.manager')->getStorage('menu');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'rest_menu_items.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('rest_menu_items.config');
    $form['output_values'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('What values do you need in the output?'),
      '#options' => [
        'key' => $this->t('Key'),
        'title' => $this->t('Title'),
        'description' => $this->t('Description'),
        'uri' => $this->t('Uri'),
        'alias' => $this->t('Alias'),
        'external' => $this->t('External'),
        'absolute' => $this->t('Absolute'),
        'relative' => $this->t('Relative'),
        'existing' => $this->t('Existing'),
        'weight' => $this->t('Weight'),
        'expanded' => $this->t('Expanded'),
        'enabled' => $this->t('Enabled'),
        'uuid' => $this->t('Uuid'),
        'options' => $this->t('Options'),
      ],
      '#default_value' => empty($config->get('output_values')) ? [] : $config->get('output_values'),
    ];

    $form['base_url'] = [
      '#type' => 'textfield',
      '#default_value' => $config->get('base_url'),
      '#title' => $this->t('Base URL'),
      '#description' => $this->t('This url is used as the base url in the absolute links.'),
    ];

    $form['add_fragment'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('add_fragment'),
      '#title' => $this->t('Add the fragment (#id) to the url'),
      '#description' => $this->t('This adds the #id (fragment, anchor link) to the outputted url.'),
    ];

    $menus = $this->menuStorage->loadMultiple();
    $menus_options = [];
    foreach ($menus as $menu_name => $menu) {
      $menus_options[$menu_name] = $menu->label();
    }
    $form['allowed_menus'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Allowed menus'),
      '#options' => $menus_options,
      '#default_value' => $config->get('allowed_menus'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('rest_menu_items.config')
      ->set('output_values', $form_state->getValue('output_values'))
      ->set('base_url', $form_state->getValue('base_url'))
      ->set('add_fragment', $form_state->getValue('add_fragment'))
      ->set('allowed_menus', $form_state->getValue('allowed_menus'))
      ->save();
  }

}
