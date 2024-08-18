<?php

namespace Drupal\ckeditor_plugin_report\Controller;

use Drupal\ckeditor5\Plugin\CKEditor5PluginManagerInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The PluginReportController controller.
 */
class PluginReportController extends ControllerBase {

  /**
   * The CKEditor 4 plugin manager, if the service is defined.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface|null
   */
  protected $ckeditorPluginManager;

  /**
   * The CKEditor 5 plugin manager, if the service is defined.
   *
   * @var \Drupal\ckeditor5\Plugin\CKEditor5PluginManagerInterface|null
   */
  protected $ckeditor5PluginManager;

  /**
   * The CKEditor 4-to-5 upgrade plugin manager, if the service is defined.
   *
   * @var \Drupal\ckeditor5\Plugin\PluginManagerInterface|null
   */
  protected $ckeditor4to5UpgradePluginManager;

  /**
   * ModalFormContactController constructor.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface|null $ckeditor_plugin_manager
   *   The CKEditor 4 plugin manager, if the service is defined.
   * @param \Drupal\ckeditor5\Plugin\CKEditor5PluginManagerInterface|null $ckeditor5_plugin_manager
   *   The CKEditor 5 plugin manager, if the service is defined.
   * @param \Drupal\ckeditor5\Plugin\PluginManagerInterface|null $ckeditor4to5upgrade_plugin_manager
   *   The CKEditor 4-to-5 upgrade plugin manager, if the service is defined.
   */
  public function __construct(PluginManagerInterface|null $ckeditor_plugin_manager, CKEditor5PluginManagerInterface|null $ckeditor5_plugin_manager, PluginManagerInterface|null $ckeditor4to5upgrade_plugin_manager) {
    $this->ckeditorPluginManager = $ckeditor_plugin_manager;
    $this->ckeditor5PluginManager = $ckeditor5_plugin_manager;
    $this->ckeditor4to5UpgradePluginManager = $ckeditor4to5upgrade_plugin_manager;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      // Since this module declares no dependencies, it's possible that either
      // CKEditor 4 or CKEditor 5 may not be installed.
      $container->get('plugin.manager.ckeditor.plugin', ContainerInterface::NULL_ON_INVALID_REFERENCE),
      $container->get('plugin.manager.ckeditor5.plugin', ContainerInterface::NULL_ON_INVALID_REFERENCE),
      $container->get('ckeditor_plugin_report.ckeditor4to5upgrade,plugin_manager', ContainerInterface::NULL_ON_INVALID_REFERENCE),
    );
  }

  /**
   * Returns the report render array.
   */
  public function content() {
    $build = [];

    $build['intro'] = [
      '#markup' => $this->t('<p>Below are CKEditor plugins as returned by their respective plugin managers:</p>'),
    ];

    $table_template = [
      '#type' => 'table',
      '#header' => [
        $this->t('Plugin ID'),
        $this->t('Provider'),
        $this->t('Class'),
      ],
      '#empty' => $this->t('No plugins found.'),
    ];

    if ($this->ckeditorPluginManager) {
      $build['ckeditor4_plugins'] = [
        '#type' => 'details',
        '#title' => $this->t('CKEditor 4 plugins'),
      ];

      $build['ckeditor4_plugins']['table'] = $table_template;

      $definitions = $this->ckeditorPluginManager->getDefinitions();
      foreach ($definitions as $definitions) {
        $build['ckeditor4_plugins']['table']['#rows'][] = [
          $definitions['id'],
          $definitions['provider'],
          $definitions['class'],
        ];
      }
    }

    if ($this->ckeditor5PluginManager) {
      $build['ckeditor5_plugins'] = [
        '#type' => 'details',
        '#title' => $this->t('CKEditor 5 plugins'),
      ];

      $build['ckeditor5_plugins']['table'] = $table_template;

      $definitions = $this->ckeditor5PluginManager->getDefinitions();
      foreach ($definitions as $definitions) {
        $build['ckeditor5_plugins']['table']['#rows'][] = [
          $definitions->id(),
          $definitions->getProvider(),
          $definitions->getClass(),
        ];
      }
    }

    if ($this->ckeditor4to5UpgradePluginManager) {
      $build['ckeditor4to5upgrade_plugins'] = [
        '#type' => 'details',
        '#title' => $this->t('CKEditor 4-to-5 upgrade plugins'),
      ];

      $build['ckeditor4to5upgrade_plugins']['table'] = $table_template;

      $definitions = $this->ckeditor4to5UpgradePluginManager->getDefinitions();
      foreach ($definitions as $definitions) {
        $build['ckeditor4to5upgrade_plugins']['table']['#rows'][] = [
          $definitions['id'],
          $definitions['provider'],
          $definitions['class'],
        ];
      }
    }

    return $build;
  }

}
