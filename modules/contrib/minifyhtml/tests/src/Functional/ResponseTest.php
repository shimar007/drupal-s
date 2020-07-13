<?php

namespace Drupal\Tests\minifyhtml\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Class ResponseTest.
 *
 * @package Drupal\Tests\minifyhtml\Kernel
 *
 * @group minifyhtml
 */
class ResponseTest extends BrowserTestBase {

  /**
   * The theme to install as the default for testing.
   *
   * Defaults to the install profile's default theme, if it specifies any.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * The test runner will merge the $modules lists from this class, the class
   * it extends, and so on up the class hierarchy. It is not necessary to
   * include modules in your list that a parent class has already declared.
   *
   * @var string[]
   *
   * @see \Drupal\Tests\BrowserTestBase::installDrupal()
   */
  protected static $modules = ['minifyhtml_test'];

  /**
   * Set of cases with expected results.
   *
   * See Cases controller for test input data.
   *
   * @return array
   *   Test cases.
   *
   * @see \Drupal\minifyhtml_test\Controller\Cases::item()
   */
  public function dataProvider() {
    $data = [];

    // Test Minify HTML Textarea Replacement.
    $expected_output  = "<html><head><title>Test HTML</title></head><body><textarea cols=\"55\" rows=\"31\">\n";
    $expected_output .= "Content in here will not matter.\n";
    $expected_output .= "Even multiline content.\n";
    $expected_output .= "</textarea></body></html>";
    $case = 'textarea_replacement';
    $data[$case] = [
      $case,
      $expected_output,
    ];

    // Test Minify HTML Pre Replacement.
    $expected_output = "<html><head><title>Test HTML</title></head><body><pre>\n";
    $expected_output .= "  Indented content.\n";
    $expected_output .= "         Weirdly Indented content.\n";
    $expected_output .= "Non-indented content.\n";
    $expected_output .= "</pre></body></html>";
    $case = 'pre_replacement';
    $data[$case] = [
      $case,
      $expected_output,
    ];

    // Test Minify HTML Iframe Replacement.
    $case = 'iframe_replacement';
    $data[$case] = [
      $case,
      "<html><head><title>Test HTML</title></head><body><iframe src=\"\" width=\"100\" height=\"100\" ></iframe></body></html>",
    ];

    // Test Minify HTML Script Replacement.
    $expected_output = "<html><head><title>Test HTML</title></head><body><script>\n";
    $expected_output .= "alert('test');\n";
    $expected_output .= "</script></body></html>";
    $case = 'script_replacement';
    $data[$case] = [
      $case,
      $expected_output,
    ];

    // Test Minify HTML Style Replacement.
    $expected_output = "<html><head><title>Test HTML</title></head><body><style>\n";
    $expected_output .= "body { color: #fff; }\n";
    $expected_output .= "</style></body></html>";
    $case = 'style_replacement';
    $data[$case] = [
      $case,
      $expected_output,
    ];

    // Test Minify HTML Comment Stripping.
    $case = 'comment_stripping';
    $data[$case] = [
      $case,
      "<html><head><title>Test HTML</title></head><body></body></html>",
    ];

    return $data;
  }

  /**
   * Test possible cases which covered by the module.
   *
   * @param string $case
   *   Case which currently tested. Passed to the route parameters to reach
   *   controller with required response.
   * @param string $expected_output
   *   Expected response result.
   *
   * @dataProvider dataProvider
   */
  public function testMinifyHtml($case, $expected_output) {
    $this->drupalGet(Url::fromRoute('minifyhtml_test.case', ['case' => $case]));
    $actual_output = $this->getSession()->getPage()->getContent();
    $this->assertEquals($expected_output, $actual_output, 'Minified source not matches expected output.');
    $this->assertSame($expected_output, $actual_output, 'Minified source not matches expected output.');
  }

}
