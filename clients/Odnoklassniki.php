<?php
namespace yii\authclient\clients;
use yii\authclient\OAuth2;

/**
 * Class Odnoklassniki
 * @package yii\authclient\clients
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'odnoklassniki' => [
 *                 'class' => 'yii\authclient\clients\odnoklassniki',
 *                 'clientId' => 'app_client_id',
 *                 'clientSecret' => 'application_client_secret', 
 *                 'application_public_key' => 'application_public_key',
 *                 'scope' => 'VALUABLE_ACCESS'
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 */
class Odnoklassniki  extends OAuth2
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'http://www.odnoklassniki.ru/oauth/authorize';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'http://api.odnoklassniki.ru/oauth/token.do';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'http://api.odnoklassniki.ru/fb.do';

    public $application_public_key;

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
       return $this->api('','GET',[
           'method' => 'users.getCurrentUser',
           'format' => 'JSON',
           'application_key' => $this->application_public_key,
           'client_id' => $this->clientId,
       ]);
    }

    /**
     * @inheritdoc
     */
    protected function apiInternal($accessToken, $url, $method, array $params, array $headers)
    {
        $access_token = $accessToken->getToken();
        if (count($params)) {
            $param_str = '';
            ksort($params);
            foreach ($params as $k => $v)
            {
                $param_str .= $k . '=' . $v;
            }
            $params['sig'] = md5($param_str . md5($access_token . $this->clientSecret));
        }
        $params['access_token'] = $access_token;
        return $this->sendRequest($method, trim($url,'/'), $params, $headers);
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'odnoklassniki';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Odnoklassniki';
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'id' => 'uid'
        ];
    }

} 