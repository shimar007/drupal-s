<?php

namespace Drupal\ckeditor_plugin_report;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Conditionally alias the plugin.manager.ckeditor4to5upgrade.plugin service.
 */
class CkeditorPluginReportServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // The plugin.manager.ckeditor4to5upgrade.plugin service is private, so
    // alias the service to a public service if it is defined.
    if ($container->hasDefinition('plugin.manager.ckeditor4to5upgrade.plugin')) {
      $container->setAlias('ckeditor_plugin_report.ckeditor4to5upgrade,plugin_manager', 'plugin.manager.ckeditor4to5upgrade.plugin');
    }
  }

}
