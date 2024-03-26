<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\clients\Facebook;
use yii\authclient\OAuth2;
use yiiunit\extensions\authclient\clients\base\BaseOauth2ClientTestCase;

class FacebookTest extends BaseOauth2ClientTestCase
{
    protected function createClient()
    {
        return new Facebook();
    }

    protected function getExpectedTokenLocation()
    {
        return OAuth2::ACCESS_TOKEN_LOCATION_BODY;
    }
}
