<?php

namespace yiiunit\extensions\authclient;

use yii\authclient\OpenIdConnect;

class OpenIdConnectTest extends TestCase
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

    public function testDiscoverConfig()
    {
        $authClient = new OpenIdConnect([
            'issuerUrl' => 'https://accounts.google.com',
        ]);
        $configParams = $authClient->getConfigParams();
        $this->assertNotEmpty($configParams);
        $this->assertTrue(isset($configParams['authorization_endpoint']));
        $this->assertTrue(isset($configParams['token_endpoint']));

        $this->assertEquals($configParams['token_endpoint'], $authClient->getConfigParam('token_endpoint'));
    }

    /**
     * @depends testDiscoverConfig
     */
    public function testBuildAuthUrl()
    {
        $authClient = new OpenIdConnect([
            'issuerUrl' => 'https://accounts.google.com',
        ]);
        $clientId = 'test_client_id';
        $authClient->clientId = $clientId;
        $returnUrl = 'http://test.return.url';
        $authClient->setReturnUrl($returnUrl);

        $builtAuthUrl = $authClient->buildAuthUrl();

        $this->assertNotEmpty($authClient->authUrl);
        $this->assertContains($clientId, $builtAuthUrl, 'No client id present!');
        $this->assertContains(rawurlencode($returnUrl), $builtAuthUrl, 'No return URL present!');
    }
}