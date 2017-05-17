<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\clients\Google;
use yii\authclient\OAuthToken;
use yii\authclient\signature\RsaSha;
use yiiunit\extensions\authclient\TestCase;

/**
 * @group google
 */
class GoogleTest extends TestCase
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

    public function testJwt()
    {
        $params = $this->getParam('google');
        if (empty($params['serviceAccount'])) {
            $this->markTestSkipped("Google service account name is not configured.");
        }

        $oauthClient = new Google([
            'clientId' => $params['serviceAccount'],
            'signatureMethod' => [
                'class' => RsaSha::className(),
                'algorithm' => OPENSSL_ALGO_SHA256,
                'privateCertificate' => $params['serviceAccountPrivateKey']
            ]
        ]);
        $token = $oauthClient->authenticateJwt();
        $this->assertTrue($token instanceof OAuthToken);
        $this->assertNotEmpty($token->getToken());
    }
}