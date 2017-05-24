<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient;

use Jose\Factory\JWKFactory;
use Jose\Loader;
use Yii;

/**
 * OpenIdConnect
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'google' => [
 *                 'class' => 'yii\authclient\OpenIdConnect',
 *                 'configUrl' => 'https://accounts.google.com/.well-known/openid-configuration',
 *                 'clientId' => 'google_client_id',
 *                 'clientSecret' => 'google_client_secret',
 *                 'name' => 'google',
 *                 'title' => 'Google OpenID Connect',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ```
 *
 * This class requires `spomky-labs/jose` libraty to be installed for JWS verification. This can be done via composer:
 *
 * ```
 * composer require --prefer-dist "spomky-labs/jose::~5.0.6"
 * ```
 *
 * @see http://openid.net/connect/
 *
 * @property array $configParams OpenID provider configuration parameters.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.3
 */
class OpenIdConnect extends OAuth2
{
    /**
     * @inheritdoc
     */
    public $scope = 'openid';
    /**
     * @var string OpenID Issuer (provider) base URL, e.g. `https://example.com`.
     */
    public $issuerUrl;
    /**
     * @var array JWS algorithms, which are authorised for use.
     */
    public $allowedAlgorithms = [
        'HS256', 'HS384', 'HS512',
        'ES256', 'ES384', 'ES512',
        'RS256', 'RS384', 'RS512',
        'PS256', 'PS384', 'PS512'
    ];
    /**
     * @var bool whether to validate/decrypt JWS received with Auth token.
     * Note: this functionality requires `spomky-labs/jose` composer package to be installed.
     * You can disable this option in case of usage of trusted OpenIDConnect provider, however this violates
     * the protocol rules, so you are doing it on your own risk.
     */
    public $validateJws = true;

    /**
     * @var array OpenID provider configuration parameters.
     */
    private $_configParams;


    /**
     * @return array OpenID provider configuration parameters.
     */
    public function getConfigParams()
    {
        if ($this->_configParams === null) {
            $this->_configParams = $this->discoverConfig();
        }
        return $this->_configParams;
    }

    /**
     * Returns particular configuration parameter value.
     * @param string $name configuration parameter name.
     * @return mixed configuration parameter value.
     */
    public function getConfigParam($name)
    {
        if ($this->_configParams === null) {
            $this->_configParams = $this->discoverConfig();
        }
        return $this->_configParams[$name];
    }

    /**
     * Discovers OpenID Provider configuration parameters.
     * @return array OpenID Provider configuration parameters.
     * @throws InvalidResponseException on failure.
     */
    protected function discoverConfig()
    {
        $request = $this->createRequest();
        $configUrl = rtrim($this->issuerUrl, '/') . '/.well-known/openid-configuration';
        $request->setMethod('GET')
            ->setUrl($configUrl);
        $response = $this->sendRequest($request);
        return $response;
    }

    /**
     * @inheritdoc
     */
    public function buildAuthUrl(array $params = [])
    {
        if ($this->authUrl === null) {
            $this->authUrl = $this->getConfigParam('authorization_endpoint');
        }
        return parent::buildAuthUrl($params);
    }

    /**
     * @inheritdoc
     */
    public function fetchAccessToken($authCode, array $params = [])
    {
        if ($this->tokenUrl === null) {
            $this->tokenUrl = $this->getConfigParam('token_endpoint');
        }
        return parent::fetchAccessToken($authCode, $params);
    }

    /**
     * @inheritdoc
     */
    public function refreshAccessToken(OAuthToken $token)
    {
        if ($this->tokenUrl === null) {
            $this->tokenUrl = $this->getConfigParam('token_endpoint');
        }
        return parent::refreshAccessToken($token);
    }

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        return $this->api($this->getConfigParam('userinfo_endpoint'), 'GET');
    }

    /**
     * Composes default [[returnUrl]] value.
     * @return string return URL.
     */
    protected function defaultReturnUrl()
    {
        $params = $_GET;
        // OAuth2 specifics :
        unset($params['code']);
        unset($params['state']);
        // OpenIdConnect specifics :
        unset($params['authuser']);
        unset($params['session_state']);
        unset($params['prompt']);
        $params[0] = Yii::$app->controller->getRoute();

        return Yii::$app->getUrlManager()->createAbsoluteUrl($params);
    }

    /**
     * @inheritdoc
     */
    protected function createToken(array $tokenConfig = [])
    {
        if ($this->validateJws) {
            $jwsData = $this->loadJws($tokenConfig['params']['id_token']);
            $tokenConfig['params'] = array_merge($tokenConfig['params'], $jwsData);
        }

        return parent::createToken($tokenConfig);
    }

    /**
     * Decrypts/validates JWS, returning related data.
     * @param string $jws raw JWS input.
     * @return array JWS underlying data.
     * @throws \Exception on invalid JWS signature.
     */
    protected function loadJws($jws)
    {
        $jwkSet = JWKFactory::createFromJKU($this->getConfigParam('jwks_uri'));
        $loader = new Loader();
        return $loader->loadAndVerifySignatureUsingKeySet($jws, $jwkSet, $this->allowedAlgorithms)->getPayload();
    }
}