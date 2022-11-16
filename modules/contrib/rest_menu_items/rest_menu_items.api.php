<?php

/**
 * @file
 * Hook examples and explanations.
 */

/**
 * @addtogroup hooks
 */

/**
 * Add some extra manipulators to change the output of the REST endpoint.
 *
 * @param array $manipulators
 *   The manipulators used, so you can add your own.
 * @param string $menu_name
 *   The menu name.
 */
function hook_rest_menu_items_resource_manipulators_alter(array &$manipulators, &$menu_name) {
  if ($menu_name === 'main') {
    $manipulators[] = ['callable' => 'custom.manipulator:method'];
  }
}

/**
 * Alter the output of the menu_items array.
 *
 * @param array $menu_items
 *   The menu items array.
 */
function hook_rest_menu_items_output_alter(array &$menu_items) {
  foreach ($menu_items as &$menu_item) {
    if (array_key_exists('below', $menu_item)) {
      $menu_item['child'] = $menu_item['below'];
      unset($menu_item['below']);
    }
  }
}
