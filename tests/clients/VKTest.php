<?php

namespace yiiunit\extensions\authclient\clients;

use yiiunit\extensions\authclient\TestCase;

/**
 * @group VKontakte
 */
class VKTest extends TestCase
{
    public function testResponse()
    {
        $response = [
            'error' => [
                'error_code' => 5,
                'error_msg' => '',
                'request_params' => '',
            ],
            'response' => null,
        ];
        $attributes = [];
        if (!empty($response['response'])) {
            $attributes = array_shift($response['response']);
        }

        $this->assertEmpty($attributes);
    }
}
