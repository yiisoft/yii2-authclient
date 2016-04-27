<?php

namespace yii\authclient\clients;

use yii\authclient\OAuth2;
use yii\web\HttpException;
use Yii;

/**
 * Weixin(Wechat) allows authentication via Weixin(Wechat) OAuth.
 *
 * In order to use Weixin(Wechat) OAuth you must register your application at <https://open.weixin.qq.com/>.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'weixin' => [
 *                 'class' => 'yii\authclient\clients\Weixin',
 *                 'clientId' => 'weixin_appid',
 *                 'clientSecret' => 'weixin_appkey',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see https://open.weixin.qq.com/
 * @see https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&lang=zh_CN
 *
 * @author Jiandong Yu <flyyjd@gmail.com>
 * @since 2.0
 */
class Weixin extends OAuth2
{

    /**
     * @inheritdoc
     */
    public $authUrl = 'https://open.weixin.qq.com/connect/qrconnect';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.weixin.qq.com';

    /**
     * @inheritdoc
     */
    public function init()
	{
        parent::init();
        if ($this->scope === null) {
            $this->scope = implode(' ', [
                'snsapi_login',
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'id' => 'openid',
            'username' => 'nickname',
        ];
    }

    /**
     * @inheritdoc
     */
    public function buildAuthUrl(array $params = [])
	{
        $authState = $this->generateAuthState();
        $this->setState('authState', $authState);
        $params['state'] = $authState;
        $params['appid'] = $this->clientId;
        return parent::buildAuthUrl($params);
    }

    /**
     * @inheritdoc
     */
    public function fetchAccessToken($authCode, array $params = [])
	{
        $authState = $this->getState('authState');
        if (!isset($_REQUEST['state']) || empty($authState) || strcmp($_REQUEST['state'], $authState) !== 0) {
            throw new HttpException(400, 'Invalid auth state parameter.');
        } else {
            $this->removeState('authState');
        }

        $params['appid'] = $this->clientId;
        $params['secret'] = $this->clientSecret;
		return parent::fetchAccessToken($authCode, $params);

    }

    /**
     * @inheritdoc
     */
    protected function apiInternal($accessToken, $url, $method, array $params, array $headers)
	{
        $params['access_token'] = $accessToken->getToken();
        $params['openid'] = $accessToken->getParam('openid');
        return $this->sendRequest($method, $url, $params, $headers);
    }

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
	{
        return $this->api('sns/userinfo');
    }

    /**
     * @inheritdoc
     */
    protected function defaultReturnUrl()
    {
        $params = $_GET;
        unset($params['code']);
        unset($params['state']);
        $params[0] = Yii::$app->controller->getRoute();

        return Yii::$app->getUrlManager()->createAbsoluteUrl($params);
    }

    /**
     * Generates the auth state value.
     * @return string auth state value.
     */
    protected function generateAuthState()
    {
        return sha1(uniqid(get_class($this), true));
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
	{
        return 'weixin';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
	{
        return 'Weixin';
    }

}
