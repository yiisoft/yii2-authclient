<?php

namespace yiiunit\extensions\authclient;

use yii\authclient\OAuth2;
use yii\base\InvalidConfigException;

class OAuth2Test extends TestCase
{
    protected function setUp(): void
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
        $oauthClient = $this->getMockBuilder(OAuth2::class)
            ->onlyMethods(['initUserAttributes'])
            ->getMock();
        return $oauthClient;
    }

    // Tests :

    public function testBuildAuthUrl(): void
    {
        $oauthClient = $this->createClient();
        $authUrl = 'http://test.auth.url';
        $oauthClient->authUrl = $authUrl;
        $clientId = 'test_client_id';
        $oauthClient->clientId = $clientId;
        $returnUrl = 'http://test.return.url';
        $oauthClient->setReturnUrl($returnUrl);

        $builtAuthUrl = $oauthClient->buildAuthUrl();

        $this->assertStringContainsString($authUrl, $builtAuthUrl, 'No auth URL present!');
        $this->assertStringContainsString($clientId, $builtAuthUrl, 'No client id present!');
        $this->assertStringContainsString(rawurlencode($returnUrl), $builtAuthUrl, 'No return URL present!');
    }

    public function testGetOriginDerivedFromReturnUrl(): void
    {
        $oauthClient = $this->createClient();
        $oauthClient->returnUrl = 'https://example.com/admin/site/auth?authclient=test';

        $this->assertEquals('https://example.com', $oauthClient->getOrigin());
    }

    public function testGetOriginKeepsExplicitPort(): void
    {
        $oauthClient = $this->createClient();
        $oauthClient->returnUrl = 'https://example.com:8443/site/auth';

        $this->assertEquals('https://example.com:8443', $oauthClient->getOrigin());
    }

    public function testGetOriginThrowsOnRelativeReturnUrl(): void
    {
        $oauthClient = $this->createClient();
        $oauthClient->returnUrl = '/site/auth';

        $this->expectException(InvalidConfigException::class);
        $oauthClient->getOrigin();
    }

    public function testSetOrigin(): void
    {
        $oauthClient = $this->createClient();
        $oauthClient->returnUrl = 'https://example.com/site/auth';
        $oauthClient->origin = 'https://origin.example.com';

        $this->assertEquals('https://origin.example.com', $oauthClient->getOrigin());
    }

    public function testPkceCodeChallengeIsPresentInAuthUrl(): void
    {
        $oauthClient = $this->createClient();
        $oauthClient->enablePkce = true;

        $oauthClient->authUrl = 'http://test.auth.url';
        $oauthClient->clientId = 'test_client_id';
        $oauthClient->returnUrl = 'http://test.return.url';

        $builtAuthUrl = $oauthClient->buildAuthUrl();

        $this->assertStringContainsString('code_challenge=', $builtAuthUrl, 'No code challenge Present!');
        $this->assertStringContainsString('code_challenge_method=S256', $builtAuthUrl, 'No code challenge method Present!');
    }
}
