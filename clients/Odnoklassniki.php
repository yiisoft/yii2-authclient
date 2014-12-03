<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\components\clients;

use yii\authclient\OAuth2;

/**
 * Odnoklassniki OAuth2 client 
 *
 * In order to use OK OAuth you must register your application at odnoklassniki.ru
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *               'odnoklassniki' => [
 *                   'class' => 'common\components\clients\OdnoklassnikiClient',
 *                   'clientId' => '{Application ID}',
 *                   'clientSecret' => '{Secret Key}',
 *                   'application_key' => '{Public Key}',
 *               ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @author zckri <joker7789@gmail.com>
 */
class Odnoklassniki extends OAuth2
{
    
    /**
     * @inheritdoc
     */
    public $application_key;
    /**
     * @inheritdoc
     */
    public $authUrl = 'http://www.odnoklassniki.ru/oauth/authorize';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://api.odnoklassniki.ru/oauth/token.do';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'http://api.odnoklassniki.ru';
    /**
     * @inheritdoc
     */
    public $scope = 'VALUABLE_ACCESS';

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        return $this->api('api/users/getCurrentUser', 'GET');
    }

    /**
     * @inheritdoc
     */
    protected function apiInternal($accessToken, $url, $method, array $params, array $headers)
    {
        $params['access_token'] = $accessToken->getToken();
        $params['application_key'] = $this->application_key;
        $params['method'] = str_replace('/','.', str_replace('api/','',$url));

        $a = 'application_key='.$this->application_key.'method='.$params['method'];
        $b = md5($params['access_token'].$this->clientSecret);
        $signature = md5($a.$b);

        $params['sig'] = $signature;

        return $this->sendRequest($method, $url, $params, $headers);
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'odnoklassniki';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Odnoklassniki';
    }
}
