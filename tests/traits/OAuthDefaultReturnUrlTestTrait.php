<?php

namespace yiiunit\extensions\authclient\traits;

use yii\authclient\BaseOAuth;

trait OAuthDefaultReturnUrlTestTrait
{
    /**
     * @return mixed
     */
    abstract protected function createClient();

    /**
     * Data provider for [[testDefaultReturnUrl]].
     * @return array test data.
     */
    abstract public function defaultReturnUrlDataProvider();

    /**
     * @dataProvider defaultReturnUrlDataProvider
     *
     * @param $requestQueryParams
     * @param $parametersToKeepInReturnUrl
     * @param $expectedReturnUrl
     */
    public function testDefaultReturnUrl($requestQueryParams, $parametersToKeepInReturnUrl, $expectedReturnUrl)
    {
        $module = \Yii::createObject(\yii\base\Module::className(), ['module']);
        $controller = \Yii::createObject(\yii\web\Controller::className(), ['default', $module]);
        $app = $this->mockWebApplication([
            'components' => [
                'request' => [
                    'queryParams' => $requestQueryParams,
                ],
                'urlManager' => [
                    'enablePrettyUrl' => true,
                    'showScriptName' => false,
                    'rules' => [
                        '/' => '/module/default',
                    ],
                ],
            ],
            'controller' => $controller,
        ]);

        /** @var BaseOAuth $oauthClient */
        $oauthClient = $this->createClient();
        if (!empty($parametersToKeepInReturnUrl)) {
            $oauthClient->parametersToKeepInReturnUrl = $parametersToKeepInReturnUrl;
        }

        $this->assertEquals($expectedReturnUrl, $oauthClient->getReturnUrl());
    }
}
