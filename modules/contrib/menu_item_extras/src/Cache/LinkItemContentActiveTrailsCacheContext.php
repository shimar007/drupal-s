<?php

namespace Drupal\menu_item_extras\Cache;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Defines the MenuActiveTrailsCacheContext service.
 *
 * This class is container-aware to avoid initializing the 'menu.active_trails'
 * service (and its dependencies) when it is not necessary.
 */
class LinkItemContentActiveTrailsCacheContext implements CalculatedCacheContextInterface, ContainerAwareInterface {

  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t("Active menu trail");
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($parameter = NULL) {
    list($menu_name, $menu_link_id) = explode(':', $parameter);

    if (!$menu_name) {
      throw new \LogicException('No menu name provided for menu.active_trails cache context.');
    }

    $active_trail_link = $this->container->get('menu.active_trail')
      ->getActiveLink($menu_name);

    if ($active_trail_link && $active_trail_link->getDerivativeId() == $menu_link_id) {
      return 'link_item_content.active.' . $menu_link_id;
    }
    else {
      return 'link_item_content.inactive';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($parameter = NULL) {
    list($menu_name,) = explode(':', $parameter);

    if (!$menu_name) {
      throw new \LogicException('No menu name provided for menu.active_trails cache context.');
    }
    $cacheable_metadata = new CacheableMetadata();
    return $cacheable_metadata->setCacheTags(["config:system.menu.$menu_name"]);
  }

}
