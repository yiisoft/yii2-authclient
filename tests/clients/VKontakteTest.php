<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\clients\VKontakte;
use yii\authclient\OAuth2;
use yii\authclient\OAuthToken;
use yiiunit\extensions\authclient\clients\base\BaseOauth2ClientTestCase;

class VKontakteTest extends BaseOauth2ClientTestCase
{
    protected function createClient()
    {
        return new VKontakte();
    }

    protected function getExpectedTokenLocation()
    {
        return OAuth2::ACCESS_TOKEN_LOCATION_BODY;
    }

    /**
     * @param array<string, string|null> $data
     * @dataProvider userIdsDataProvider
     */
    public function testApplyAccessTokenPreservesUserIds(array $data, ?string $tokenUserId, ?string $expectedUserIds): void
    {
        $client = new VKontakte();
        $client->setAccessToken(new OAuthToken([
            'params' => ['user_id' => $tokenUserId],
            'token' => 'test-token',
        ]));
        $request = $client->createApiRequest()->setData($data);

        $request->beforeSend();

        $requestData = $request->getData();
        $this->assertSame($expectedUserIds, $requestData['user_ids']);
        $this->assertSame('first_name', $requestData['fields']);
        $this->assertSame($client->apiVersion, $requestData['v']);
        $this->assertSame('test-token', $requestData['access_token']);
    }

    /**
     * @return array<string, array{array<string, string|null>, string|null, string|null}>
     */
    public function userIdsDataProvider(): array
    {
        return [
            'explicit IDs with token user' => [['fields' => 'first_name', 'user_ids' => '123,456'], '789', '123,456'],
            'explicit IDs without token user' => [['fields' => 'first_name', 'user_ids' => '123'], null, '123'],
            'explicit zero' => [['fields' => 'first_name', 'user_ids' => '0'], '789', '0'],
            'default token user' => [['fields' => 'first_name'], '789', '789'],
            'no user IDs' => [['fields' => 'first_name'], null, null],
            'null uses token user' => [['fields' => 'first_name', 'user_ids' => null], '789', '789'],
        ];
    }
}
