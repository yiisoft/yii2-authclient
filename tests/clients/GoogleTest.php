<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\clients\Google;
use yii\authclient\OAuth2;
use yii\authclient\OAuthToken;
use yii\authclient\signature\RsaSha;
use yiiunit\extensions\authclient\clients\base\BaseOauth2ClientTestCase;
use yiiunit\extensions\authclient\traits\OAuthDefaultReturnUrlTestTrait;

/**
 * @group google
 */
class GoogleTest extends BaseOauth2ClientTestCase
{
    use OAuthDefaultReturnUrlTestTrait;

    protected function createClient()
    {
        return new Google();
    }

    protected function getExpectedTokenLocation()
    {
        return OAuth2::ACCESS_TOKEN_LOCATION_BODY;
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
