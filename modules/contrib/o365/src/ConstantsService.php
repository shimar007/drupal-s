<?php

namespace Drupal\o365;

use Drupal;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class ConstantsService.
 */
class ConstantsService {

  /**
   * The config factory interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The modules API config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $apiConfig;

  /**
   * Constructs a new ConstantsService object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory interface.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
    $this->apiConfig = $this->configFactory->get('o365.api_settings');
  }

  /**
   * The url where Microsoft will redirect us too.
   *
   * @var string
   */
  private $redirectUrl = '/o365/callback';

  /**
   * The authorize endpoint root.
   *
   * @var string
   */
  private $authorizeRoot = 'https://login.microsoftonline.com/';

  /**
   * The authorize endpoint path.
   *
   * @var string
   */
  private $authorizePath = '/oauth2/v2.0/authorize';

  /**
   * The token endpoint root.
   *
   * @var string
   */
  private $tokenRoot = 'https://login.microsoftonline.com/';

  /**
   * The token endpoint path.
   *
   * @var string
   */
  private $tokenPath = '/oauth2/v2.0/token';

  /**
   * The name of the temp store.
   *
   * @var string
   */
  private $userTempStoreName = 'o365.tempstore';

  /**
   * The name of the data saved in the temp store.
   *
   * @var string
   */
  private $userTempStoreDataName = 'o365AuthData';

  /**
   * Get the redirect URL.
   *
   * @return string
   *   The redirect url.
   */
  public function getRedirectUrl() {
    return 'https://' . Drupal::request()->getHost() . $this->redirectUrl;
  }

  /**
   * Get the authorize url.
   *
   * @return string
   *   The authorize url.
   */
  public function getAuthorizeUrl() {
    $tenant = empty($this->apiConfig->get('tenant_id')) ? 'common' : $this->apiConfig->get('tenant_id');

    return $this->authorizeRoot . $tenant . $this->authorizePath;
  }

  /**
   * Get the token url.
   *
   * @return string
   *   The token url.
   */
  public function getTokenUrl() {
    $tenant = empty($this->apiConfig->get('tenant_id')) ? 'common' : $this->apiConfig->get('tenant_id');

    return $this->tokenRoot . $tenant . $this->tokenPath;
  }

  /**
   * Get the user temp store name.
   *
   * @return string
   *   The user temp store name.
   */
  public function getUserTempStoreName() {
    return $this->userTempStoreName;
  }

  /**
   * Get the user temp store data name.
   *
   * @return string
   *   The user temp store data name.
   */
  public function getUserTempStoreDataName() {
    return $this->userTempStoreDataName;
  }

  /**
   * Get the cookie expire timestamp.
   *
   * @return int
   *   The expire timestamp.
   */
  public function getCookieExpire() {
    return time() + 3600;
  }

}
