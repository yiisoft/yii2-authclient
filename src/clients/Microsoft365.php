<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * Microsoft365 allows authentication via Microsoft365 (aka Office365 or Azure AD) OAuth2 Organizations endpoints.
 *
 * Before using Microsoft365 OAuth2 you must register your App on the Microsoft Azure Portal <https://portal.azure.com>
 *
 * Note: the registered App must have the following:
 * -Authentication: set the 'Redirect URIs' e.g. 'site/auth?authclient=microsoft365' as an absolute URL e.g. https://domain.com/index.php/site/auth?authclient=microsoft365
 * -API Permissions: 'Microsoft Graph' > 'User.Read'
 * -Decide whether the App should be single-tenant (only allow your own Microsoft Tenant to use it) or multi-tenant (allow any other Microsoft Tenants to use it)
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'microsoft365' => [
 *                 'class' => 'yii\authclient\clients\Microsoft365',
 *                 'clientId' => 'a5e19acd-dc50-4b0a-864a-d13b9347ddf9',
 *                 'clientSecret' => 'ljSAd89.lvk34NV-3t4v3_2kl_42Rt4klr234',
 *             ],
 *         ],
 *     ]
 *     // ...
 * ]
 * ```
 *
 * @see https://portal.azure.com
 * @see https://learn.microsoft.com/en-us/power-apps/developer/data-platform/walkthrough-register-app-azure-active-directory
 * @see https://learn.microsoft.com/en-us/graph/use-the-api
 *
 * @author Enrico De Gaudenzi <enrico@degaudenzi.eu>
 * @since 2.2.15
 */
class Microsoft365 extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $authUrl = 'https://login.microsoftonline.com/organizations/oauth2/v2.0/authorize';
    /**
     * {@inheritdoc}
     */
    public $tokenUrl = 'https://login.microsoftonline.com/organizations/oauth2/v2.0/token';
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
            $this->scope = implode(' ', [
                'User.Read',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
        return $this->api('me', 'GET');
    }

    /**
     * {@inheritdoc}
     */
    public function applyAccessTokenToRequest($request, $accessToken)
    {
        $request->headers->set('Authorization', 'Bearer '.$accessToken->getToken());
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return 'microsoft365';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'Microsoft365';
    }
}
