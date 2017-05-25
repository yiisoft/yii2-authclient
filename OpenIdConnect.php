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
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\web\HttpException;

/**
 * OpenIdConnect serves as a client for the OpenIdConnect flow.
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
 *                 'issuerUrl' => 'https://accounts.google.com',
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
 * This class requires `spomky-labs/jose` library to be installed for JWS verification. This can be done via composer:
 *
 * ```
 * composer require --prefer-dist "spomky-labs/jose::~5.0.6"
 * ```
 *
 * Note: if you are using well-trusted OpenIdConnect provider, you may disable [[validateJws]], making installation of
 * `spomky-labs/jose` library redundant, however it is not recommended as it violates the protocol specification.
 *
 * @see http://openid.net/connect/
 * @see OAuth2
 *
 * @property bool $validateAuthNonce whether to use and validate auth 'nonce' parameter in authentication flow.
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
     * @var bool whether to validate/decrypt JWS received with Auth token.
     * Note: this functionality requires `spomky-labs/jose` composer package to be installed.
     * You can disable this option in case of usage of trusted OpenIDConnect provider, however this violates
     * the protocol rules, so you are doing it on your own risk.
     */
    public $validateJws = true;
    /**
     * @var array JWS algorithms, which are allowed to be used.
     * These are used by `spomky-labs/jose` library for JWS validation/decryption.
     * Make sure `spomky-labs/jose` supports the particular algorithm before adding it here.
     */
    public $allowedJwsAlgorithms = [
        'HS256', 'HS384', 'HS512',
        'ES256', 'ES384', 'ES512',
        'RS256', 'RS384', 'RS512',
        'PS256', 'PS384', 'PS512'
    ];

    /**
     * @var bool|null whether to use and validate auth 'nonce' parameter in authentication flow.
     * The option is used for preventing replay attacks.
     */
    private $_validateAuthNonce;
    /**
     * @var array OpenID provider configuration parameters.
     */
    private $_configParams;


    /**
     * @return bool whether to use and validate auth 'nonce' parameter in authentication flow.
     */
    public function getValidateAuthNonce()
    {
        if ($this->_validateAuthNonce === null) {
            $this->_validateAuthNonce = $this->validateJws && in_array('nonce', $this->getConfigParam('claims_supported'));
        }
        return $this->_validateAuthNonce;
    }

    /**
     * @param bool $validateAuthNonce whether to use and validate auth 'nonce' parameter in authentication flow.
     */
    public function setValidateAuthNonce($validateAuthNonce)
    {
        $this->_validateAuthNonce = $validateAuthNonce;
    }

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

        if (!isset($params['nonce']) && $this->getValidateAuthNonce()) {
            $nonce = $this->generateAuthNonce();
            $this->setState('authNonce', $nonce);
            $params['nonce'] = $nonce;
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
     * @inheritdoc
     */
    protected function applyClientCredentialsToRequest($request)
    {
        $supportedAuthMethods = $this->getConfigParam('token_endpoint_auth_methods_supported');

        if (in_array('client_secret_basic', $supportedAuthMethods)) {
            $request->addHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret)
            ]);
        } elseif (in_array('client_secret_post', $supportedAuthMethods)) {
            $request->addData([
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);
        } elseif (in_array('client_secret_jwt', $supportedAuthMethods)) {
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256',
            ];
            $payload = [
                'iss' => $this->clientId,
                'sub' => $this->clientId,
                'aud' => $this->tokenUrl,
                'jti' => $this->generateAuthNonce(),
                'iat' => time(),
                'exp' => time() + 3600,
            ];

            $signatureBaseString = base64_encode(Json::encode($header)) . '.' . base64_encode(Json::encode($payload));
            $signatureMethod = new HmacSha(['algorithm' => 'sha256']);
            $signature = $signatureMethod->generateSignature($signatureBaseString, $this->clientSecret);

            $assertion = $signatureBaseString . '.' . $signature;

            $request->addData([
                'assertion' => $assertion,
            ]);
        } else {
            throw new InvalidConfigException('Unable to authenticate request: none of following auth methods is suported: ' . implode(', ', $supportedAuthMethods));
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultReturnUrl()
    {
        $params = $_GET;
        // OAuth2 specifics :
        unset($params['code']);
        unset($params['state']);
        // OpenIdConnect specifics :
        unset($params['nonce']);
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
            $this->validateClaims($jwsData);
            $tokenConfig['params'] = array_merge($tokenConfig['params'], $jwsData);

            if ($this->getValidateAuthNonce()) {
                $authNonce = $this->getState('authNonce');
                if (!isset($jwsData['nonce']) || empty($authNonce) || strcmp($jwsData['nonce'], $authNonce) !== 0) {
                    throw new HttpException(400, 'Invalid auth nonce');
                } else {
                    $this->removeState('authNonce');
                }
            }
        }

        return parent::createToken($tokenConfig);
    }

    /**
     * Decrypts/validates JWS, returning related data.
     * @param string $jws raw JWS input.
     * @return array JWS underlying data.
     * @throws HttpException on invalid JWS signature.
     */
    protected function loadJws($jws)
    {
        try {
            $jwkSet = JWKFactory::createFromJKU($this->getConfigParam('jwks_uri'));
            $loader = new Loader();
            return $loader->loadAndVerifySignatureUsingKeySet($jws, $jwkSet, $this->allowedJwsAlgorithms)->getPayload();
        } catch (\Exception $e) {
            $message = YII_DEBUG ? 'Unable to verify JWS: ' . $e->getMessage() : 'Invalid JWS';
            throw new HttpException(400, $message, $e->getCode(), $e);
        }
    }

    /**
     * Validates the claims data received from OpenID provider.
     * @param array $claims claims data.
     * @throws HttpException on invalid claims.
     */
    private function validateClaims(array $claims)
    {
        if (!isset($claims['iss']) || (strcmp(rtrim($claims['iss'], '/'), rtrim($this->issuerUrl, '/')) !== 0)) {
            throw new HttpException(400, 'Invalid "iss"');
        }
        if (!isset($claims['aud']) || (strcmp($claims['aud'], $this->clientId) !== 0)) {
            throw new HttpException(400, 'Invalid "aud"');
        }
    }

    /**
     * Generates the auth nonce value.
     * @return string auth nonce value.
     */
    protected function generateAuthNonce()
    {
        return Yii::$app->security->generateRandomString();
    }
}