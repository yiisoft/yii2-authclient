<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;
use Yii;

/**
 * Twitch allows authentication via Twitch OAuth.
 *
 * In order to use Twitch OAuth you must register your application at <https://www.twitch.tv/kraken/oauth2/clients/new>.
 *
 * Example application configuration:
 *
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'twitch' => [
 *                 'class' => 'yii\authclient\clients\Twitch',
 *                 'clientId' => 'twitch_client_id',
 *                 'clientSecret' => 'twitch_client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ```
 *
 * @see https://github.com/justintv/Twitch-API
 *
 * @author Oleg Balykin <ezoterik.h@gmail.com>
 * @since 2.0
 */
class Twitch extends OAuth2
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'https://api.twitch.tv/kraken/oauth2/authorize';

    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://api.twitch.tv/kraken/oauth2/token';

    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.twitch.tv/kraken';

    /**
     * @inheritdoc
     */
    public $scope = 'user_read';

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        return $this->api('user', 'GET');
    }

    /**
     * @inheritdoc
     */
    protected function apiInternal($accessToken, $url, $method, array $params, array $headers)
    {
        $params['oauth_token'] = $accessToken->getToken();

        return $this->sendRequest($method, $url, $params, $headers);
    }

    /**
     * @inheritdoc
     */
    protected function defaultReturnUrl()
    {
        $params = $_GET;
        unset($params['code']);
        unset($params['scope']);
        $params[0] = Yii::$app->controller->getRoute();

        return Yii::$app->getUrlManager()->createAbsoluteUrl($params);
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'twitch';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Twitch';
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'id' => '_id',
        ];
    }
}
