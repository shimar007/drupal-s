<?php

namespace Drupal\adsense\Controller;

use Drupal\adsense\PublisherId;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CseResultsController.
 */
class CseV2ResultsController extends ControllerBase {

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new CseV2ResultsController controller.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface|null $module_handler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler')
    );
  }

  /**
   * Display the search results page.
   *
   * @param string $slot
   *   CSE slot ID.
   *
   * @return array
   *   Markup for the page with the search results.
   */
  public function display($slot) {
    $config = $this->config('adsense.settings');
    $client = PublisherId::get();
    $this->moduleHandler->alter('adsense', $client);

    if ($config->get('adsense_test_mode')) {
      $content = [
        '#theme' => 'adsense_ad',
        '#content' => ['#markup' => nl2br("Results\ncx = partner-$client:{$slot}")],
        '#classes' => ['adsense-placeholder'],
        '#height' => 100,
      ];
    }
    else {
      // Log the search keys.
      $this->getLogger('AdSense CSE v2')->notice('Search keywords: %keyword', [
        '%keyword' => urldecode($_GET['q']),
      ]);

      $content = [
        '#theme' => 'adsense_cse_v2_results',
        '#client' => $client,
        '#slot' => $slot,
      ];
    }
    return $content;
  }

}
