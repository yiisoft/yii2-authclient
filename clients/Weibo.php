<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * Tencent allows authentication via Tencent OAuth.
 * 新浪微博的第三方登陆模块
 *
 * In order to use Tencent OAuth you must register your application at <http://open.weibo.com/apps.
 * 为了使用这个模块你应该在新浪微博注册你的应用
 *
 * Example application configuration:
 * 下面是配置你的应用的一个例子，其中clientId是你在注册你的应用获得的appid，
 * clientSecret是你获得的密钥
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'weibo' => [
 *                 'class' => 'yii\authclient\clients\Weibo',
 *                 'clientId' => 'github_client_id',
 *                 'clientSecret' => 'github_client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see http://open.weibo.com/wiki/
 * @see http://open.weibo.com/wiki/%E5%BE%AE%E5%8D%9AAPI
 *
 * @author kangqingfei <kangqingfei@gmail.com> http://weibo.com/u/3227269845
 * @since 1.0
 */
class Weibo extends OAuth2
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'https://api.weibo.com/oauth2/authorize';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://api.weibo.com/oauth2/access_token';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.weibo.com/2';

    /**
     * @inheritdoc
     */
    protected function apiInternal($accessToken, $url, $method, array $params, array $headers)
    {
        $params['access_token'] = $accessToken->getToken();
        $params['uid'] = $accessToken->getParam('uid');
        return $this->sendRequest($method, $url, $params, $headers);
    }

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        return $this->api('users/show.json', 'GET');
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'weibo';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return '新浪微博';
    }
}
 ?>
