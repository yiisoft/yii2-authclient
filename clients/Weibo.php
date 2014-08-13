<?php
namespace yii\authclient\clients;

use yii\authclient\OAuth2;

//https://api.weibo.com/oauth2/authorize?client_id=130617301&response_type=code&redirect_uri=http://kangqingfei.sinaapp.com/
//https://api.weibo.com/oauth2/access_token?client_id=130617301&client_secret=1ddf6908903658da115c5ea525bb2159&grant_type=authorization_code&redirect_uri=http://kangqingfei.sinaapp.com/&code=CODE


class Weibo extends OAuth2
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'https://api.weibo.com/oauth2/authorize';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://api.weibo.com/oauth2/access_token';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.weibo.com/2';

    /**
     * @inheritdoc
     */
    protected function apiInternal($accessToken, $url, $method, array $params, array $headers)
    {
        $params['access_token'] = $accessToken->getToken();
        $params['uid'] = $accessToken->getParam('uid');
        return $this->sendRequest($method, $url, $params, $headers);
    }

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        return $this->api('users/show.json', 'GET');
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'weibo';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return '新浪微博';
    }
}


 ?>
