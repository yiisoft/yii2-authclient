<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\extensions\authclient;

use yii\authclient\OAuthToken;
use yii\authclient\OpenIdConnect;
use yii\base\InvalidConfigException;

class OpenIdConnectLogoutTest extends TestCase
{
    protected function setUp(): void
    {
        $this->mockWebApplication();
    }

    public function testBuildLogoutUrlFromDiscovery(): void
    {
        $client = $this->getMockBuilder(OpenIdConnect::class)
            ->onlyMethods(['discoverConfig'])
            ->getMock();
        $client->cache = null;
        $client->clientId = 'test-client';
        $client->clientSecret = 'secret';
        $client->returnUrl = 'https://app.example/login-callback';
        $client->expects($this->once())->method('discoverConfig')->willReturn([
            'end_session_endpoint' => 'https://provider.example/logout?tenant=example',
        ]);
        $token = new OAuthToken(['params' => [
            'id_token' => 'header.payload.signature',
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
        ]]);
        $client->setAccessToken($token);

        $url = $client->buildLogoutUrl([
            'post_logout_redirect_uri' => 'https://app.example/logged-out?lang=en&from=logout',
            'state' => 'a+b/c=?',
            'ui_locales' => 'en fr',
            'logout_hint' => 'user@example.com',
        ]);

        $this->assertSame(
            'https://provider.example/logout?tenant=example&client_id=test-client'
            . '&id_token_hint=header.payload.signature'
            . '&post_logout_redirect_uri=https%3A%2F%2Fapp.example%2Flogged-out%3Flang%3Den%26from%3Dlogout'
            . '&state=a%2Bb%2Fc%3D%3F&ui_locales=en%20fr&logout_hint=user%40example.com',
            $url
        );
        $this->assertSame($token, $client->getAccessToken());
        $this->assertTrue($client->autoRefreshAccessToken);
    }

    public function testExplicitEndpointAndParametersBypassDiscoveryAndTokenStorage(): void
    {
        $client = $this->getMockBuilder(OpenIdConnect::class)
            ->onlyMethods(['discoverConfig', 'getAccessTokenWithoutRefresh'])
            ->getMock();
        $client->logoutUrl = 'https://provider.example/logout';
        $client->clientId = 'default-client';
        $client->expects($this->never())->method('discoverConfig');
        $client->expects($this->never())->method('getAccessTokenWithoutRefresh');

        $this->assertSame(
            'https://provider.example/logout?client_id=other-client&id_token_hint=explicit-token',
            $client->buildLogoutUrl(['client_id' => 'other-client', 'id_token_hint' => 'explicit-token'])
        );
        $this->assertSame(
            'https://provider.example/logout',
            $client->buildLogoutUrl(['client_id' => null, 'id_token_hint' => null])
        );
    }

    public function testBuildLogoutUrlWithoutIdToken(): void
    {
        $client = new OpenIdConnect([
            'clientId' => 'test-client',
            'configParams' => ['end_session_endpoint' => 'https://provider.example/logout'],
        ]);
        $client->setAccessToken(null);
        $this->assertSame('https://provider.example/logout?client_id=test-client', $client->buildLogoutUrl());

        $client->setAccessToken(new OAuthToken(['params' => ['access_token' => 'access-token']]));
        $this->assertSame('https://provider.example/logout?client_id=test-client', $client->buildLogoutUrl());
    }

    public function testLogoutDoesNotRefreshOrCacheExpiredStoredToken(): void
    {
        $client = new OpenIdConnect(['id' => 'logout-test']);
        $token = new OAuthToken([
            'createTimestamp' => time() - 3600,
            'params' => [
                'expires_in' => 1,
                'id_token' => 'old-id-token',
                'refresh_token' => 'refresh-token',
            ],
        ]);
        $this->assertTrue($token->getIsExpired());
        $client->setAccessToken($token);

        // A new instance restores the token from session, where automatic refresh normally happens.
        $restoredClient = $this->getMockBuilder(OpenIdConnect::class)
            ->onlyMethods(['refreshAccessToken', 'getStateKeyPrefix'])
            ->getMock();
        $restoredClient->id = 'logout-test';
        $restoredClient->clientId = 'test-client';
        $restoredClient->logoutUrl = 'https://provider.example/logout';
        $restoredClient->method('getStateKeyPrefix')->willReturn(OpenIdConnect::class . '_logout-test_');
        $freshToken = new OAuthToken(['params' => ['id_token' => 'fresh-id-token']]);
        $refreshed = false;
        $restoredClient->expects($this->once())->method('refreshAccessToken')->with($token)
            ->willReturnCallback(static function () use ($freshToken, &$refreshed) {
                $refreshed = true;
                return $freshToken;
            });

        $this->assertSame(
            'https://provider.example/logout?client_id=test-client&id_token_hint=old-id-token',
            $restoredClient->buildLogoutUrl()
        );
        $this->assertTrue($restoredClient->autoRefreshAccessToken);
        $this->assertFalse($refreshed);
        $this->assertSame($freshToken, $restoredClient->getAccessToken());

        $restoredClient->autoRefreshAccessToken = false;
        $restoredClient->buildLogoutUrl();
        $this->assertFalse($restoredClient->autoRefreshAccessToken);
    }

    public function testLogoutBypassesRefreshingAccessors(): void
    {
        $client = $this->getMockBuilder(OpenIdConnect::class)
            ->onlyMethods(['getAccessToken', 'restoreAccessToken'])
            ->getMock();
        $client->logoutUrl = 'https://provider.example/logout';
        $client->clientId = 'test-client';
        $client->setAccessToken(new OAuthToken(['params' => ['id_token' => 'cached-id-token']]));
        $client->expects($this->never())->method('getAccessToken');
        $client->expects($this->never())->method('restoreAccessToken');

        $this->assertSame(
            'https://provider.example/logout?client_id=test-client&id_token_hint=cached-id-token',
            $client->buildLogoutUrl()
        );
    }

    public function testEmptyEndpointFallsBackToDiscovery(): void
    {
        $client = new OpenIdConnect([
            'logoutUrl' => '',
            'configParams' => ['end_session_endpoint' => 'https://provider.example/logout'],
        ]);
        $this->assertSame('https://provider.example/logout', $client->buildLogoutUrl(['id_token_hint' => null]));
    }

    public function testEmptyDefaultsAreOmitted(): void
    {
        $client = new OpenIdConnect([
            'logoutUrl' => 'https://provider.example/logout',
            'clientId' => '',
        ]);
        $client->setAccessToken(new OAuthToken(['params' => ['id_token' => '']]));
        $this->assertSame('https://provider.example/logout', $client->buildLogoutUrl());
        $this->assertSame(
            'https://provider.example/logout?client_id=&id_token_hint=',
            $client->buildLogoutUrl(['client_id' => '', 'id_token_hint' => ''])
        );
    }

    public function testUnexpectedStoredTokenIsIgnored(): void
    {
        $storage = $this->createMock(\yii\authclient\StateStorageInterface::class);
        $storage->method('get')->willReturnOnConsecutiveCalls(false, ['id_token' => 'unexpected']);
        $client = new OpenIdConnect([
            'logoutUrl' => 'https://provider.example/logout',
            'stateStorage' => $storage,
        ]);

        $this->assertSame('https://provider.example/logout', $client->buildLogoutUrl());
        $this->assertSame('https://provider.example/logout', $client->buildLogoutUrl());
    }

    public function testMissingEndpoint(): void
    {
        $client = new OpenIdConnect(['configParams' => []]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('end_session_endpoint');
        $client->buildLogoutUrl();
    }
}
