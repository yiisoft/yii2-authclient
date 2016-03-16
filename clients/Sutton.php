<?php

namespace yii\authclient\clients;

use yii\authclient\OAuth2;
use yii\web\HttpException;
use Yii;

/**
 * Sutton allows authentication via Sutton OAuth.
 *
 * In order to use sutton OAuth you must register with Sutton
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'sutton' => [
 *                 'class' => 'yii\authclient\clients\Sutton',
 *                 'clientId' => 'sutton_client_id',
 *                 'clientSecret' => 'sutton_client_secret',
 *                 'username' => 'sutton_username',
 *                 'password' => 'sutton_password',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see http://docs.suttonapi.apiary.io/
 *
 * @author Steve Morocho <steve.morocho@backatyou.com>
 */
class Sutton extends OAuth2
{
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://api.sutton.com/v1/oauth2/token';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.sutton.com/v1';
    /**
     * @var array list of attribute names, which should be requested from API to initialize user attributes.
     * @since 2.0.4
     */
    public $attributeNames = [
        'id',
    	'username',
        'email',
        'first_name',
    	'middle_name',
        'last_name',
    	'preferred_first_name',
        'picture_file_id'
    ];

    /**
     * @var string Sutton provided username
     */
    public $username;
    public $password;

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
    	return $this->api('oauth2/me', 'GET');
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'sutton';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Sutton';
    }
}
