<?php

namespace Drupal\filebrowser\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class FolderForm extends FormBase {

  /**
   * @var int
   * If we create a folder in a sub folder this is the fid
   * of the subfolder. If we want to redirect to the node page we can use
   * the url query /node/{nid}?fid=$relativeFid
   */
  protected $relativeFid;

  /**
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * @var string
   */
  protected $relativeRoot;

  /**
   * @var \Drupal\filebrowser\Services\Common
   */
  protected $common;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'create_folder_form';
  }

  /**
   * {@inheritdoc}
   * @var array $list
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = null, $relative_fid = null, $fids = null, $ajax = null) {
    $this->common = \Drupal::service('filebrowser.common');
    $this->relativeRoot = $this->common->relativePath($relative_fid);
    $this->node = Node::load($nid);
    $this->relativeFid = $relative_fid;

    // If this form is to be presented in a slide-down window we
    // will set the attributes and at a close-window link
    if($ajax) {
      $form['#attributes'] = [
        'class' => [
          'form-in-slide-down'
        ],
      ];
      $form['close-window'] = $this->common->closeButtonMarkup();
    }

    $form['#tree'] = true;

    $form['folder_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Folder Name'),
      '#size' => 40,
      '#description' => $this->t('This folder will be created within the current directory.'),
      '#required' => true,
      ];

    $form['create'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create'),
      '#name' => 'create',
    ];
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'filebrowser/filebrowser-styles';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $folder_uri =
      $this->node->filebrowser->folderPath . $this->relativeRoot . '/' . $form_state->getValue('folder_name');

    $success = \Drupal::service('file_system')->prepareDirectory($folder_uri, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    if (!$success) {
      \Drupal::messenger()->addError($this->t('Unable to create this folder, do you have filesystem right to do that ?'));
    }
    else{
      Cache::invalidateTags(['filebrowser:node:' . $this->node->id()]);
    }
    $route = $this->common->redirectRoute($this->relativeFid, $this->node->id());
    $form_state->setRedirect($route['name'], $route['node'], $route['query']);
  }

}
