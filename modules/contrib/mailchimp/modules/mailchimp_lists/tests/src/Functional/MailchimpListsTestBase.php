<?php

namespace Drupal\Tests\mailchimp_lists\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\mailchimp_lists_test\MailchimpListsConfigOverrider;

/**
 * Sets up Mailchimp Lists/Audiences module tests.
 */
abstract class MailchimpListsTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    \Drupal::configFactory()->addOverride(new MailchimpListsConfigOverrider());
  }

}
