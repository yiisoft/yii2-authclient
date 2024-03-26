<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\clients\Live;
use yii\authclient\OAuth2;
use yiiunit\extensions\authclient\clients\base\BaseOauth2ClientTestCase;

class LiveTest extends BaseOauth2ClientTestCase
{
    protected function createClient()
    {
        return new Live();
    }

    protected function getExpectedTokenLocation()
    {
        return OAuth2::ACCESS_TOKEN_LOCATION_BODY;
    }
}
