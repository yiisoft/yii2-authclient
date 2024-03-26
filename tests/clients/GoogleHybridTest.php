<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\clients\GoogleHybrid;
use yii\authclient\OAuth2;
use yiiunit\extensions\authclient\clients\base\BaseOauth2ClientTestCase;
use yiiunit\extensions\authclient\traits\OAuthDefaultReturnUrlTestTrait;

class GoogleHybridTest extends BaseOauth2ClientTestCase
{
    use OAuthDefaultReturnUrlTestTrait;

    protected function createClient()
    {
        return new GoogleHybrid();
    }

    protected function getExpectedTokenLocation()
    {
        return OAuth2::ACCESS_TOKEN_LOCATION_BODY;
    }

    /**
     * Data provider for [[testDefaultReturnUrl]].
     * @return array test data.
     */
    public function defaultReturnUrlDataProvider()
    {
        return [
            'default'                => [['authclient' => 'google-hybrid'], null, 'postmessage'],
            'remove extra parameter' => [['authclient' => 'google-hybrid', 'extra' => 'userid'], null, 'postmessage'],
            'keep extra parameter'   => [['authclient' => 'google-hybrid', 'extra' => 'userid'], ['authclient', 'extra'], 'postmessage'],
        ];
    }
}
