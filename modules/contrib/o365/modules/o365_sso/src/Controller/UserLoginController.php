<?php

namespace Drupal\o365_sso\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\o365\AuthenticationService;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\o365\GraphService;

/**
 * Class UserLoginController.
 */
class UserLoginController extends ControllerBase {

  /**
   * Drupal\o365\GraphService definition.
   *
   * @var \Drupal\o365\GraphService
   */
  protected $graphService;

  /**
   * Drupal\o365\AuthenticationService definition.
   *
   * @var \Drupal\o365\AuthenticationService
   */
  protected $authenticationService;

  /**
   * Constructs a new UserLoginController object.
   *
   * @param \Drupal\o365\GraphService $o365_graph
   *   The GraphService definition.
   * @param \Drupal\o365\AuthenticationService $authenticationService
   *   The AuthenticationService definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManager definition.
   */
  public function __construct(GraphService $o365_graph, AuthenticationService $authenticationService, EntityTypeManagerInterface $entity_type_manager) {
    $this->graphService = $o365_graph;
    $this->authenticationService = $authenticationService;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('o365.graph'),
      $container->get('o365.authentication'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Login a user.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   The redirect to the set URL in config.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   */
  public function login() {
    // Enable cookie auth.
    $this->authenticationService->useCookieAuth = TRUE;

    // Get user data.
    $userData = $this->graphService->getGraphData('/me');

    // Get user unique identifier.
    $o365_id = $userData['id'];

    // Check whether user account exists.
    $users = $this->entityTypeManager()
      ->getStorage('user')
      ->loadByProperties([
        'o365_id' => $o365_id,
      ]);

    if (!$users) {
      if (!$user = user_load_by_mail($userData['userPrincipalName'])) {
        // We need to create the user.
        $user = User::create();
        $user->setUsername($userData['userPrincipalName']);
        $user->setEmail($userData['userPrincipalName']);
        $user->enforceIsNew();
        $user->set('status', 1);
      }

      $user->set('o365_id', $o365_id);
      $user->save();
    }
    else {
      $user = reset($users);
    }

    // Get config and redirect url after login.
    $config = $this->config('o365.api_settings');
    $redirectUrl = $config->get('redirect_login');

    // Login the user in Drupal.
    user_login_finalize($user);

    // Save the auth data in the session and delete the cookie.
    $this->authenticationService->saveAuthDataFromCookie();

    // Return the redirect.
    return new TrustedRedirectResponse($redirectUrl);
  }

}
