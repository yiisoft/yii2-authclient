<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth1;

/**
 * Tumblr allows authentication via Tumblr OAuth.
 *
 * In order to use Tumblr OAuth you must register your application at <https://www.tumblr.com/oauth/apps>.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'tumblr' => [
 *                 'class' => 'yii\authclient\clients\Tumblr',
 *                 'consumerKey' => 'tumblr_consumer_key',
 *                 'consumerSecret' => 'tumblr_consumer_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see https://www.tumblr.com/oauth/apps
 * @see https://www.tumblr.com/docs/en/api/v2
 *
 * @author Alex Vlasov <xmrerx@gmail.com>
 * @since 2.0
 */
class Tumblr extends OAuth1
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'https://www.tumblr.com/oauth/authorize';
    /**
     * @inheritdoc
     */
    public $requestTokenUrl = 'https://www.tumblr.com/oauth/request_token';
    /**
     * @inheritdoc
     */
    public $requestTokenMethod = 'POST';
    /**
     * @inheritdoc
     */
    public $accessTokenUrl = 'https://www.tumblr.com/oauth/access_token';
    /**
     * @inheritdoc
     */
    public $accessTokenMethod = 'POST';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.tumblr.com/v2';


    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        return $this->api('account/verify_credentials.json', 'GET');
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'tumblr';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Tumblr';
    }
}
