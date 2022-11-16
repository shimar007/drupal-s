<?php

namespace Drupal\Tests\rest_menu_items\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\Tests\rest\Functional\CookieResourceTestTrait;
use Drupal\Tests\rest\Functional\ResourceTestBase;
use Psr\Http\Message\ResponseInterface;

/**
 * Test the output and availabillity of the rest menu items endpoint.
 *
 * @group rest_menu_items
 */
class RestMenuItemsTest extends ResourceTestBase {

  use CookieResourceTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'menu_ui',
    'menu_link_content',
    'rest_menu_items_test',
  ];

  /**
   * The generated menu name.
   *
   * @var string
   */
  protected $menuName;

  /**
   * A admin user.
   *
   * @var \Drupal\user\Entity\User|false
   */
  protected $adminUser;

  /**
   * The list of links.
   *
   * @var array
   */
  protected $links = [];

  /**
   * {@inheritdoc}
   */
  protected static $auth = 'cookie';

  /**
   * {@inheritdoc}
   */
  protected static $resourceConfigId = 'rest_menu_item';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $auth = isset(static::$auth) ? [static::$auth] : [];
    $this->provisionResource([static::$format], $auth);

    // Create the links array.
    $this->links = [
      [
        'uri' => '/node',
        'title' => 'REST menu items test internal link',
      ],
      [
        'uri' => 'https://drupal.org',
        'title' => 'Drupal',
      ],
    ];

    // Create users.
    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer menu',
    ]);
  }

  /**
   * Test if the REST menu items endpoint returns the data we expect.
   */
  public function testRestMenuItems() {
    // Create our new menu.
    $this->addCustomMenu();

    // Setup authentication and the endpoint url.
    $this->initAuthentication();
    $request_options = $this->getAuthenticationRequestOptions('GET');
    $url = Url::fromRoute('rest.rest_menu_item.GET')
      ->setRouteParameter('menu_name', $this->menuName)
      ->setRouteParameter('_format', static::$format);

    // Test if we get a 403 when user has no permissions.
    $response = $this->request('GET', $url, $request_options);
    $this->assertResourceErrorResponse(
      403,
      "The 'restful get rest_menu_item' permission is required.",
      $response,
      ['4xx-response', 'http_response'],
      ['user.permissions']
    );

    // Create a user account that has the required permissions to read
    // the rest_menu_item resource via the REST API.
    $this->setUpAuthorization('GET');

    $response = $this->request('GET', $url, $request_options);
    $this->assertResourceResponse(
      200,
      FALSE,
      $response,
      [
        'config:rest.resource.rest_menu_item',
        'config:system.menu.' . $this->menuName,
        'http_response',
      ],
      ['url.query_args', 'user.permissions'],
      FALSE,
      'MISS'
    );

    // Retrieve the endpoint data.
    $response = $this->request('GET', $url, $request_options);
    $this->assertSame(200, $response->getStatusCode());
    $restBody = Json::decode((string) $response->getBody());

    // Test if the content of the endpoint contains data we expect.
    $this->assertEquals('base:node', $restBody[0]['uri']);
    $this->assertEquals($this->links[0]['title'], $restBody[0]['title']);
    $this->assertEquals($this->links[1]['uri'], $restBody[1]['uri']);
    $this->assertEquals($this->links[1]['title'], $restBody[1]['title']);
  }

  /**
   * Add a custom menu.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  protected function addCustomMenu() {
    // Login as admin user.
    $this->drupalLogin($this->adminUser);

    // Try adding a menu using a menu_name that is too long.
    $this->drupalGet('admin/structure/menu/add');
    $this->menuName = strtolower($this->randomMachineName(16));

    $edit = [
      'id' => $this->menuName,
      'description' => '',
      'label' => $this->menuName,
    ];

    $this->drupalGet('admin/structure/menu/add');
    $this->submitForm($edit, 'Save');

    // Add some menu links to our custom menu.
    foreach ($this->links as $weight => $link) {
      $this->drupalGet('admin/structure/menu/manage/' . $this->menuName . '/add');
      $this->submitForm([
        'link[0][uri]' => $link['uri'],
        'title[0][value]' => $link['title'],
        'weight[0][value]' => $weight,
      ], 'Save');
    }

    // Logout so we can continue the rest of our test as a different user.
    $this->drupalLogout();
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpAuthorization($method) {
    switch ($method) {
      case 'GET':
        $this->grantPermissionsToTestedRole(['restful get rest_menu_item']);
        break;

      default:
        throw new \UnexpectedValueException();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function assertResponseWhenMissingAuthentication($method, ResponseInterface $response) {
  }

  /**
   * {@inheritdoc}
   */
  protected function assertNormalizationEdgeCases($method, Url $url, array $request_options) {
  }

  /**
   * {@inheritdoc}
   */
  protected function assertAuthenticationEdgeCases($method, Url $url, array $request_options) {
  }

  /**
   * {@inheritdoc}
   */
  protected function getExpectedUnauthorizedAccessCacheability() {
  }

}
