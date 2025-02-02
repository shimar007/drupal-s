<?php

namespace Drupal\Tests\config_delete\FunctionalJavascript;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the user interface for deleting configuration.
 *
 * @group config_delete
 */
class ConfigDeleteUITest extends WebDriverTestBase {

  use StringTranslationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['contact', 'config', 'config_delete', 'comment'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalLogin($this->drupalCreateUser(['delete configuration']));
  }

  /**
   * Tests config delete.
   */
  public function testConfigDeletion() {
    $this->drupalGet('admin/config/development/configuration/delete');
    $config = $this->config('contact.form.personal');
    $this->assertNotNull($config->get('id'));

    $this->getSession()->getPage()->selectFieldOption('config_type', 'contact_form');
    $this->assertSession()->assertExpectedAjaxRequest();
    $this->getSession()->getPage()->selectFieldOption('config_name', 'personal');
    $this->assertSession()->assertExpectedAjaxRequest();
    $this->getSession()->getPage()->pressButton('Delete');
    $this->assertSession()->pageTextContains($this->t('Configuration "contact.form.personal" successfully deleted.'));

    $this->rebuildContainer();
    $config = $this->config('contact.form.personal');
    $this->assertNull($config->get('id'));
  }

  /**
   * Tests form validation.
   */
  public function testFormValidation() {
    $this->drupalGet('admin/config/development/configuration/delete');
    $this->getSession()->getPage()->selectFieldOption('config_type', 'comment_type');
    $this->assertSession()->assertExpectedAjaxRequest();
    $this->getSession()->getPage()->selectFieldOption('config_name', '- Select -');
    $this->getSession()->getPage()->pressButton('Delete');
    $this->assertSession()->pageTextContains($this->t('Please select a valid configuration name.'));
  }

}
