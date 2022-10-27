<?php

namespace Drupal\o365_rest\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "get_access_token_resource",
 *   label = @Translation("Get access token resource"),
 *   uri_paths = {
 *     "canonical" = "/o365/get-access-token"
 *   }
 * )
 */
class GetAccessTokenResource extends ResourceBase {

  /**
   * The authentication service, used to handle all kinds of auth stuff.
   *
   * @var \Drupal\o365\AuthenticationService
   */
  protected $authenticationService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->authenticationService = $container->get('o365.authentication');
    return $instance;
  }

    /**
     * Return the
     *
     * @return \Drupal\rest\ResourceResponse
     *
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */

  /**
   * Return the current users access token.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   */
    public function get() {
        return new ResourceResponse($this->authenticationService->getAccessToken(), 200);
    }

}
