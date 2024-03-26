<?php

namespace yiiunit\extensions\authclient\clients\base;

use yii\authclient\OAuth2;
use yii\authclient\OAuthToken;
use yii\base\InvalidConfigException;
use yiiunit\extensions\authclient\TestCase;

abstract class BaseOauth2ClientTestCase extends TestCase
{
    /**
     * @return OAuth2
     */
    abstract protected function createClient();

    /**
     * @return string
     */
    abstract protected function getExpectedTokenLocation();

    /**
     * @return string
     * @see https://datatracker.ietf.org/doc/html/rfc6750#section-6.1.1
     */
    protected function getAccessTokenHeaderTypeName()
    {
        return 'Bearer';
    }

    protected function getAccessTokenBodyParamName()
    {
        return 'access_token';
    }

    protected function setUp()
    {
        $config = [
            'components' => [
                'request' => [
                    'hostInfo' => 'http://testdomain.com',
                    'scriptUrl' => '/index.php',
                ],
            ],
        ];
        $this->mockApplication($config, '\yii\web\Application');
    }

    public function testTokenLocation()
    {
        $tokenLocation = $this->getExpectedTokenLocation();
        $client = $this->createClient();
        $testToken = 'test-token';
        $client->setAccessToken(new OAuthToken(['token' => $testToken]));
        $request = $client->createApiRequest();
        $request->beforeSend(); // injects the access token

        if ($tokenLocation == OAuth2::ACCESS_TOKEN_LOCATION_HEADER) {
            $authorizationHeader = $request->getHeaders()->get('authorization');
            $this->assertEquals($this->getAccessTokenHeaderTypeName() . ' ' . $testToken, $authorizationHeader);
        } elseif ($tokenLocation == OAuth2::ACCESS_TOKEN_LOCATION_BODY) {
            $accessTokenBodyParamName = $this->getAccessTokenBodyParamName();
            $data = $request->getData();
            $this->assertArrayHasKey($accessTokenBodyParamName, $data);
            $this->assertEquals($testToken, $data[$accessTokenBodyParamName]);
        } else {
            throw new InvalidConfigException('Unknown token location "' . $tokenLocation . '".');
        }
    }
}
