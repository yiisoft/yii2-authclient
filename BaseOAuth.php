<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient;

use yii\base\Exception;
use yii\base\InvalidParamException;
use Yii;

/**
 * BaseOAuth is a base class for the OAuth clients.
 *
 * @see http://oauth.net/
 *
 * @property OAuthToken $accessToken Auth token instance. Note that the type of this property differs in
 * getter and setter. See [[getAccessToken()]] and [[setAccessToken()]] for details.
 * @property array $requestOptions HTTP request options.
 * @property string $returnUrl Return URL.
 * @property signature\BaseMethod $signatureMethod Signature method instance. Note that the type of this
 * property differs in getter and setter. See [[getSignatureMethod()]] and [[setSignatureMethod()]] for details.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class BaseOAuth extends BaseClient implements ClientInterface
{
    /**
     * @var string protocol version.
     */
    public $version = '1.0';
    /**
     * @var string API base URL.
     */
    public $apiBaseUrl;
    /**
     * @var string authorize URL.
     */
    public $authUrl;
    /**
     * @var string auth request scope.
     */
    public $scope;
    /**
     * @var boolean whether to automatically perform 'refresh access token' request on expired access token.
     * @since 2.0.6
     */
    public $autoRefreshAccessToken = true;

    /**
     * @var string URL, which user will be redirected after authentication at the OAuth provider web site.
     * Note: this should be absolute URL (with http:// or https:// leading).
     * By default current URL will be used.
     */
    private $_returnUrl;
    /**
     * @var array cURL request options. Option values from this field will overwrite corresponding
     * values from [[defaultRequestOptions()]].
     */
    private $_requestOptions = [];
    /**
     * @var OAuthToken|array access token instance or its array configuration.
     */
    private $_accessToken;
    /**
     * @var signature\BaseMethod|array signature method instance or its array configuration.
     */
    private $_signatureMethod = [];


    /**
     * @param string $returnUrl return URL
     */
    public function setReturnUrl($returnUrl)
    {
        $this->_returnUrl = $returnUrl;
    }

    /**
     * @return string return URL.
     */
    public function getReturnUrl()
    {
        if ($this->_returnUrl === null) {
            $this->_returnUrl = $this->defaultReturnUrl();
        }
        return $this->_returnUrl;
    }

    /**
     * @param array $options HTTP request options.
     */
    public function setRequestOptions(array $options)
    {
        $this->_requestOptions = $options;
    }

    /**
     * @return array HTTP request options.
     */
    public function getRequestOptions()
    {
        return $this->_requestOptions;
    }

    /**
     * @param array|OAuthToken $token
     */
    public function setAccessToken($token)
    {
        if (!is_object($token)) {
            $token = $this->createToken($token);
        }
        $this->_accessToken = $token;
        $this->saveAccessToken($token);
    }

    /**
     * @return OAuthToken auth token instance.
     */
    public function getAccessToken()
    {
        if (!is_object($this->_accessToken)) {
            $this->_accessToken = $this->restoreAccessToken();
        }

        return $this->_accessToken;
    }

    /**
     * @param array|signature\BaseMethod $signatureMethod signature method instance or its array configuration.
     * @throws InvalidParamException on wrong argument.
     */
    public function setSignatureMethod($signatureMethod)
    {
        if (!is_object($signatureMethod) && !is_array($signatureMethod)) {
            throw new InvalidParamException('"' . get_class($this) . '::signatureMethod" should be instance of "\yii\autclient\signature\BaseMethod" or its array configuration. "' . gettype($signatureMethod) . '" has been given.');
        }
        $this->_signatureMethod = $signatureMethod;
    }

    /**
     * @return signature\BaseMethod signature method instance.
     */
    public function getSignatureMethod()
    {
        if (!is_object($this->_signatureMethod)) {
            $this->_signatureMethod = $this->createSignatureMethod($this->_signatureMethod);
        }

        return $this->_signatureMethod;
    }

    /**
     * Composes default [[returnUrl]] value.
     * @return string return URL.
     */
    protected function defaultReturnUrl()
    {
        return Yii::$app->getRequest()->getAbsoluteUrl();
    }

    /**
     * Creates HTTP request instance.
     * @param array $config request object configuration.
     * @return \yii\httpclient\Request HTTP request instance.
     */
    protected function createRequest(array $config = [])
    {
        $request = $this->getHttpClient()->createRequest();

        if (isset($config['headers'])) {
            $request->addHeaders($config['headers']);
            unset($config['headers']);
        }
        if (isset($config['data'])) {
            if (is_array($config['data'])) {
                $request->setData($config['data']);
            } else {
                $request->setContent($config['data']);
            }
            unset($config['data']);
        }
        if (isset($config['options'])) {
            $request->addOptions($config['options']);
            unset($config['options']);
        }

        Yii::configure($request, $config);

        return $request;
    }

    /**
     * Sends HTTP request.
     * @param string $method request type.
     * @param string $url request URL.
     * @param array|string $data request data.
     * @param array $headers additional request headers.
     * @return array response.
     * @throws Exception on failure.
     */
    protected function sendRequest($method, $url, $data = [], $headers = [])
    {
        $response = $this->createRequest([
                'method' => $method,
                'url' => $url,
                'data' => $data,
                'headers' => $headers,
            ])
            ->addOptions($this->defaultRequestOptions())
            ->addOptions($this->getRequestOptions())
            ->send();

        if (!$response->getIsOk()) {
            throw new InvalidResponseException($response, 'Request failed with code: ' . $response->getStatusCode() . ', message: ' . $response->getContent());
        }

        return $response->getData();
    }

    /**
     * Returns default HTTP request options.
     * @return array HTTP request options.
     */
    protected function defaultRequestOptions()
    {
        return [
            'userAgent' => Yii::$app->name . ' OAuth ' . $this->version . ' Client',
            'timeout' => 30,
            'sslVerifyPeer' => false,
        ];
    }

    /**
     * Creates signature method instance from its configuration.
     * @param array $signatureMethodConfig signature method configuration.
     * @return signature\BaseMethod signature method instance.
     */
    protected function createSignatureMethod(array $signatureMethodConfig)
    {
        if (!array_key_exists('class', $signatureMethodConfig)) {
            $signatureMethodConfig['class'] = signature\HmacSha1::className();
        }
        return Yii::createObject($signatureMethodConfig);
    }

    /**
     * Creates token from its configuration.
     * @param array $tokenConfig token configuration.
     * @return OAuthToken token instance.
     */
    protected function createToken(array $tokenConfig = [])
    {
        if (!array_key_exists('class', $tokenConfig)) {
            $tokenConfig['class'] = OAuthToken::className();
        }
        return Yii::createObject($tokenConfig);
    }

    /**
     * Composes URL from base URL and GET params.
     * @param string $url base URL.
     * @param array $params GET params.
     * @return string composed URL.
     */
    protected function composeUrl($url, array $params = [])
    {
        if (strpos($url, '?') === false) {
            $url .= '?';
        } else {
            $url .= '&';
        }
        $url .= http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        return $url;
    }

    /**
     * Saves token as persistent state.
     * @param OAuthToken $token auth token
     * @return $this the object itself.
     */
    protected function saveAccessToken(OAuthToken $token)
    {
        return $this->setState('token', $token);
    }

    /**
     * Restores access token.
     * @return OAuthToken auth token.
     */
    protected function restoreAccessToken()
    {
        $token = $this->getState('token');
        if (is_object($token)) {
            /* @var $token OAuthToken */
            if ($token->getIsExpired() && $this->autoRefreshAccessToken) {
                $token = $this->refreshAccessToken($token);
            }
        }
        return $token;
    }

    /**
     * Sets persistent state.
     * @param string $key state key.
     * @param mixed $value state value
     * @return $this the object itself
     */
    protected function setState($key, $value)
    {
        if (!Yii::$app->has('session')) {
            return $this;
        }
        /* @var \yii\web\Session $session */
        $session = Yii::$app->get('session');
        $key = $this->getStateKeyPrefix() . $key;
        $session->set($key, $value);
        return $this;
    }

    /**
     * Returns persistent state value.
     * @param string $key state key.
     * @return mixed state value.
     */
    protected function getState($key)
    {
        if (!Yii::$app->has('session')) {
            return null;
        }
        /* @var \yii\web\Session $session */
        $session = Yii::$app->get('session');
        $key = $this->getStateKeyPrefix() . $key;
        $value = $session->get($key);
        return $value;
    }

    /**
     * Removes persistent state value.
     * @param string $key state key.
     * @return boolean success.
     */
    protected function removeState($key)
    {
        if (!Yii::$app->has('session')) {
            return true;
        }
        /* @var \yii\web\Session $session */
        $session = Yii::$app->get('session');
        $key = $this->getStateKeyPrefix() . $key;
        $session->remove($key);
        return true;
    }

    /**
     * Returns session key prefix, which is used to store internal states.
     * @return string session key prefix.
     */
    protected function getStateKeyPrefix()
    {
        return get_class($this) . '_' . sha1($this->authUrl) . '_';
    }

    /**
     * Performs request to the OAuth API.
     * @param string $apiSubUrl API sub URL, which will be append to [[apiBaseUrl]], or absolute API URL.
     * @param string $method request method.
     * @param array|string $data request data.
     * @param array $headers additional request headers.
     * @return array API response
     * @throws Exception on failure.
     */
    public function api($apiSubUrl, $method = 'GET', $data = [], $headers = [])
    {
        if (preg_match('/^https?:\\/\\//is', $apiSubUrl)) {
            $url = $apiSubUrl;
        } else {
            $url = $this->apiBaseUrl . '/' . $apiSubUrl;
        }
        $accessToken = $this->getAccessToken();
        if (!is_object($accessToken) || !$accessToken->getIsValid()) {
            throw new Exception('Invalid access token.');
        }
        return $this->apiInternal($accessToken, $url, $method, $data, $headers);
    }

    /**
     * Gets new auth token to replace expired one.
     * @param OAuthToken $token expired auth token.
     * @return OAuthToken new auth token.
     */
    abstract public function refreshAccessToken(OAuthToken $token);

    /**
     * Performs request to the OAuth API.
     * @param OAuthToken $accessToken actual access token.
     * @param string $url absolute API URL.
     * @param string $method request method.
     * @param array|string $data request data.
     * @param array $headers additional request headers.
     * @return array API response.
     * @throws Exception on failure.
     */
    abstract protected function apiInternal($accessToken, $url, $method, $data, $headers);
}
