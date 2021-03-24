<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * Azure allows authentication via Microsoft Azure OAuth.
 *
 * In order to use Microsoft Azure OAuth you must register your application at <https://portal.azure.com/>
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'live' => [
 *                 'class' => 'yii\authclient\clients\Azure',
 *                 'clientId' => 'azure_client_id',
 *                 'clientSecret' => 'azure_client_secret',
 *             ],
 *         ],
 *     ]
 *     // ...
 * ]
 * ```
 * 
 * @see https://docs.microsoft.com/en-us/azure/active-directory/develop/
 * @see https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-oauth2-auth-code-flow
 *
 * @author Law Wai Chun <chun10161991@gmail.com>
 * @since 2.0
 */
class Azure extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $authUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
    /**
     * {@inheritdoc}
     */
    public $tokenUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    /**
     * {@inheritdoc}
     */
    public $apiBaseUrl = 'https://graph.microsoft.com/v1.0';


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->scope === null) {
            $this->scope = 'user.read';
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
        return $this->api('me', 'GET');
    }

    public function applyAccessTokenToRequest($request, $accessToken)
    {
        $request->addHeaders(['Authorization' => 'Bearer '. $accessToken->getToken()]);
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return 'azure';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'Azure';
    }
}
