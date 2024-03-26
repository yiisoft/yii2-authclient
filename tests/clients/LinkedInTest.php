<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\clients\LinkedIn;
use yii\authclient\OAuth2;
use yiiunit\extensions\authclient\clients\base\BaseOauth2ClientTestCase;

class LinkedInTest extends BaseOauth2ClientTestCase
{
    protected function createClient()
    {
        return new LinkedIn();
    }

    protected function getExpectedTokenLocation()
    {
        return OAuth2::ACCESS_TOKEN_LOCATION_BODY;
    }

    protected function getAccessTokenBodyParamName()
    {
        return 'oauth2_access_token';
    }
}
