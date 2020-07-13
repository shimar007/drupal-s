<?php

namespace Drupal\minifyhtml_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\HtmlResponse;

/**
 * Class Cases.
 *
 * @package Drupal\minifyhtml_test\Controller
 */
class Cases extends ControllerBase {

  /**
   * Collection of endpoints with test cases.
   *
   * See dataProvider for expected results.
   *
   * @param string $case
   *   Test case key.
   *
   * @return \Drupal\Core\Render\HtmlResponse
   *   Test response.
   *
   * @see \Drupal\Tests\minifyhtml\Functional\ResponseTest::dataProvider()
   */
  public function item($case) {
    $input = '';
    switch ($case) {
      case 'textarea_replacement':
        // Test Minify HTML Textarea Replacement.
        $input .= "<html>\n";
        $input .= "  <head>\n";
        $input .= "    <title>Test HTML</title>\n";
        $input .= "  </head>\n";
        $input .= "  <body>\n";
        $input .= "    <textarea cols=\"55\" rows=\"31\">\n";
        $input .= "Content in here will not matter.\n";
        $input .= "Even multiline content.\n";
        $input .= "</textarea>\n";
        $input .= "  </body>\n";
        $input .= "</html>\n";
        break;

      case 'pre_replacement':
        // Test Minify HTML Pre Replacement.
        $input .= "<html>\n";
        $input .= "  <head>\n";
        $input .= "    <title>Test HTML</title>\n";
        $input .= "  </head>\n";
        $input .= "  <body>\n";
        $input .= "    <pre>\n";
        $input .= "  Indented content.\n";
        $input .= "         Weirdly Indented content.\n";
        $input .= "Non-indented content.\n";
        $input .= "</pre>\n";
        $input .= "  </body>\n";
        $input .= "</html>\n";
        break;

      case 'iframe_replacement':
        // Test Minify HTML Iframe Replacement.
        $input .= "<html>\n";
        $input .= "  <head>\n";
        $input .= "    <title>Test HTML</title>\n";
        $input .= "  </head>\n";
        $input .= "  <body>\n";
        $input .= "    <iframe src=\"\" width=\"100\" height=\"100\" ></iframe>\n";
        $input .= "  </body>\n";
        $input .= "</html>\n";
        break;

      case 'script_replacement':
        // Test Minify HTML Script Replacement.
        $input .= "<html>\n";
        $input .= "  <head>\n";
        $input .= "    <title>Test HTML</title>\n";
        $input .= "  </head>\n";
        $input .= "  <body>\n";
        $input .= "    <script>\n";
        $input .= "alert('test');\n";
        $input .= "    </script>\n";
        $input .= "  </body>\n";
        $input .= "</html>\n";
        break;

      case 'style_replacement':
        // Test Minify HTML Style Replacement.
        $input .= "<html>\n";
        $input .= "  <head>\n";
        $input .= "    <title>Test HTML</title>\n";
        $input .= "  </head>\n";
        $input .= "  <body>\n";
        $input .= "    <style>\n";
        $input .= "body { color: #fff; }\n";
        $input .= "    </style>\n";
        $input .= "  </body>\n";
        $input .= "</html>\n";
        break;

      case 'comment_stripping':
        // Test Minify HTML Comment Stripping.
        $input .= "<html>\n";
        $input .= "  <head>\n";
        $input .= "    <title>Test HTML</title>\n";
        $input .= "  </head>\n";
        $input .= "  <body>\n";
        $input .= "<!-- The body goes here //-->";
        $input .= "  </body>\n";
        $input .= "</html>\n";
        break;
    }

    return HtmlResponse::create($input);
  }

}
