<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\extensions\authclient;

use yii\authclient\OAuth2;
use yii\authclient\OAuthToken;
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

    /**
     * @dataProvider refreshAccessTokenDataProvider
     */
    public function testRefreshAccessToken(array $response, $expectedRefreshToken): void
    {
        $oauthClient = $this->getMockBuilder(OAuth2::class)
            ->onlyMethods(['initUserAttributes', 'sendRequest'])
            ->getMock();
        $oauthClient->tokenUrl = 'https://example.com/token';
        $requestCount = 0;
        $oauthClient->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturnCallback(function ($request) use ($response, $expectedRefreshToken, &$requestCount) {
                $params = $request->getData();
                $this->assertSame('refresh_token', $params['grant_type']);
                $this->assertSame($requestCount++ === 0 ? 'original-refresh-token' : $expectedRefreshToken, $params['refresh_token']);
                return $response;
            });

        $token = new OAuthToken([
            'tokenParamKey' => 'access_token',
            'createTimestamp' => time() - 7200,
            'params' => [
                'access_token' => 'expired-access-token',
                'refresh_token' => 'original-refresh-token',
                'expires_in' => 3600,
            ],
        ]);

        for ($i = 0; $i < 2; ++$i) {
            $token = $oauthClient->refreshAccessToken($token);
            $this->assertSame($expectedRefreshToken, $token->getRefreshToken());
            $this->assertSame('new-access-token', $token->getToken());
            $this->assertSame(1800, $token->getExpireDuration());
            $this->assertFalse($token->getIsExpired());
            $this->assertSame($token, $oauthClient->getAccessToken());
        }
    }

    public function refreshAccessTokenDataProvider(): array
    {
        return [
            'refresh token omitted' => [
                ['access_token' => 'new-access-token', 'expires_in' => 1800],
                'original-refresh-token',
            ],
            'refresh token rotated' => [
                ['access_token' => 'new-access-token', 'expires_in' => 1800, 'refresh_token' => 'rotated-refresh-token'],
                'rotated-refresh-token',
            ],
        ];
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
