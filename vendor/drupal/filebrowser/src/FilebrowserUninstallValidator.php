<?php

/**
 * Prevents filebrowser module from being uninstalled whilst any filebrowser nodes exists.
 */

namespace Drupal\filebrowser;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

class FilebrowserUninstallValidator implements ModuleUninstallValidatorInterface {

  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new FilebrowserUninstallValidator.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface EntityTypeManager
   *   The entity query factory.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */

  public function __construct(EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation) {
    $this->entityQuery = $entity_type_manager->getStorage('node')->getQuery();
    $this->stringTranslation = $string_translation;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($module) {
    $reasons = [];
    if ($module == 'filebrowser') {
      // The Filebrowser node type is provided by the Filebrowser module. Prevent uninstall
      // if there are any nodes of that type.
      if ($this->hasNodes()) {
        $reasons[] = $this->t('To uninstall Filebrowser, delete all nodes of type %type', ['%type' => 'dir_listing']);
      }
    }
    return $reasons;
  }

  /**
   * Determines if there is any filebrowser nodes or not.
   *
   * @return bool
   *   TRUE if there are filebrowser nodes, FALSE otherwise.
   */
  protected function hasNodes() {
    $nodes = $this->entityQuery
      ->condition('type', 'dir_listing')
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();
    return !empty($nodes);
  }

}