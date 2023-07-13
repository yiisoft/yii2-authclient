<?php

namespace yiiunit\extensions\authclient;

use yii\authclient\OAuthToken;
use yii\authclient\OpenIdConnect;
use yii\caching\ArrayCache;

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
            'cache' => null,
        ]);
        $configParams = $authClient->getConfigParams();
        $this->assertNotEmpty($configParams);
        $this->assertTrue(isset($configParams['authorization_endpoint']));
        $this->assertTrue(isset($configParams['token_endpoint']));

        $this->assertEquals($configParams['token_endpoint'], $authClient->getConfigParam('token_endpoint'));

        // Test default value for non existing
        $this->assertEquals('default', $authClient->getConfigParam('non-existing', 'default'));
    }

    /**
     * @depends testDiscoverConfig
     */
    public function testDiscoverConfigCache()
    {
        $cache = new ArrayCache();

        $authClient = new OpenIdConnect([
            'issuerUrl' => 'https://accounts.google.com',
            'id' => 'google',
            'cache' => $cache,
        ]);
        $cachedConfigParams = $authClient->getConfigParams();

        $authClient = new OpenIdConnect([
            'issuerUrl' => 'https://invalid-url.com',
            'id' => 'google',
            'cache' => $cache,
        ]);
        $this->assertEquals($cachedConfigParams, $authClient->getConfigParams());

        $authClient = new OpenIdConnect([
            'issuerUrl' => 'https://yiiframework.com', // Should be a domain that returns an error for the /.well-known/openid-configuration endpoint
            'id' => 'foo',
            'cache' => $cache,
        ]);
        $this->expectException('yii\authclient\InvalidResponseException');
        $authClient->getConfigParams();
    }

    /**
     * @depends testDiscoverConfig
     */
    public function testBuildAuthUrl()
    {
        $authClient = new OpenIdConnect([
            'issuerUrl' => 'https://accounts.google.com',
            'cache' => null,
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

    public function testNonce()
    {
        $authClient = new OpenIdConnect([
            'issuerUrl' => 'https://accounts.google.com',
            'cache' => null,
        ]);
        $clientId = 'test_client_id';
        $authClient->clientId = $clientId;
        $returnUrl = 'http://test.return.url';
        $nonce = 'test_nonce';
        $code = 'test_code';
        $authClient->setReturnUrl($returnUrl);

        $builtAuthUrl = $authClient->buildAuthUrl([
            'nonce' => $nonce,
        ]);


        $query = parse_url($builtAuthUrl)['query'];
        parse_str($query, $query_vars);
        $this->assertEquals($query_vars['nonce'], $nonce);
    }

    public function testUserInfoFromToken()
    {
        $accessToken = new OAuthToken([
            'params' => [
                'id_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJ0ZXN0LWNsaWVudC10eXBlLWF1dGgtY29kZS1vcGVuLWlkLWNvbm5lY3QiLCJpc3MiOiJodHRwczovL2xvY2FsaG9zdCIsImlhdCI6MTYzNTY5NDUzNi40MjkyMzcsImV4cCI6NDc5MTM2ODEzNiwic3ViIjoiMTIzIiwiYXV0aF90aW1lIjoxNjM1NjkwOTM1LCJub25jZSI6InNWbEVmS2xNMTdhYlJfY2Q5cXhvcU5fZHN3a0VRWXUxIn0.LgV-jFoopYnEhgygtt4bDL4HV1Rnw_cuKgopQ_I8f2YxDUlXKO2M0ANjA1iWIsBTCAKnI5JF7wYWBlK7eFJkU16U8yNVYHyUNaMGzXG1Q3khLmPfa9tmU2Kj2loA2hGGkZTjHCAuDgYSSFucLlFnqcR4-vhhwUyZdvFvwRRi0FF1r10m2oNmzfVLAcQxo2C5C_inSmuGnzfWqvrsDjdnT8N2XE2e3hVRxlIEv4GkupUejjdyWlSBjsUjnfXMlmi6VBn7HfElcVjJqp3L7GHVV1zfSA82e3oo7_wvQbb090M4nwFOmasvGnvZddELQdxL9KW0s_AIdkUM5lFxFFnl8Q',
            ]
        ]);

        $authClient = new OpenIdConnect([
            'cache' => null,
            'configParams' => [],
            'accessToken' => $accessToken,
            'validateJws' => false,
        ]);

        $userAttributes = $authClient->getUserAttributes();

        $this->assertEquals(['sub' => '123'], $userAttributes);
    }

    public function testUserInfoFromUserInfoTokenResponse()
    {
        /** @var OpenIdConnect $oidcClient */
        $oidcClient = $this->getMockBuilder(OpenIdConnect::className())
            ->setMethods(['api'])
            ->getMock();

        $oidcClient->expects($this->once())
            ->method('api')
            ->willReturn(
                'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJ0ZXN0LWNsaWVudC10eXBlLWF1dGgtY29kZS1vcGVuLWlkLWNvbm5lY3QiLCJpc3MiOiJodHRwczovL2xvY2FsaG9zdCIsImlhdCI6MTYzNTY5NDUzNi40MjkyMzcsImV4cCI6NDc5MTM2ODEzNiwic3ViIjoiMTIzIiwiYXV0aF90aW1lIjoxNjM1NjkwOTM1LCJub25jZSI6InNWbEVmS2xNMTdhYlJfY2Q5cXhvcU5fZHN3a0VRWXUxIn0.LgV-jFoopYnEhgygtt4bDL4HV1Rnw_cuKgopQ_I8f2YxDUlXKO2M0ANjA1iWIsBTCAKnI5JF7wYWBlK7eFJkU16U8yNVYHyUNaMGzXG1Q3khLmPfa9tmU2Kj2loA2hGGkZTjHCAuDgYSSFucLlFnqcR4-vhhwUyZdvFvwRRi0FF1r10m2oNmzfVLAcQxo2C5C_inSmuGnzfWqvrsDjdnT8N2XE2e3hVRxlIEv4GkupUejjdyWlSBjsUjnfXMlmi6VBn7HfElcVjJqp3L7GHVV1zfSA82e3oo7_wvQbb090M4nwFOmasvGnvZddELQdxL9KW0s_AIdkUM5lFxFFnl8Q'
            );

        $oidcClient->cache = null;
        $oidcClient->configParams = ['userinfo_endpoint' => 'http://localhost'];
        $oidcClient->validateJws = false;

        $userAttributes = $oidcClient->getUserAttributes();

        $this->assertEquals(['sub' => '123'], $userAttributes);
    }
}
