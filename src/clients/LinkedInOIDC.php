<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;
use Yii;

/**
 * LinkedIn allows authentication via LinkedIn OAuth.
 *
 * In order to use linkedIn OAuth you must register your application at <https://www.linkedin.com/secure/developer>.
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'linkedin' => [
 *                 'class' => 'yii\authclient\clients\LinkedIn',
 *                 'clientId' => 'linkedin_client_id',
 *                 'clientSecret' => 'linkedin_client_secret',
 *             ],
 *         ],
 *     ]
 *     // ...
 * ]
 * ```
 *
 * @see https://learn.microsoft.com/en-us/linkedin/consumer/integrations/self-serve/sign-in-with-linkedin-v2
 * @see https://www.linkedin.com/secure/developer
 *
 * @author Mohammed Najem Ali <mohammednjmali@gmail.com>
 * @since 2.0
 */
class LinkedInOIDC extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $authUrl = 'https://www.linkedin.com/oauth/v2/authorization';
    /**
     * {@inheritdoc}
     */
    public $tokenUrl = 'https://www.linkedin.com/oauth/v2/accessToken';
    /**
     * {@inheritdoc}
     */
    public $apiBaseUrl = 'https://api.linkedin.com/v2';
    /**
     * @var array list of attribute names, which should be requested from API to initialize user attributes.
     * @since 2.0.4
     */
    public $attributeNames = [
        'id',
        'given_name',
        'family_name',
        'email',
        'picture'
    ];


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->scope === null) {
            $this->scope = implode(' ', [
                'openid',
                'profile',
                'email', 
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'first_name' => function ($attributes) {
                return $attributes['given_name'];
            },
            'last_name' => function ($attributes) {
                return $attributes['family_name'];
            },
            'email' => function ($attributes) {
                return $attributes['email'];
            },
            'profile_image_url' => function ($attributes) {
                return $attributes['picture']; 
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
        $attributes = $this->api('userinfo', 'GET');
        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAccessTokenToRequest($request, $accessToken)
    {
        $request->addHeaders(['Authorization' => 'Bearer ' . $accessToken->getToken()]);
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return 'linkedin';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'LinkedInOIDC';
    }
}
