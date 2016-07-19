<?php

namespace yiiunit\extensions\authclient;

use yii\authclient\SessionStateStorage;
use yiiunit\extensions\authclient\data\Session;

class SessionStateStorageTest extends TestCase
{
    public function testSetState()
    {
        $storage = new SessionStateStorage([
            'session' => Session::className()
        ]);

        $key = 'test-key';
        $value = 'test-value';

        $storage->set($key, $value);

        $this->assertEquals($value, $storage->get($key));

        $storage->remove($key);
        $this->assertNull($storage->get($key));
    }
}