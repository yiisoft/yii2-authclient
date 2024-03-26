<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\clients\Yandex;
use yii\authclient\OAuth2;
use yiiunit\extensions\authclient\clients\base\BaseOauth2ClientTestCase;

class YandexTest extends BaseOauth2ClientTestCase
{
    protected function createClient()
    {
        return new Yandex();
    }

    protected function getExpectedTokenLocation()
    {
        return OAuth2::ACCESS_TOKEN_LOCATION_BODY;
    }

    protected function getAccessTokenBodyParamName()
    {
        return 'oauth_token';
    }
}
