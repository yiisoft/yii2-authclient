<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\clients\Google;
use yii\authclient\OAuthToken;
use yii\authclient\signature\RsaSha;
use yiiunit\extensions\authclient\TestCase;
use yiiunit\extensions\authclient\traits\OAuthDefaultReturnUrlTestTrait;

/**
 * @group google
 */
class GoogleTest extends TestCase
{
    use OAuthDefaultReturnUrlTestTrait;

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

    public function testAuthenticateUserJwt()
    {
        $params = $this->getParam('google');
        if (empty($params['serviceAccount'])) {
            $this->markTestSkipped("Google service account name is not configured.");
        }

        $oauthClient = new Google();
        $token = $oauthClient->authenticateUserJwt($params['serviceAccount'], [
            'class' => RsaSha::className(),
            'algorithm' => OPENSSL_ALGO_SHA256,
            'privateCertificate' => $params['serviceAccountPrivateKey']
        ]);
        $this->assertTrue($token instanceof OAuthToken);
        $this->assertNotEmpty($token->getToken());
    }

    protected function createClient()
    {
        return new Google();
    }

    /**
     * Data provider for [[testDefaultReturnUrl]].
     * @return array test data.
     */
    public function defaultReturnUrlDataProvider()
    {
        return [
            'default'                => [['authclient' => 'google'], null, '/?authclient=google'],
            'remove extra parameter' => [['authclient' => 'google', 'extra' => 'userid'], null, '/?authclient=google'],
            'keep extra parameter'   => [['authclient' => 'google', 'extra' => 'userid'], ['authclient', 'extra'], '/?authclient=google&extra=userid'],
        ];
    }
}
