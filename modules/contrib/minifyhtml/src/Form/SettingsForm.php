<?php

namespace Drupal\minifyhtml\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for the module.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'minifyhtml.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('minifyhtml.config');

    $form['minifyhtml_minify'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Minified Source HTML.'),
      '#description'   => $this->t('Toggle minified HTML on or off.'),
      '#default_value' => $config->get('minify'),
    ];

    $form['strip_comments'] = [
      '#title'         => $this->t('Strip comments from the source HTML'),
      '#description'   => $this->t('If checked, strip HTML comments and multi-line comments in @script and @style tags.', ['@script' => '<script>', '@style' => '<style>']),
      '#type'          => 'checkbox',
      '#default_value' => $config->get('strip_comments'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'minifyhtml_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('minifyhtml.config')
      ->set('strip_comments', $form_state->getValue('strip_comments'))
      ->set('minify', $form_state->getValue('minifyhtml_minify'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
