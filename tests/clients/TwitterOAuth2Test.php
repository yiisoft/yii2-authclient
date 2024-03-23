<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\clients\TwitterOAuth2;
use yii\authclient\OAuth2;
use yiiunit\extensions\authclient\clients\base\BaseOauth2ClientTestCase;

class TwitterOAuth2Test extends BaseOauth2ClientTestCase
{
    protected function createClient()
    {
        return new TwitterOAuth2();
    }

    protected function getExpectedTokenLocation()
    {
        return OAuth2::ACCESS_TOKEN_LOCATION_HEADER;
    }
}
