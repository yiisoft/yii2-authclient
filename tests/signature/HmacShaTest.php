<?php

namespace yiiunit\extensions\authclient\signature;

use yii\authclient\signature\HmacSha;
use yiiunit\extensions\authclient\TestCase;

class HmacShaTest extends TestCase
{
    public function testGenerateSignature()
    {
        $signatureMethod = new HmacSha(['algorithm' => 'sha256']);

        $baseString = 'test_base_string';
        $key = 'test_key';

        $signature = $signatureMethod->generateSignature($baseString, $key);
        $this->assertNotEmpty($signature, 'Unable to generate signature!');
    }
}