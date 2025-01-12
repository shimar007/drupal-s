<?php

namespace Drupal\rest_menu_items\Plugin\rest\resource;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;
use Drupal\field\FieldConfigInterface;
use Drupal\file\Entity\File;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Provides a resource to get bundles by entity.
 *
 * @RestResource(
 *   id = "rest_menu_item",
 *   label = @Translation("Menu items per menu"),
 *   uri_paths = {
 *     "canonical" = "/api/menu_items/{menu_name}"
 *   }
 * )
 */
class RestMenuItemsResource extends ResourceBase {

  /**
   * A instance of the alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * A instance of the config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * A instance of the entitytype manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * A instance of modulehandler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * MenuLinkContent Entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $menuLinkContentStorage;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * A list of menu items.
   *
   * @var array
   */
  protected $menuItems = [];

  /**
   * The maximum depth we want to return the tree.
   *
   * @var int
   */
  protected $maxDepth = 0;

  /**
   * The minimum depth we want to return the tree from.
   *
   * @var int
   */
  protected $minDepth = 1;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, AliasManagerInterface $alias_manager, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entityTypeManager, ModuleHandlerInterface $moduleHandler, FileUrlGeneratorInterface $file_url_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->aliasManager = $alias_manager;
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entityTypeManager;
    $this->moduleHandler = $moduleHandler;
    $this->menuLinkContentStorage = $entityTypeManager->getStorage('menu_link_content');
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->getParameter('serializer.formats'), $container->get('logger.factory')
      ->get('rest'), $container->get('path_alias.manager'), $container->get('config.factory'), $container->get('entity_type.manager'), $container->get('module_handler'), $container->get('file_url_generator'));
  }

  /**
   * Responds to GET requests.
   *
   * Returns a list of menu items for specified menu name.
   *
   * @param string|null $menu_name
   *   The menu name.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The response containing a list of bundle names.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function get($menu_name = NULL) {
    if ($menu_name) {

      if (!$this->isAllowed($menu_name)) {
        throw new AccessDeniedHttpException('Forbidden menu.');
      }

      // Setup variables.
      $this->setup();

      // Create the parameters.
      $parameters = new MenuTreeParameters();
      // @todo Uncomment this line after the core issue is fixed.
      // @see https://www.drupal.org/project/drupal/issues/2939357
      // We're not losing anything. Disabled elements will be filtered later
      // in $menu_tree->build($tree).
      // $parameters->onlyEnabledLinks();
      if (!empty($this->maxDepth)) {
        $parameters->setMaxDepth($this->maxDepth);
      }

      if (!empty($this->minDepth)) {
        $parameters->setMinDepth($this->minDepth);
      }

      // Load the tree based on this set of parameters.
      $menu_tree = \Drupal::menuTree();
      $tree = $menu_tree->load($menu_name, $parameters);

      // Return if the menu does not exist or has no entries.
      if (empty($tree)) {
        $response = new ModifiedResourceResponse($tree);

        if ($response instanceof CacheableResponseInterface) {
          $response->addCacheableDependency(new RestMenuItemsCacheableDependency($menu_name, $this->minDepth, $this->maxDepth));
        }

        return $response;
      }

      // Transform the tree using the manipulators you want.
      $manipulators = [
        // Only show links that are accessible for the current user.
        ['callable' => 'menu.default_tree_manipulators:checkAccess'],
        // Use the default sorting of menu links.
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
      ];
      $this->moduleHandler->alter('rest_menu_items_resource_manipulators', $manipulators, $menu_name);
      $tree = $menu_tree->transform($tree, $manipulators);

      // Finally, build a renderable array from the transformed tree.
      $menu = $menu_tree->build($tree);

      // Return if the menu has no entries.
      if (empty($menu['#items'])) {
        return new ModifiedResourceResponse([]);
      }

      $this->getMenuItems($menu['#items'], $this->menuItems);

      // Other modules can alter the output before rendering.
      $this->moduleHandler->alter('rest_menu_items_output', $this->menuItems);

      // Return response.
      $response = new ModifiedResourceResponse(array_values($this->menuItems));

      // Configure caching for minDepth and maxDepth parameters.
      if ($response instanceof CacheableResponseInterface) {
        $response->addCacheableDependency(new RestMenuItemsCacheableDependency($menu_name, $this->minDepth, $this->maxDepth));
      }

      // Return the JSON response.
      return $response;
    }
    throw new HttpException("Menu name was not provided");
  }

  /**
   * Check whether or not the given menu can be exposed.
   *
   * @param string $menu_name
   *   The menu name.
   *
   * @return bool
   *   TRUE is the menu can be exposed, FALSE otherwise.
   */
  protected function isAllowed($menu_name) {
    $config = $this->configFactory->get('rest_menu_items.config');
    $allowed_menus = $config->get('allowed_menus') ?? [];
    return !array_key_exists($menu_name, $allowed_menus) || in_array($menu_name, $allowed_menus);
  }

  /**
   * Generate the menu tree we can use in JSON.
   *
   * @param array $tree
   *   The menu tree.
   * @param array $items
   *   The already created items.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getMenuItems(array $tree, array &$items = []) {
    $config = $this->configFactory->get('rest_menu_items.config');
    $outputValues = $config->get('output_values');

    // Loop through the menu items.
    foreach ($tree as $item_value) {
      /** @var \Drupal\Core\Menu\MenuLinkInterface $org_link */
      $org_link = $item_value['original_link'];

      /** @var \Drupal\Core\Url $url */
      $url = $item_value['url'];

      $newValue = [];

      foreach ($outputValues as $valueKey) {
        if (!empty($valueKey)) {
          $this->getElementValue($newValue, $valueKey, $org_link, $url);
        }
      }

      $this->checkContentFields($org_link, $newValue);

      if (!empty($item_value['below'])) {
        $newValue['below'] = [];
        $this->getMenuItems($item_value['below'], $newValue['below']);
      }

      $items[] = $newValue;
    }
  }

  /**
   * This function is used to generate some variables we need to use.
   *
   * These variables are available in the url.
   */
  private function setup() {
    // Get the current request.
    $request = \Drupal::request();

    // Get and set the max depth if available.
    $max = $request->get('max_depth');
    if (!empty($max)) {
      $this->maxDepth = $max;
    }

    // Get and set the min depth if available.
    $min = $request->get('min_depth');
    if (!empty($min)) {
      $this->minDepth = $min;
    }
  }

  /**
   * Generate the menu element value.
   *
   * @param array $returnArray
   *   The return array we want to add this item to.
   * @param string $key
   *   The key to use in the output.
   * @param \Drupal\Core\Menu\MenuLinkInterface $link
   *   The link from the menu.
   * @param \Drupal\Core\Url $url
   *   The URL object of the menu item.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getElementValue(array &$returnArray, $key, MenuLinkInterface $link, Url $url) {
    $config = $this->configFactory->get('rest_menu_items.config');
    $external = $url->isExternal();
    $routed = $url->isRouted();
    $existing = TRUE;
    $value = NULL;

    // Check if the url is a <nolink> and do not do anything for some keys.
    $itemsToRemoveWhenNoLink = [
      'uri',
      'alias',
      'absolute',
      'relative',
    ];
    if (!$external && $routed && $url->getRouteName() === '<nolink>' && in_array($key, $itemsToRemoveWhenNoLink)) {
      return;
    }

    if ($external || !$routed) {
      $uri = $url->getUri();
    }
    else {
      try {
        $uri = $url->getInternalPath();
      }
      catch (\UnexpectedValueException $e) {
        $uri = $relative = Url::fromUri($url->getUri())
          ->toString();
        $existing = FALSE;
      }
    }

    switch ($key) {
      case 'key':
        $value = $link->getDerivativeId();
        if (empty($value)) {
          $value = $link->getBaseId();
        }
        break;

      case 'title':
        $value = $link->getTitle();
        break;

      case 'description':
        $value = $link->getDescription();
        break;

      case 'uri':
        $value = $uri;
        break;

      case 'alias':
        if ($routed) {
          $value = ltrim($this->aliasManager->getAliasByPath("/$uri"), '/');
        }
        break;

      case 'external':
        $value = $external;
        break;

      case 'absolute':
        $base_url = $config->get('base_url');

        if ($external) {
          $value = $uri;
        }
        elseif (!$routed) {
          if (empty($base_url)) {
            $url->setAbsolute();
          }

          $value = $url->toString(TRUE)
            ->getGeneratedUrl();

          if (!empty($base_url)) {
            $value = $base_url . $value;
          }
        }
        else {
          $options = [];
          if (empty($base_url)) {
            $options = ['absolute' => TRUE];
          }

          $value = Url::fromUri('internal:/' . $uri, $options)
            ->toString(TRUE)
            ->getGeneratedUrl();

          if (!empty($base_url)) {
            $value = $base_url . $value;
          }
        }
        break;

      case 'relative':
        if (!$external) {
          $value = Url::fromUri('internal:/' . $uri, ['absolute' => FALSE])
            ->toString(TRUE)
            ->getGeneratedUrl();
        }

        if (!$routed) {
          $url->setAbsolute(FALSE);
          $value = $url->toString(TRUE)
            ->getGeneratedUrl();
        }

        if (!$existing) {
          $value = Url::fromUri($url->getUri())
            ->toString();
        }
        break;

      case 'existing':
        $value = $existing;
        break;

      case 'weight':
        $value = $link->getWeight();
        break;

      case 'expanded':
        $value = $link->isExpanded();
        break;

      case 'enabled':
        $value = $link->isEnabled();
        break;

      case 'uuid':
        if (!$external && $routed) {
          $params = Url::fromUri('internal:/' . $uri)
            ->getRouteParameters();
          $entity_type = key($params);

          if (!empty($entity_type) && $this->entityTypeManager->hasDefinition($entity_type)) {
            $entity = $this->entityTypeManager->getStorage($entity_type)
              ->load($params[$entity_type]);
            $value = $entity->uuid();
          }
        }
        break;

      case 'options':
        $value = $link->getOptions();
        break;
    }

    $addFragmentElements = [
      'alias',
      'absolute',
      'relative',
    ];
    if (!empty($config->get('add_fragment')) && in_array($key, $addFragmentElements)) {
      $this->addFragment($value, $link);
    }

    $returnArray[$key] = $value;
  }

  /**
   * Add the fragment to the value if neccesary.
   *
   * @param string $value
   *   The value to add the fragment to. Passed by reference.
   * @param \Drupal\Core\Menu\MenuLinkInterface $link
   *   The link from the menu.
   */
  private function addFragment(&$value, MenuLinkInterface $link) {
    $options = $link->getOptions();
    if (!empty($options) && isset($options['fragment'])) {
      $value .= '#' . $options['fragment'];
    }
  }

  /**
   * Check if there are any additional fields added to MenuLink.
   *
   * @param \Drupal\Core\Menu\MenuLinkInterface $menu_link
   *   The menu link.
   * @param array $newValue
   *   The new value.
   */
  protected function checkContentFields(MenuLinkInterface $menu_link, array &$newValue) {
    $plugin_definition = $menu_link->getPluginDefinition();
    if (!empty($plugin_definition['metadata'])) {
      $id = $plugin_definition['metadata']['entity_id'];
      $menu_link_content = $this->menuLinkContentStorage->load($id);

      if ($menu_link_content) {
        $lang_code = \Drupal::service('language_manager')->getCurrentLanguage()->getId();
        if ($menu_link_content->hasTranslation($lang_code)) {
          $menu_link_content = $menu_link_content->getTranslation($lang_code);
        }

        $field_definitions = $menu_link_content->getFieldDefinitions();

        foreach ($field_definitions as $field_definition) {
          // Ignore BaseFields.
          if ($field_definition instanceof FieldConfigInterface) {
            $field_name = $field_definition->getName();
            if (
              $field_definition->getType() == 'entity_reference' ||
              $field_definition->getType() == 'entity_reference_revisions'
              || $field_definition->getSetting('target_type') == 'taxonomy_term') {
              $field_value = $menu_link_content->get($field_name)->entity;
            }
            elseif ($field_definition->getType() == 'image') {
              $image_urls = [];
              foreach ($menu_link_content->get($field_name)->getValue() as $fids) {
                $file = File::load($fids['target_id']);
                $image_urls[]['value'] = Url::fromUri($this->fileUrlGenerator->generateAbsoluteString($file->getFileUri()))->toString();
              }
              $field_value = $image_urls;
            }
            else {
              $field_value = $menu_link_content->get($field_name)->getValue();
            }
            // @todo add proper normalizing.
            // @todo render the values of the fields, as simply the referenced
            //   entity ID doesn't do much for the client using the endpoint.
            $normalized_value = NULL;
            if (!empty($field_value)) {
              $normalized_value = $field_value;
            }
            $newValue[$field_name] = $normalized_value;
          }
        }
      }
    }
  }

}
