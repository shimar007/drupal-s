<?php

namespace Drupal\entity_usage;

use Drupal\Core\Entity\EntityInterface;

/**
 * Entity usage interface.
 */
interface EntityUsageInterface {

  /**
   * Register or update a usage record.
   *
   * If called with $count >= 1, the record matching the other parameters will
   * be updated (or created if it doesn't exist). If called with $count <= 0,
   * the record will be deleted.
   *
   * Note that this method will honor the settings defined on the configuration
   * page, hence potentially ignoring the register if the settings for the
   * called combination are to not track this usage. Also, the hook
   * hook_entity_usage_block_tracking() will be invoked, so other modules will
   * have an opportunity to block this record before it is written to DB.
   *
   * @param int|string $target_id
   *   The target entity ID.
   * @param string $target_type
   *   The target entity type.
   * @param int|string $source_id
   *   The source entity ID.
   * @param string $source_type
   *   The source entity type.
   * @param string $source_langcode
   *   The source entity language code.
   * @param int|string $source_vid
   *   The source entity revision ID.
   * @param string $method
   *   The method used to relate source entity with the target entity. Normally
   *   the plugin id.
   * @param string $field_name
   *   The name of the field in the source entity using the target entity.
   * @param int $count
   *   (optional) The number of references to add to the object. Defaults to 1.
   */
  public function registerUsage($target_id, $target_type, $source_id, $source_type, $source_langcode, $source_vid, $method, $field_name, $count = 1): void;

  /**
   * Remove all records of a given target entity type.
   *
   * @param string $target_type
   *   The target entity type.
   */
  public function bulkDeleteTargets($target_type): void;

  /**
   * Remove all records of a given source entity type.
   *
   * @param string $source_type
   *   The source entity type.
   */
  public function bulkDeleteSources($source_type): void;

  /**
   * Delete all records for a given field_name + source_type.
   *
   * @param string $source_type
   *   The source entity type.
   * @param string $field_name
   *   The name of the field in the source entity using the
   *   target entity.
   */
  public function deleteByField($source_type, $field_name): void;

  /**
   * Delete all records for a given source entity.
   *
   * @param int|string $source_id
   *   The source entity ID.
   * @param string $source_type
   *   The source entity type.
   * @param string $source_langcode
   *   (optional) The source entity language code. Defaults to NULL.
   * @param string $source_vid
   *   (optional) The source entity revision ID. Defaults to NULL.
   */
  public function deleteBySourceEntity($source_id, $source_type, $source_langcode = NULL, $source_vid = NULL): void;

  /**
   * Delete all records for a given target entity.
   *
   * @param int|string $target_id
   *   The target entity ID.
   * @param string $target_type
   *   The target entity type.
   */
  public function deleteByTargetEntity($target_id, $target_type): void;

  /**
   * Provide a list of all referencing source entities for a target entity.
   *
   * Examples:
   *  - Return example 1:
   *  [
   *    'node' => [
   *      123 => [
   *        'source_langcode' => 'en',
   *        'source_vid' => '128',
   *        'method' => 'entity_reference',
   *        'field_name' => 'field_related_items',
   *        'count' => 1,
   *      ],
   *      124 => [
   *        'source_langcode' => 'en',
   *        'source_vid' => '129',
   *        'method' => 'entity_reference',
   *        'field_name' => 'Related items',
   *        'count' => 1,
   *      ],
   *    ],
   *    'user' => [
   *      2 => [
   *        'source_langcode' => 'en',
   *        'source_vid' => '2',
   *        'method' => 'entity_reference',
   *        'field_name' => 'field_author',
   *        'count' => 1,
   *      ],
   *    ],
   *  ]
   *  - Return example 2:
   *  [
   *    'entity_reference' => [
   *      'node' => [...],
   *      'user' => [...],
   *    ]
   *  ]
   *
   * @param \Drupal\Core\Entity\EntityInterface $target_entity
   *   A target entity.
   * @param bool $nest_results
   *   (optional) Whether the results should be returned in a nested structure.
   *   Defaults to TRUE.
   * @param int $limit
   *   (optional) Limit number of results returned. Defaults to 0, which
   *   will return all sources.
   *
   * @return ($nest_results is true ? array<string, array<string, array<array{source_langcode: string, source_vid: string, method: string, field_name: string, count: string}>>> : array<array{source_type: string, source_id: string, source_langcode: string, source_vid: string, method: string, field_name: string, count: string}>)
   *   A nested array with usage data. The first level is keyed by the type of
   *   the source entities, the second by the source id. The value of the second
   *   level contains all other information like the method used by the source
   *   to reference the target, the field name and the source language code. If
   *   $nest_results is FALSE, the returned array will be an indexed array where
   *   values are arrays containing all DB columns for the records.
   */
  public function listSources(EntityInterface $target_entity, $nest_results = TRUE, int $limit = 0): array;

