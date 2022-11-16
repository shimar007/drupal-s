<?php

namespace Drupal\rest_menu_items\Plugin\rest\resource;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\Cache;

/**
 * The cacheable dependency class.
 */
class RestMenuItemsCacheableDependency implements CacheableDependencyInterface {

  /**
   * Minimum depth parameter.
   *
   * @var int
   */
  protected $minDepth = 1;

  /**
   * Maximum depth parameter.
   *
   * @var int
   */
  protected $maxDepth = 1;

  /**
   * The menu being exposed.
   *
   * @var string
   */
  protected $menuName = '';

  /**
   * RestMenuItemsCachableDependency constructor.
   *
   * @param string $menuName
   *   The menu name.
   * @param int $minDepth
   *   The minimum depth to be used as a cache context.
   * @param int $maxDepth
   *   The maximum depth to be used as a cache context.
   */
  public function __construct($menuName, $minDepth, $maxDepth) {
    $this->menuName = $menuName;
    $this->minDepth = $minDepth;
    $this->maxDepth = $maxDepth;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = [];
    // URL parameters as contexts.
    if ($this->minDepth != 1 || $this->maxDepth != 1) {
      $contexts[] = 'url.query_args';
      $contexts[] = 'user.permissions';
    }
    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = [];
    $tags[] = 'config:system.menu.' . $this->menuName;
    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

}
