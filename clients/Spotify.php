<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * Spotify allows authentication via Spotify OAuth.
 *
 * In order to use Spotify OAuth you must register your application at <https://developer.spotify.com/my-applications/>.
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'spotify' => [
 *                 'class' => 'yii\authclient\clients\Spotify',
 *                 'clientId' => 'spotify_client_id',
 *                 'clientSecret' => 'spotify_client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ```
 *
 * @see https://developer.spotify.com/web-api/authorization-guide/
 * @see https://developer.spotify.com/my-applications/
 *
 * @author Marek Viger <marek.viger@gmail.com>
 * @since 2.0.6
 */
class Spotify extends OAuth2
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'https://accounts.spotify.com/authorize';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://accounts.spotify.com/api/token';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.spotify.com/v1';
    /**
     * @inheritdoc
     */
    public $scope = 'user-read-email';

    /**
     * @var array list of attribute names, which should be requested from API to initialize user attributes.
     */
    public $attributeNames = [
        'id',
        'display_name',
        'email',
    ];

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        return $this->api('me', 'GET', [
            'fields' => implode(',', $this->attributeNames),
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'name' => 'display_name',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'spotify';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Spotify';
    }
}