  /**
   * Provide a list of all referenced target entities for a source entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $source_entity
   *   The source entity to check for references.
   * @param int $vid
   *   (optional) The revision id to return the references for.
   *   Defaults to all revisions.
   *
   * @return array<string, array<int|string, array<array{method: string, field_name: string, count: string}>>>
   *   A nested array with usage data. The first level is keyed by the type of
   *   the target entities, the second by the target id. The value of the second
   *   level contains all other information like the method used by the source
   *   to reference the target, the field name and the target language code.
   *
   * @see \Drupal\entity_usage\EntityUsageInterface::listSources()
   */
  public function listTargets(EntityInterface $source_entity, $vid = NULL): array;

  /**
   * Determines where an entity is used (deprecated).
   *
   * This method should not be used in new integrations, and is only provided
   * as BC-layer for existing implementations. Note however that the count
   * returned on 2.x will be different from the count returned on 1.x, once
   * now we track all revisions / translations independently.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A target (referenced) entity.
   * @param bool $include_method
   *   (optional) Whether the results must be wrapped into an additional array
   *   level, by the reference method. Defaults to FALSE.
   *
   * @return array<string, array<int|string, string>>
   *   A nested array with usage data.The first level is keyed by the type of
   *   the source entity, the second by the referencing objects ID. The value of
   *   the second level contains the usage count, which will be summed for all
   *   revisions and translations tracked.
   *   Note that if $include_method is TRUE, the first level is keyed by the
   *   reference method, and the second level will continue as explained above.
   *
   * @deprecated in entity_usage:2.0.0 and is removed from entity_usage:3.0.0.
   *   Use \Drupal\entity_usage\EntityUsageInterface::listSources() instead.
   *
   * @see https://www.drupal.org/project/entity_usage/issues/3445394
   */
  public function listUsage(EntityInterface $entity, $include_method = FALSE): array;

  /**
   * Determines referenced entities (deprecated).
   *
   * This method should not be used in new integrations, and is only provided
   * as BC-layer for existing implementations. Note however that the count
   * returned on 2.x will be different from the count returned on 1.x, once
   * now we track all revisions / translations independently.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A source entity.
   *
   * @return array<string, array<int, int>>
   *   A nested array with usage data.The first level is keyed by the type of
   *   the target entity, the second by the referencing objects ID. The value of
   *   the second level contains the usage count, which will be summed for all
   *   revisions and translations tracked.
   *
   * @deprecated in entity_usage:2.0.0 and is removed from entity_usage:3.0.0.
   *   Use \Drupal\entity_usage\EntityUsageInterface::listTargets() instead.
   *
   * @see https://www.drupal.org/project/entity_usage/issues/3445394
   */
  public function listReferencedEntities(EntityInterface $entity): array;

  /**
   * Lists all target entities for a source entity, field and method.
   *
   * @param string|int $source_id
   *   The source entity ID.
   * @param string $source_entity_type_id
   *   The source entity type.
   * @param string $source_langcode
   *   The source entity language code.
   * @param string|int $source_vid
   *   The source entity revision ID.
   * @param string $method
   *   The method used to relate source entity with the target entity. Normally
   *   the plugin id.
   * @param string $field_name
   *   The field name.
   *
   * @return string[]
   *   An indexed array of strings where each target entity type and ID are
   *   concatenated with a "|" character. Will return an empty array if no
   *   target entities found.
   *
   * @see \Drupal\entity_usage\EntityUsageTrackInterface::getTargetEntities()
   */
  public function listTargetEntitiesByFieldAndMethod(string|int $source_id, string $source_entity_type_id, string $source_langcode, string|int $source_vid, string $method, string $field_name): array;

}
