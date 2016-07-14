<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient;

use Yii;
use yii\base\InvalidParamException;

/**
 * OAuth1 serves as a client for the OAuth 1/1.0a flow.
 *
 * In order to acquire access token perform following sequence:
 *
 * ```php
 * use yii\authclient\OAuth1;
 *
 * $oauthClient = new OAuth1();
 * $requestToken = $oauthClient->fetchRequestToken(); // Get request token
 * $url = $oauthClient->buildAuthUrl($requestToken); // Get authorization URL
 * return Yii::$app->getResponse()->redirect($url); // Redirect to authorization URL
 * // After user returns at our site:
 * $accessToken = $oauthClient->fetchAccessToken($requestToken); // Upgrade to access token
 * ```
 *
 * @see http://oauth.net/
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class OAuth1 extends BaseOAuth
{
    /**
     * @var string protocol version.
     */
    public $version = '1.0';
    /**
     * @var string OAuth consumer key.
     */
    public $consumerKey;
    /**
     * @var string OAuth consumer secret.
     */
    public $consumerSecret;
    /**
     * @var string OAuth request token URL.
     */
    public $requestTokenUrl;
    /**
     * @var string request token HTTP method.
     */
    public $requestTokenMethod = 'GET';
    /**
     * @var string OAuth access token URL.
     */
    public $accessTokenUrl;
    /**
     * @var string access token HTTP method.
     */
    public $accessTokenMethod = 'GET';


    /**
     * Fetches the OAuth request token.
     * @param array $params additional request params.
     * @return OAuthToken request token.
     */
    public function fetchRequestToken(array $params = [])
    {
        $this->removeState('token');
        $defaultParams = [
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_callback' => $this->getReturnUrl(),
            //'xoauth_displayname' => Yii::$app->name,
        ];
        if (!empty($this->scope)) {
            $defaultParams['scope'] = $this->scope;
        }

        $request = $this->createRequest()
            ->setMethod($this->requestTokenMethod)
            ->setUrl($this->requestTokenUrl)
            ->setData(array_merge($defaultParams, $params));

        $response = $this->sendRequest($request);

        $token = $this->createToken([
            'params' => $response
        ]);
        $this->setState('requestToken', $token);

        return $token;
    }

    /**
     * Composes user authorization URL.
     * @param OAuthToken $requestToken OAuth request token.
     * @param array $params additional request params.
     * @return string authorize URL
     * @throws InvalidParamException on failure.
     */
    public function buildAuthUrl(OAuthToken $requestToken = null, array $params = [])
    {
        if (!is_object($requestToken)) {
            $requestToken = $this->getState('requestToken');
            if (!is_object($requestToken)) {
                throw new InvalidParamException('Request token is required to build authorize URL!');
            }
        }
        $params['oauth_token'] = $requestToken->getToken();

        return $this->composeUrl($this->authUrl, $params);
    }

    /**
     * Fetches OAuth access token.
     * @param OAuthToken $requestToken OAuth request token.
     * @param string $oauthVerifier OAuth verifier.
     * @param array $params additional request params.
     * @return OAuthToken OAuth access token.
     * @throws InvalidParamException on failure.
     */
    public function fetchAccessToken(OAuthToken $requestToken = null, $oauthVerifier = null, array $params = [])
    {
        if (!is_object($requestToken)) {
            $requestToken = $this->getState('requestToken');
            if (!is_object($requestToken)) {
                throw new InvalidParamException('Request token is required to fetch access token!');
            }
        }
        $this->removeState('requestToken');
        $defaultParams = [
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_token' => $requestToken->getToken()
        ];
        if ($oauthVerifier === null) {
            if (isset($_REQUEST['oauth_verifier'])) {
                $oauthVerifier = $_REQUEST['oauth_verifier'];
            }
        }
        if (!empty($oauthVerifier)) {
            $defaultParams['oauth_verifier'] = $oauthVerifier;
        }

        $request = $this->createRequest()
            ->setMethod($this->accessTokenMethod)
            ->setUrl($this->accessTokenUrl)
            ->setData(array_merge($defaultParams, $params));

        $this->signRequest($request, $requestToken);

        $response = $this->sendRequest($request);

        $token = $this->createToken([
            'params' => $response
        ]);
        $this->setAccessToken($token);

        return $token;
    }

    /**
     * @inheritdoc
     */
    protected function sendRequest($request)
    {
        $this->signRequest($request);
        return parent::sendRequest($request);
    }

    /**
     * @inheritdoc
     */
    public function applyAccessTokenToRequest($request, $accessToken)
    {
        $data = $request->getData();
        $data['oauth_consumer_key'] = $this->consumerKey;
        $data['oauth_token'] = $accessToken->getToken();
        $request->setData($data);
    }

    /**
     * Gets new auth token to replace expired one.
     * @param OAuthToken $token expired auth token.
     * @return OAuthToken new auth token.
     */
    public function refreshAccessToken(OAuthToken $token)
    {
        // @todo
        return null;
    }

    /**
     * Composes default [[returnUrl]] value.
     * @return string return URL.
     */
    protected function defaultReturnUrl()
    {
        $params = $_GET;
        unset($params['oauth_token']);
        $params[0] = Yii::$app->controller->getRoute();

        return Yii::$app->getUrlManager()->createAbsoluteUrl($params);
    }

    /**
     * Generates nonce value.
     * @return string nonce value.
     */
    protected function generateNonce()
    {
        return md5(microtime() . mt_rand());
    }

    /**
     * Generates timestamp.
     * @return integer timestamp.
     */
    protected function generateTimestamp()
    {
        return time();
    }

    /**
     * Generate common request params like version, timestamp etc.
     * @return array common request params.
     */
    protected function generateCommonRequestParams()
    {
        $params = [
            'oauth_version' => $this->version,
            'oauth_nonce' => $this->generateNonce(),
            'oauth_timestamp' => $this->generateTimestamp(),
        ];

        return $params;
    }

    /**
     * Sign given request with [[signatureMethod]].
     * @param \yii\httpclient\Request $request request instance.
     * @param OAuthToken|null $token OAuth token to be used for signature, if not set [[accessToken]] will be used.
     * @since 2.1 this method is public.
     */
    public function signRequest($request, $token = null)
    {
        $params = $request->getData();

        if (isset($params['oauth_signature_method'])) {
            // avoid double sign of request
            return;
        }

        if (empty($params)) {
            $params = $this->generateCommonRequestParams();
        } else {
            $params = array_merge($this->generateCommonRequestParams(), $params);
        }

        $url = $request->getUrl();
        if (strpos($url, '://') === false) {
            $url = $request->client->baseUrl . '/' . $url;
        }

        $signatureMethod = $this->getSignatureMethod();

        $params['oauth_signature_method'] = $signatureMethod->getName();
        $signatureBaseString = $this->composeSignatureBaseString($request->getMethod(), $url, $params);
        $signatureKey = $this->composeSignatureKey($token);
        $params['oauth_signature'] = $signatureMethod->generateSignature($signatureBaseString, $signatureKey);

        $request->setData($params);

        $authorizationHeader = $this->composeAuthorizationHeader($params);
        if (!empty($authorizationHeader)) {
            $request->addHeaders($authorizationHeader);
        }
    }

    /**
     * Creates signature base string, which will be signed by [[signatureMethod]].
     * @param string $method request method.
     * @param string $url request URL.
     * @param array $params request params.
     * @return string base signature string.
     */
    protected function composeSignatureBaseString($method, $url, array $params)
    {
        unset($params['oauth_signature']);
        uksort($params, 'strcmp'); // Parameters are sorted by name, using lexicographical byte value ordering. Ref: Spec: 9.1.1
        $parts = [
            strtoupper($method),
            $url,
            http_build_query($params, '', '&', PHP_QUERY_RFC3986)
        ];
        $parts = array_map('rawurlencode', $parts);

        return implode('&', $parts);
    }

    /**
     * Composes request signature key.
     * @param OAuthToken|null $token OAuth token to be used for signature key.
     * @return string signature key.
     */
    protected function composeSignatureKey($token = null)
    {
        $signatureKeyParts = [
            $this->consumerSecret
        ];

        if ($token === null) {
            $token = $this->getAccessToken();
        }
        if (is_object($token)) {
            $signatureKeyParts[] = $token->getTokenSecret();
        } else {
            $signatureKeyParts[] = '';
        }

        $signatureKeyParts = array_map('rawurlencode', $signatureKeyParts);

        return implode('&', $signatureKeyParts);
    }

    /**
     * Composes authorization header.
     * @param array $params request params.
     * @param string $realm authorization realm.
     * @return array authorization header in format: [name => content].
     */
    protected function composeAuthorizationHeader(array $params, $realm = '')
    {
        $header = 'OAuth';
        $headerParams = [];
        if (!empty($realm)) {
            $headerParams[] = 'realm="' . rawurlencode($realm) . '"';
        }
        foreach ($params as $key => $value) {
            if (substr_compare($key, 'oauth', 0, 5)) {
                continue;
            }
            $headerParams[] = rawurlencode($key) . '="' . rawurlencode($value) . '"';
        }
        if (!empty($headerParams)) {
            $header .= ' ' . implode(', ', $headerParams);
        }

        return ['Authorization' => $header];
    }
}
