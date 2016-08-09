<?php

namespace yiiunit\extensions\authclient;

use yii\authclient\OAuth1;
use yii\authclient\signature\PlainText;
use yii\authclient\OAuthToken;

class OAuth1Test extends TestCase
{
    protected function setUp()
    {
        $config = [
            'components' => [
                'request' => [
                    'hostInfo' => 'http://testdomain.com',
                    'scriptUrl' => '/index.php',
                ],
            ]
        ];
        $this->mockApplication($config, '\yii\web\Application');
    }

    /**
     * Creates test OAuth1 client instance.
     * @return OAuth1 oauth client.
     */
    protected function createClient()
    {
        $oauthClient = $this->getMock(OAuth1::className(), ['initUserAttributes']);
        return $oauthClient;
    }

    // Tests :

    public function testSignRequest()
    {
        $oauthClient = $this->createClient();

        $request = $oauthClient->createRequest();

        $oauthSignatureMethod = new PlainText();
        $oauthClient->setSignatureMethod($oauthSignatureMethod);

        $oauthClient->signRequest($request);

        $signedParams = $request->getData();

        $this->assertNotEmpty($signedParams['oauth_signature'], 'Unable to sign request!');
    }

    /**
     * @depends testSignRequest
     */
    public function testAuthorizationHeaderMethods()
    {
        $oauthClient = $this->createClient();

        $request = $oauthClient->createRequest();
        $request->setMethod('POST');
        $oauthClient->signRequest($request);
        $this->assertNotEmpty($request->getHeaders()->get('Authorization'));

        $request = $oauthClient->createRequest();
        $request->setMethod('GET');
        $oauthClient->signRequest($request);
        $this->assertEmpty($request->getHeaders()->get('Authorization'));

        $oauthClient->authorizationHeaderMethods = ['GET'];
        $request = $oauthClient->createRequest();
        $request->setMethod('GET');
        $oauthClient->signRequest($request);
        $this->assertNotEmpty($request->getHeaders()->get('Authorization'));

        $oauthClient->authorizationHeaderMethods = null;
        $request = $oauthClient->createRequest();
        $request->setMethod('GET');
        $oauthClient->signRequest($request);
        $this->assertNotEmpty($request->getHeaders()->get('Authorization'));

        $oauthClient->authorizationHeaderMethods = [];
        $request = $oauthClient->createRequest();
        $request->setMethod('POST');
        $oauthClient->signRequest($request);
        $this->assertEmpty($request->getHeaders()->get('Authorization'));
    }

    /**
     * Data provider for [[testComposeAuthorizationHeader()]].
     * @return array test data.
     */
    public function composeAuthorizationHeaderDataProvider()
    {
        return [
            [
                '',
                [
                    'oauth_test_name_1' => 'oauth_test_value_1',
                    'oauth_test_name_2' => 'oauth_test_value_2',
                ],
                ['Authorization' => 'OAuth oauth_test_name_1="oauth_test_value_1", oauth_test_name_2="oauth_test_value_2"']
            ],
            [
                'test_realm',
                [
                    'oauth_test_name_1' => 'oauth_test_value_1',
                    'oauth_test_name_2' => 'oauth_test_value_2',
                ],
                ['Authorization' => 'OAuth realm="test_realm", oauth_test_name_1="oauth_test_value_1", oauth_test_name_2="oauth_test_value_2"']
            ],
            [
                '',
                [
                    'oauth_test_name_1' => 'oauth_test_value_1',
                    'test_name_2' => 'test_value_2',
                ],
                ['Authorization' => 'OAuth oauth_test_name_1="oauth_test_value_1"']
            ],
        ];
    }

    /**
     * @dataProvider composeAuthorizationHeaderDataProvider
     *
     * @param string $realm                       authorization realm.
     * @param array  $params                      request params.
     * @param string $expectedAuthorizationHeader expected authorization header.
     */
    public function testComposeAuthorizationHeader($realm, array $params, $expectedAuthorizationHeader)
    {
        $oauthClient = $this->createClient();
        $authorizationHeader = $this->invoke($oauthClient, 'composeAuthorizationHeader', [$params, $realm]);
        $this->assertEquals($expectedAuthorizationHeader, $authorizationHeader);
    }

    public function testBuildAuthUrl()
    {
        $oauthClient = $this->createClient();
        $authUrl = 'http://test.auth.url';
        $oauthClient->authUrl = $authUrl;

        $requestTokenToken = 'test_request_token';
        $requestToken = new OAuthToken();
        $requestToken->setToken($requestTokenToken);

        $builtAuthUrl = $oauthClient->buildAuthUrl($requestToken);

        $this->assertContains($authUrl, $builtAuthUrl, 'No auth URL present!');
        $this->assertContains($requestTokenToken, $builtAuthUrl, 'No token present!');
    }
}
