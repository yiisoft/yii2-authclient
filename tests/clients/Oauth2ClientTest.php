<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\clients\Oauth2Client;
use yii\authclient\OAuth2;
use yiiunit\extensions\authclient\clients\base\BaseOauth2ClientTestCase;

class Oauth2ClientTest extends BaseOauth2ClientTestCase
{
    protected function createClient()
    {
        return new Oauth2Client();
    }

    protected function getExpectedTokenLocation()
    {
        return OAuth2::ACCESS_TOKEN_LOCATION_HEADER;
    }
}
