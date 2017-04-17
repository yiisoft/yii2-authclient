<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * Weibo allows authentication via Weibo OAuth.
 *
 * In order to use Weibo OAuth you must register your application at <http://open.weibo.com>.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'weibo' => [
 *                 'class' => 'yii\authclient\clients\Weibo',
 *                 'clientId' => 'weibo_client_id',
 *                 'clientSecret' => 'weibo_client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see http://open.weibo.com
 * @see http://open.weibo.com/wiki
 *
 * @author Allen <aiddroid@gmail.com>
 * @since 2.0
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
    public $scope = 'email';

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        $ret = $this->api('account/get_uid.json', 'GET');
        return $this->api('users/show.json', 'GET', $ret);
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
        return 'Weibo';
    }

    /**
     * @inheritdoc
     */
    protected function defaultViewOptions()
    {
        return [
            'popupWidth' => 860,
            'popupHeight' => 480,
        ];
    }
}

