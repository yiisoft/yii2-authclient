<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth1;

/**
 * Twitter allows authentication via Twitter OAuth.
 *
 * In order to use Twitter OAuth you must register your application at <https://dev.twitter.com/apps/new>.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'twitter' => [
 *                 'class' => 'yii\authclient\clients\Twitter',
 *                 'requestEmail' => 'true',
 *                 'consumerKey' => 'twitter_consumer_key',
 *                 'consumerSecret' => 'twitter_consumer_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see https://dev.twitter.com/apps/new
 * @see https://dev.twitter.com/docs/api
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Twitter extends OAuth1
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'https://api.twitter.com/oauth/authenticate';
    /**
     * @inheritdoc
     */
    public $requestTokenUrl = 'https://api.twitter.com/oauth/request_token';
    /**
     * @inheritdoc
     */
    public $requestTokenMethod = 'POST';
    /**
     * @inheritdoc
     */
    public $accessTokenUrl = 'https://api.twitter.com/oauth/access_token';
    /**
     * @inheritdoc
     */
    public $accessTokenMethod = 'POST';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.twitter.com/1.1';


    /**
     * @var bool whether to request user's email from Twitter API.
     * The �Request email addresses from users� checkbox must be set in your app permissions 
     * @see https://dev.twitter.com/rest/reference/get/account/verify_credentials Twitter API documentation
     */
    public $requestEmail = false;
  
    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        $params = [];
        if($this->requestEmail){
          $params = ['include_email' => 'true'];
        }
        return $this->api('account/verify_credentials.json', 'GET', $params);
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'twitter';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Twitter';
    }
}
