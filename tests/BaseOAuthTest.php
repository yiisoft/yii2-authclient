<?php

namespace yiiunit\extensions\authclient;

use yii\authclient\signature\PlainText;
use yii\authclient\OAuthToken;
use yii\authclient\BaseOAuth;

class BaseOAuthTest extends TestCase
{
    protected function setUp()
    {
        $this->mockApplication();
    }

    /**
     * Creates test OAuth client instance.
     * @return BaseOAuth oauth client.
     */
    protected function createOAuthClient()
    {
        $oauthClient = $this->getMock(BaseOAuth::className(), ['composeRequestCurlOptions', 'refreshAccessToken', 'apiInternal']);
        return $oauthClient;
    }

    /**
     * Invokes the OAuth client method even if it is protected.
     * @param  BaseOAuth $oauthClient OAuth client instance.
     * @param  string    $methodName  name of the method to be invoked.
     * @param  array     $arguments   method arguments.
     * @return mixed     method invoke result.
     */
    protected function invokeOAuthClientMethod($oauthClient, $methodName, array $arguments = [])
    {
        $classReflection = new \ReflectionClass(get_class($oauthClient));
        $methodReflection = $classReflection->getMethod($methodName);
        $methodReflection->setAccessible(true);
        $result = $methodReflection->invokeArgs($oauthClient, $arguments);
        $methodReflection->setAccessible(false);

        return $result;
    }

    // Tests :

    public function testSetGet()
    {
        $oauthClient = $this->createOAuthClient();

        $returnUrl = 'http://test.return.url';
        $oauthClient->setReturnUrl($returnUrl);
        $this->assertEquals($returnUrl, $oauthClient->getReturnUrl(), 'Unable to setup return URL!');

        $curlOptions = [
            'option1' => 'value1',
            'option2' => 'value2',
        ];
        $oauthClient->setRequestOptions($curlOptions);
        $this->assertEquals($curlOptions, $oauthClient->getRequestOptions(), 'Unable to setup cURL options!');
    }

    public function testSetupComponents()
    {
        $oauthClient = $this->createOAuthClient();

        $oauthToken = new OAuthToken();
        $oauthClient->setAccessToken($oauthToken);
        $this->assertEquals($oauthToken, $oauthClient->getAccessToken(), 'Unable to setup token!');

        $oauthSignatureMethod = new PlainText();
        $oauthClient->setSignatureMethod($oauthSignatureMethod);
        $this->assertEquals($oauthSignatureMethod, $oauthClient->getSignatureMethod(), 'Unable to setup signature method!');
    }

    /**
     * @depends testSetupComponents
     */
    public function testSetupComponentsByConfig()
    {
        $oauthClient = $this->createOAuthClient();

        $oauthToken = [
            'token' => 'test_token',
            'tokenSecret' => 'test_token_secret',
        ];
        $oauthClient->setAccessToken($oauthToken);
        $this->assertEquals($oauthToken['token'], $oauthClient->getAccessToken()->getToken(), 'Unable to setup token as config!');

        $oauthSignatureMethod = [
            'class' => 'yii\authclient\signature\PlainText'
        ];
        $oauthClient->setSignatureMethod($oauthSignatureMethod);
        $returnedSignatureMethod = $oauthClient->getSignatureMethod();
        $this->assertEquals($oauthSignatureMethod['class'], get_class($returnedSignatureMethod), 'Unable to setup signature method as config!');
    }

    /**
     * Data provider for [[testComposeUrl()]].
     * @return array test data.
     */
    public function composeUrlDataProvider()
    {
        return [
            [
                'http://test.url',
                [
                    'param1' => 'value1',
                    'param2' => 'value2',
                ],
                'http://test.url?param1=value1&param2=value2',
            ],
            [
                'http://test.url?with=some',
                [
                    'param1' => 'value1',
                    'param2' => 'value2',
                ],
                'http://test.url?with=some&param1=value1&param2=value2',
            ],
        ];
    }

    /**
     * @dataProvider composeUrlDataProvider
     *
     * @param string $url         request URL.
     * @param array  $params      request params
     * @param string $expectedUrl expected composed URL.
     */
    public function testComposeUrl($url, array $params, $expectedUrl)
    {
        $oauthClient = $this->createOAuthClient();
        $composedUrl = $this->invokeOAuthClientMethod($oauthClient, 'composeUrl', [$url, $params]);
        $this->assertEquals($expectedUrl, $composedUrl);
    }

    /**
     * Data provider for [[testApiUrl]].
     * @return array test data.
     */
    public function apiUrlDataProvider()
    {
        return [
            [
                'http://api.base.url',
                'sub/url',
                'http://api.base.url/sub/url',
            ],
            [
                'http://api.base.url',
                'http://api.base.url/sub/url',
                'http://api.base.url/sub/url',
            ],
            [
                'http://api.base.url',
                'https://api.base.url/sub/url',
                'https://api.base.url/sub/url',
            ],
        ];
    }

    /**
     * @dataProvider apiUrlDataProvider
     *
     * @param $apiBaseUrl
     * @param $apiSubUrl
     * @param $expectedApiFullUrl
     */
    public function testApiUrl($apiBaseUrl, $apiSubUrl, $expectedApiFullUrl)
    {
        $oauthClient = $this->createOAuthClient();
        $oauthClient->expects($this->any())->method('apiInternal')->will($this->returnArgument(1));

        $accessToken = new OAuthToken();
        $accessToken->setToken('test_access_token');
        $accessToken->setExpireDuration(1000);
        $oauthClient->setAccessToken($accessToken);

        $oauthClient->apiBaseUrl = $apiBaseUrl;

        $this->assertEquals($expectedApiFullUrl, $oauthClient->api($apiSubUrl));
    }
}
