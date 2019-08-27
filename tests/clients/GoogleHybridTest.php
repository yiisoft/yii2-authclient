<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\clients\GoogleHybrid;
use yiiunit\extensions\authclient\TestCase;
use yiiunit\extensions\authclient\traits\OAuthDefaultReturnUrlTestTrait;

class GoogleHybridTest extends TestCase
{
    use OAuthDefaultReturnUrlTestTrait;

    protected function createClient()
    {
        return new GoogleHybrid();
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
