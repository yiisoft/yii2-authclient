<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * Generic client that allows authentication via OAuth 2.0.
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'oauth2' => [
 *                 'class' => 'yii\authclient\clients\Oauth2Client',
 *                 'authUrl' => 'https://oauth2service.com/oauth2/authorize',
 *                 'tokenUrl' => 'https://oauth2service.com/oauth2/authorize',
 *                 'apiBaseUrl' => 'https://oauth2service.com/api',
 *                 'clientId' => 'your_app_client_id',
 *                 'clientSecret' => 'your_app_client_secret',
 *                 'name' => 'custom name',
 *                 'title' => 'custom title'
 *             ],
 *         ],
 *     ]
 *     // ...
 * ]
 * ```
 *
 * @since 2.2.16
 */
class Oauth2Client extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $accessTokenLocation = self::ACCESS_TOKEN_LOCATION_HEADER;


    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
        return []; // Plain Oauth 2.0 doesn't specify user attributes.
    }
}
