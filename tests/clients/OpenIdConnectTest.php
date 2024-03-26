<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\OAuth2;
use yii\authclient\OpenIdConnect;
use yiiunit\extensions\authclient\clients\base\BaseOauth2ClientTestCase;

class OpenIdConnectTest extends BaseOauth2ClientTestCase
{
    protected function createClient()
    {
        return new OpenIdConnect();
    }

    protected function getExpectedTokenLocation()
    {
        return OAuth2::ACCESS_TOKEN_LOCATION_HEADER;
    }
}
