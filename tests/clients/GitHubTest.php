<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\clients\GitHub;
use yii\authclient\OAuth2;
use yiiunit\extensions\authclient\clients\base\BaseOauth2ClientTestCase;

class GitHubTest extends BaseOauth2ClientTestCase
{
    protected function createClient()
    {
        return new GitHub();
    }

    protected function getExpectedTokenLocation()
    {
        return OAuth2::ACCESS_TOKEN_LOCATION_HEADER;
    }

    protected function getAccessTokenHeaderTypeName()
    {
        return 'token';
    }
}
