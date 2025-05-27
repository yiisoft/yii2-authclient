<?php

namespace yiiunit\extensions\authclient;

use yii\authclient\AuthAction;
use yii\authclient\Collection;

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

    public function testSetGet()
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
    public function testGetDefaultSuccessUrl()
    {
        $action = new AuthAction(null, null);

        $this->assertNotEmpty($action->getSuccessUrl(), 'Unable to get default success URL!');
    }

    /**
     * @depends testSetGet
     */
    public function testGetDefaultCancelUrl()
    {
        $action = new AuthAction(null, null);

        $this->assertNotEmpty($action->getSuccessUrl(), 'Unable to get default cancel URL!');
    }

    public function testRedirect()
    {
        $action = new AuthAction(null, null);

        $url = 'http://test.url';
        $response = $action->redirect($url, true);

        $this->assertStringContainsString($url, $response->content);
    }

    public function testGetClientId()
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
}
