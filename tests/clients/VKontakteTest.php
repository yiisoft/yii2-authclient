<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\clients\VKontakte;
use yii\authclient\OAuth2;
use yiiunit\extensions\authclient\clients\base\BaseOauth2ClientTestCase;

class VKontakteTest extends BaseOauth2ClientTestCase
{
    protected function createClient()
    {
        return new VKontakte();
    }

    protected function getExpectedTokenLocation()
    {
        return OAuth2::ACCESS_TOKEN_LOCATION_BODY;
    }
}
