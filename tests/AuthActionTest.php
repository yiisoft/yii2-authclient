<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\extensions\authclient;

use yii\authclient\AuthAction;
use yii\authclient\Collection;
use yii\authclient\clients\Google;
use yii\web\Application;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use Yii;

class AuthActionTest extends TestCase
{
    protected function setUp(): void
    {
        $config = [
            'components' => [
                'user' => [
                    'identityClass' => '\yii\web\IdentityInterface'
                ],
                'request' => [
                    'hostInfo' => 'http://testdomain.com',
                    'scriptUrl' => '/index.php',
                ],
            ]
        ];
        $this->mockApplication($config, '\yii\web\Application');
    }

    // Tests :

    public function testSetGet(): void
    {
        $action = new AuthAction(null, null);

        $successUrl = 'http://test.success.url';
        $action->setSuccessUrl($successUrl);
        $this->assertEquals($successUrl, $action->getSuccessUrl(), 'Unable to setup success URL!');

        $cancelUrl = 'http://test.cancel.url';
        $action->setCancelUrl($cancelUrl);
        $this->assertEquals($cancelUrl, $action->getCancelUrl(), 'Unable to setup cancel URL!');
    }

    /**
     * @depends testSetGet
     */
    public function testGetDefaultSuccessUrl(): void
    {
        $action = new AuthAction(null, null);

        $this->assertNotEmpty($action->getSuccessUrl(), 'Unable to get default success URL!');
    }

    /**
     * @depends testSetGet
     */
    public function testGetDefaultCancelUrl(): void
    {
        $action = new AuthAction(null, null);

        $this->assertNotEmpty($action->getSuccessUrl(), 'Unable to get default cancel URL!');
    }

    public function testRedirect(): void
    {
        $action = new AuthAction(null, null);

        $url = 'http://test.url';
        $response = $action->redirect($url, true);

        $this->assertStringContainsString($url, $response->content);
    }

    public function testGetClientId(): void
    {
        $clientId = 'clientId';
        $defaultClientId = 'defaultClientId';

        $action = new AuthAction(null, null);

        $this->assertEmpty($action->getClientId());

        $action->defaultClientId = $defaultClientId;

        $this->assertEquals($defaultClientId, $action->getClientId(), 'Unable to get default client ID!');

        $_GET['authclient'] = $clientId;
        $this->assertEquals($clientId, $action->getClientId(), 'Unable to get default client ID!');
    }

    /**
     * @param array<mixed> $clientId
     * @dataProvider invalidClientIdDataProvider
     */
    public function testRunRejectsArrayClientId(array $clientId): void
    {
        $app = Yii::$app;
        $this->assertInstanceOf(Application::class, $app);
        $app->set('authClientCollection', [
            'class' => \yii\authclient\Collection::class,
        ]);
        $app->getRequest()->setQueryParams(['authclient' => $clientId]);
        $action = new AuthAction('auth', new Controller('site', $app), ['defaultClientId' => 'google']);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid auth client ID.');

        $action->run();
    }

    /**
     * @return array<string, array{array<mixed>}>
     */
    public function invalidClientIdDataProvider(): array
    {
        return [
            'array' => [['google']],
            'nested array' => [[['google']]],
            'empty array' => [[]],
        ];
    }

    public function testGetClientIdWithCustomParameter(): void
    {
        $app = Yii::$app;
        $this->assertInstanceOf(Application::class, $app);
        $app->getRequest()->setQueryParams(['provider' => '123']);
        $action = new AuthAction('auth', new Controller('site', $app), ['clientIdGetParamName' => 'provider']);

        $this->assertSame('123', $action->getClientId());
    }

    /**
     * @dataProvider defaultClientDataProvider
     */
    public function testRunWithDefaultClient(?string $requestedId, string $expectedId): void
    {
        $app = Yii::$app;
        $this->assertInstanceOf(Application::class, $app);
        $collection = new Collection(['clients' => [
            'default' => ['class' => Google::class],
            'explicit' => ['class' => Google::class],
        ]]);
        $app->set('authClientCollection', $collection);
        $app->getRequest()->setQueryParams($requestedId === null ? [] : ['authclient' => $requestedId]);
        $action = $this->getMockBuilder(AuthAction::class)
            ->setConstructorArgs(['auth', new Controller('site', $app), ['defaultClientId' => 'default']])
            ->onlyMethods(['auth'])
            ->getMock();
        $action->expects($this->once())->method('auth')
            ->with($this->identicalTo($collection->getClient($expectedId)))
            ->willReturn($app->getResponse());

        $this->assertSame($app->getResponse(), $action->run());
    }

    /**
     * @return array<string, array{string|null, string}>
     */
    public function defaultClientDataProvider(): array
    {
        return [
            'default client' => [null, 'default'],
            'explicit client overrides default' => ['explicit', 'explicit'],
        ];
    }

}
