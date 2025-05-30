<?php

namespace Drupal\filebrowser\EventSubscriber;

use Drupal\Component\Utility\DeprecationHelper;
use Drupal\Core\StringTranslation\ByteSizeMarkup;
use Drupal;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\filebrowser\Entity\FilebrowserMetadataEntity;
use Drupal\filebrowser\Events\MetadataEvent;
use Drupal\filebrowser\File\DisplayFile;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MetadataEventSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  protected $storage;
  protected $nid;

  public function __construct() {
    $this->storage = Drupal::entityTypeManager()->getStorage('filebrowser_metadata_entity');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events['filebrowser.metadata_event'][] = ['setMetadata', 0];
    return $events;
  }

  public function setMetadata(MetadataEvent $event) {
    $this->nid = $event->nid;
    $fid = $event->getFid();
    $file = $event->file;
    $subdir_fid = $event->subdir_fid;
    $columns = $event->columns;
    $meta = $this->MetadataIds();
    foreach($meta as $name => $title) {
      // only the selected columns
      if ($columns[$name]) {
        $data = $this->createData($name, $fid, $file, $subdir_fid);
        $query = Drupal::entityQuery('filebrowser_metadata_entity')
          ->accessCheck(FALSE)
          ->condition('fid', $fid)
          ->condition('module', 'filebrowser')
          ->condition('name', $name);
        $entity_id = $query->execute();
        if ($entity_id) {
          // entity exists, so we just update the contents
          /** @var FilebrowserMetadataEntity $metadata */
          $metadata = $this->storage->load(reset($entity_id));
          $metadata->setTheme($data['theme']);
          $metadata->setContent(serialize($data['content']));
          $metadata->save();
        }
        else {
          $value = [
            'fid' => $fid,
            'nid' => $this->nid,
            'name' => $name,
            'title' => $title,
            'module' => 'filebrowser',
            'theme' => $data['theme'],
            'content' => serialize($data['content']),
          ];
          $entity = $this->storage->create($value);
          $entity->save();
        }
      }
    }
  }

  protected function createData($id, $fid, $file, $subdir_fid) {
    if ($file->fileData->type == 'file') {
      /** @var DisplayFile $file */
      switch ($id) {
        case 'description':
          return [
            'content' => $this->generateDescription($file, $subdir_fid, $fid),
            'theme' => 'filebrowser_description'
          ];

        case 'size':
          return [
            'content' => DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '10.2.0', fn() => ByteSizeMarkup::create($file->fileData->size), fn() => format_size($file->fileData->size)),
            'theme' => "",
          ];

        case 'created':
          return [
            'theme' => "",
            'content' => Drupal::service('date.formatter')->format($file->fileData->timestamp, 'short'),
          ];

        case 'mimetype':
          return [
            'theme' => "",
            'content' => $file->fileData->mimetype,
          ];
      }
    }
    else {
      if ($id == 'description') {
        return [
          'content' => $this->generateDescription($file, $subdir_fid, $fid),
          'theme' => 'filebrowser_description'
        ];
      }
      else {
        return [
          'theme' => "",
          'content' => "",
        ];
      }
    }
  }

  public function MetadataIds() {
    return [
      'description' => $this->t('Description'),
      'size' => $this->t('File size'),
      'created' => $this->t('Created'),
      'mimetype' => $this->t('Mimetype'),
    ];
  }

  public function generateDescription($file, $subdir_fid, $fid) {
    /** @var FilebrowserMetadataEntity $metadata */
    // get the present description
    $query = Drupal::entityQuery('filebrowser_metadata_entity')
      ->accessCheck(FALSE)
      ->condition('fid', $fid)
      ->condition('module', 'filebrowser')
      ->condition('name', 'description');
    $entity_id = $query->execute();

    if ($entity_id) {
      // entity exists
      $metadata = $this->storage->load(reset($entity_id));
      $content = unserialize($metadata->content->value);
      //originally title was not set for directories. So even if the entity existed, there was no title
      $description = $content['title'] ?? $this->t('Default description');
    }
    else{
      // no description available
      $description = $this->t('Default description');
    }

    if(!empty($subdir_fid)) {
      //this is a sub-folder
      $p = ['nid' => $this->nid, 'query_fid' => $subdir_fid, 'fids' => $fid,];
    }
    else {
      $p = ['nid' => $this->nid, 'fids' => $fid,];
    }
    return [
      'create_link' => !($file->name == '..'),
      'title' => $file->name == '..' ? '' : $description,
      'url' => Url::fromRoute('filebrowser.inline_description_form', $p),
      'attributes' => [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode(['width' => 700]),
      ],
      'image_title' => $this->t('Edit description'),
    ];
  }

}
