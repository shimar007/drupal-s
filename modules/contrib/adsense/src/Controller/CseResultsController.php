<?php

namespace Drupal\adsense\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class CseResultsController.
 */
class CseResultsController extends ControllerBase {

  /**
   * Display the search results page.
   *
   * @return array
   *   Markup for the page with the search results.
   */
  public function display() {
    $config = $this->config('adsense.settings');
    $width = $config->get('adsense_cse_frame_width');
    $country = $config->get('adsense_cse_country');

    if ($config->get('adsense_test_mode')) {
      $content = [
        '#theme' => 'adsense_ad',
        '#content' => ['#markup' => nl2br("Results\nwidth = $width\ncountry = $country")],
        '#classes' => ['adsense-placeholder'],
        '#width' => $width,
        '#height' => 100,
      ];
    }
    else {
      global $base_url;

      // Log the search keys.
      $this->getLogger('AdSense CSE v1')->notice('Search keywords: %keyword', [
        '%keyword' => urldecode($_GET['q']),
      ]);

      $content = [
        '#theme' => 'adsense_cse_results',
        '#width' => $width,
        '#country' => $country,
        // http://www.google.com/afsonline/show_afs_search.js
        '#script' => $base_url . '/' . drupal_get_path('module', 'adsense') . '/js/adsense_cse-v1.results.js',
      ];
    }
    return $content;
  }

}
