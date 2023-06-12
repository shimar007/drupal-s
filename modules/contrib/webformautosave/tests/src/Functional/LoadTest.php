<?php

namespace Drupal\Tests\webformautosave\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Simple test to ensure that pages load with module enabled.
 *
 * @group webformautosave
 */
class LoadTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'webform',
    'webform_submission_log',
    'webformautosave',
  ];

  /**
   * A user with permission to administer site configuration.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser(['access administration pages']);
  }

  /**
   * Tests that the admin page loads.
   */
  public function testAdmin() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin');
    $this->assertSession()->elementExists('xpath', '//h1[text() = "Administration"]');
  }

}
