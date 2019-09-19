<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * LemonLDAPNG allows authentication via LemonLDAP::NG Oauth2
 *
 * In order to use LemonLDAPNG OAuth2 you must have a OIDC service set up and configure a relying party for your app in your LemonLDAP::NG.
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *    'authClientCollection' => [
 *      'clients' => [
 *         // ...
 *         'lemonldapng' => [
 *             'class' => 'yii\authclient\clients\LemonLdapNG',
 *             'clientId' => 'myClientId',
 *             'clientSecret' => 'myClientIdSecret',
 *             'scope' => 'openid profile email',
 *             'authUrl' => 'https://auth.example.com/oauth2/authorize',
 *             'tokenUrl' => 'https://auth.example.com/oauth2/token',
 *             'apiBaseUrl' => 'https://auth.example.com/oauth2',
 *             'defaultName' => 'lemonldapng',
 *             'defaultTitle' => 'auth.example.com'
 *         ],
 *      ],
 *     // ...
 * ]
 * ```
 *
 * @see https://lemonldap-ng.org/documentation/latest/openidconnectservice
 * @see https://lemonldap-ng.org/documentation/latest/idpopenidconnect
 * @see https://auth.example.com/.well-known/openid-configuration
 *
 * @author Soisik Froger <soisik.froger@worteks.com>
 * @since 2.2.5
 */

class LemonLdapNG extends OAuth2
{
    public $defaultName;
    public $defaultTitle;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->scope === null) {
          $this->scope = 'openid profile email';
        }
        if ($this->defaultName === null) {
          $this->defaultName = 'lemonldapng';
        }
        if ($this->defaultTitle === null) {
          $this->defaultTitle = 'LemonLDAP::NG';
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
      $accessToken = $this->getAccessToken();
      if (!is_object($accessToken) || !$accessToken->getIsValid()) {
        throw new Exception('Invalid access token.');
      }
      $headers = [
        'Authorization' => 'Bearer ' . $this->getAccessToken()->getToken(),
        'Content-Type:' => 'application/json'
      ];
      return $this->api('userinfo', 'GET', [], $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeApiRequestSend($event)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return $this->defaultName;
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return $this->defaultTitle;
    }
}
