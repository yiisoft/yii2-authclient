<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * Box allows authentication via Box OAuth.
 *
 * In order to use Box OAuth you must register your application at <https://app.box.com/developers/services/edit>.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'box' => [
 *                 'class' => 'yii\authclient\clients\Box',
 *                 'clientId' => 'box_client_id',
 *                 'clientSecret' => 'box_client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see https://developers.box.com/
 * @see https://box-content.readme.io/reference#oauth-2
 *
 * @author AlQurashi, Abdallah <qrshi90@@gmail.com>
 * @since 2.0
 */
class Box extends OAuth2
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'https://app.box.com/api/oauth2/authorize';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://api.box.com/oauth2/token';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.box.com/2.0';
    /**
     * @inheritdoc
     */
    public $scope = '';

    /**
    * @var array list of attribute names, which should be requested from API to initialize user attributes.
    * @since 2.0.5
    */
   public $attributeNames = [
       'name',
       'login',
   ];

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        return $this->api('users/me', 'GET');
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'box';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Box';
    }
}
