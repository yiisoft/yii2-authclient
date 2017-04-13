<?php

namespace yiiunit\extensions\authclient;

use yii\authclient\BaseClient;
use yii\authclient\SessionStateStorage;

class BaseClientTest extends TestCase
{
    protected function setUp()
    {
        $this->mockApplication();
    }

    /**
     * Creates test OAuth client instance.
     * @return BaseClient oauth client.
     */
    protected function createClient()
    {
        $oauthClient = $this->getMockBuilder(BaseClient::className())
            ->setMethods(['initUserAttributes'])
            ->getMock();
        return $oauthClient;
    }

    // Tests :

    public function testSetGet()
    {
        $client = $this->createClient();

        $id = 'test_id';
        $client->setId($id);
        $this->assertEquals($id, $client->getId(), 'Unable to setup id!');

        $name = 'test_name';
        $client->setName($name);
        $this->assertEquals($name, $client->getName(), 'Unable to setup name!');

        $title = 'test_title';
        $client->setTitle($title);
        $this->assertEquals($title, $client->getTitle(), 'Unable to setup title!');

        $userAttributes = [
            'attribute1' => 'value1',
            'attribute2' => 'value2',
        ];
        $client->setUserAttributes($userAttributes);
        $this->assertEquals($userAttributes, $client->getUserAttributes(), 'Unable to setup user attributes!');

        $normalizeUserAttributeMap = [
            'name' => 'some/name',
            'email' => 'some/email',
        ];
        $client->setNormalizeUserAttributeMap($normalizeUserAttributeMap);
        $this->assertEquals($normalizeUserAttributeMap, $client->getNormalizeUserAttributeMap(), 'Unable to setup normalize user attribute map!');

        $viewOptions = [
            'option1' => 'value1',
            'option2' => 'value2',
        ];
        $client->setViewOptions($viewOptions);
        $this->assertEquals($viewOptions, $client->getViewOptions(), 'Unable to setup view options!');

        $requestOptions = [
            'option1' => 'value1',
            'option2' => 'value2',
        ];
        $client->setRequestOptions($requestOptions);
        $this->assertEquals($requestOptions, $client->getRequestOptions(), 'Unable to setup request options!');
    }

    public function testGetDefaults()
    {
        $client = $this->createClient();

        $this->assertNotEmpty($client->getName(), 'Unable to get default name!');
        $this->assertNotEmpty($client->getTitle(), 'Unable to get default title!');
        $this->assertNotNull($client->getViewOptions(), 'Unable to get default view options!');
        $this->assertNotNull($client->getNormalizeUserAttributeMap(), 'Unable to get default normalize user attribute map!');
    }

    /**
     * Data provider for [[testNormalizeUserAttributes()]]
     * @return array test data
     */
    public function dataProviderNormalizeUserAttributes()
    {
        return [
            [
                [
                    'name' => 'raw/name',
                    'email' => 'raw/email',
                ],
                [
                    'raw/name' => 'name value',
                    'raw/email' => 'email value',
                ],
                [
                    'name' => 'name value',
                    'email' => 'email value',
                ],
            ],
            [
                [
                    'name' => function ($attributes) {
                            return $attributes['firstName'] . ' ' . $attributes['lastName'];
                        },
                ],
                [
                    'firstName' => 'John',
                    'lastName' => 'Smith',
                ],
                [
                    'name' => 'John Smith',
                ],
            ],
            [
                [
                    'email' => ['emails', 'prime'],
                ],
                [
                    'emails' => [
                        'prime' => 'some@email.com'
                    ],
                ],
                [
                    'email' => 'some@email.com',
                ],
            ],
            [
                [
                    'email' => ['emails', 0],
                    'secondaryEmail' => ['emails', 1],
                ],
                [
                    'emails' => [
                        'some@email.com',
                    ],
                ],
                [
                    'email' => 'some@email.com',
                ],
            ],
            [
                [
                    'name' => 'file_get_contents',
                ],
                [
                    'file_get_contents' => 'value',
                ],
                [
                    'name' => 'value',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderNormalizeUserAttributes
     *
     * @depends testSetGet
     *
     * @param array $normalizeUserAttributeMap
     * @param array $rawUserAttributes
     * @param array $expectedNormalizedUserAttributes
     */
    public function testNormalizeUserAttributes($normalizeUserAttributeMap, $rawUserAttributes, $expectedNormalizedUserAttributes)
    {
        $client = $this->createClient();
        $client->setNormalizeUserAttributeMap($normalizeUserAttributeMap);

        $client->setUserAttributes($rawUserAttributes);
        $normalizedUserAttributes = $client->getUserAttributes();

        $this->assertEquals(array_merge($rawUserAttributes, $expectedNormalizedUserAttributes), $normalizedUserAttributes);
    }

    public function testSetupHttpClient()
    {
        $client = $this->createClient();

        $client->setHttpClient([
            'baseUrl' => 'http://domain.com'
        ]);
        $httpClient = $client->getHttpClient();

        $this->assertTrue($httpClient instanceof \yii\httpclient\Client, 'Unable to setup http client.');
        $this->assertEquals('http://domain.com', $httpClient->baseUrl, 'Unable to setup http client property.');

        $client = $this->createClient();
        $httpClient = $client->getHttpClient();
        $this->assertTrue($httpClient instanceof \yii\httpclient\Client, 'Unable to get default http client.');
    }

    /**
     * @depends testSetGet
     * @depends testSetupHttpClient
     */
    public function testCreateRequest()
    {
        $client = $this->createClient();

        $request = $client->createRequest();
        $this->assertTrue($request instanceof \yii\httpclient\Request);

        $options = [
            'userAgent' => 'Test User Agent'
        ];
        $client->setRequestOptions($options);
        $request = $client->createRequest();
        $expectedOptions = array_merge($options, $this->invoke($client, 'defaultRequestOptions'));
        $this->assertEquals($expectedOptions, $request->getOptions());
    }

    public function testSetupStateStorage()
    {
        $client = $this->createClient();

        $stateStorage = new SessionStateStorage();
        $client->setStateStorage($stateStorage);

        $this->assertSame($stateStorage, $client->getStateStorage(), 'Unable to setup state storage.');

        $client = $this->createClient();
        $stateStorage = $client->getStateStorage();
        $this->assertTrue($stateStorage instanceof SessionStateStorage, 'Unable to get default http client.');
    }
}