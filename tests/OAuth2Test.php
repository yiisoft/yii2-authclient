<?php

namespace yiiunit\extensions\authclient;

use yii\authclient\OAuth2;

class OAuth2Test extends TestCase
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
     * Creates test OAuth2 client instance.
     * @return OAuth2 oauth client.
     */
    protected function createClient()
    {
        $oauthClient = $this->getMockBuilder(OAuth2::className())
            ->setMethods(['initUserAttributes'])
            ->getMock();
        return $oauthClient;
    }

    // Tests :

    public function testBuildAuthUrl()
    {
        $oauthClient = $this->createClient();
        $authUrl = 'http://test.auth.url';
        $oauthClient->authUrl = $authUrl;
        $clientId = 'test_client_id';
        $oauthClient->clientId = $clientId;
        $returnUrl = 'http://test.return.url';
        $oauthClient->setReturnUrl($returnUrl);

        $builtAuthUrl = $oauthClient->buildAuthUrl();

        $this->assertContains($authUrl, $builtAuthUrl, 'No auth URL present!');
        $this->assertContains($clientId, $builtAuthUrl, 'No client id present!');
        $this->assertContains(rawurlencode($returnUrl), $builtAuthUrl, 'No return URL present!');
    }

    public function testPkceCodeChallengeIsPresentInAuthUrl()
    {
        $oauthClient = $this->createClient();
        $oauthClient->enablePkce = true;

        $oauthClient->authUrl = 'http://test.auth.url';
        $oauthClient->clientId = 'test_client_id';
        $oauthClient->returnUrl = 'http://test.return.url';

        $builtAuthUrl = $oauthClient->buildAuthUrl();

        $this->assertContains('code_challenge=', $builtAuthUrl, 'No code challenge Present!');
        $this->assertContains('code_challenge_method=S256', $builtAuthUrl, 'No code challenge method Present!');
    }
}
