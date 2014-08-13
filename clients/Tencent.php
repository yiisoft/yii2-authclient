<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;
use yii\helpers\Json;

/**
 * Tencent allows authentication via Tencent OAuth.
 * 腾讯互联的第三方登陆模块
 *
 * In order to use Tencent OAuth you must register your application at <http://connect.qq.com>.
 * 为了使用这个模块你应该在腾讯互联注册你的应用
 *
 * Example application configuration:
 * 下面是配置你的应用的一个例子，其中clientId是你在注册你的应用获得的appid，
 * clientSecret是你获得的密钥
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'tencent' => [
 *                 'class' => 'yii\authclient\clients\Tencent',
 *                 'clientId' => 'github_client_id',
 *                 'clientSecret' => 'github_client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see http://wiki.connect.qq.com/
 * @see http://connect.qq.com/manage/index?apptype=web
 *
 * @author kangqingfei <kangqingfei@gmail.com> http://weibo.com/u/3227269845
 * @since 1.0
 */

class Tencent extends OAuth2
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'https://graph.qq.com/oauth2.0/authorize';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://graph.qq.com/oauth2.0/token';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://graph.qq.com';

    public $openidUrl = 'https://graph.qq.com/oauth2.0/me';

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        return $this->api('user/get_user_info', 'GET');
    }

    /**
     * @inheritdoc
     */
    protected function apiInternal($accessToken, $url, $method, array $params, array $headers)
    {
        $params['access_token'] = $accessToken->getToken();
        $Openid = $this->getOpenId($accessToken, $url, $method, $params, []);      
        $params['openid'] = $Openid['openid'];
        $params['oauth_consumer_key'] = $this->clientId;       
        return $this->sendRequest($method, $url, $params, $headers);
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'tencent';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return '腾讯互联';
    }

    /**
     * @return array openid used to get the information.
     */
    protected function getOpenId($accessToken, $url, $method, array $params, array $headers)
    {
        $openidParams = ['grant_type' => 'openid_code','access_token' =>  $params['access_token']];
        $curlOptions = $this->mergeCurlOptions(
            $this->defaultCurlOptions(),
            $this->getCurlOptions(),
            [
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $this->openidUrl,
            ],

            $this->composeRequestCurlOptions(strtoupper($method), $this->openidUrl, $openidParams)
        );

        $openidCurlResource = curl_init();
        foreach ($curlOptions as $option => $value) {
            curl_setopt($openidCurlResource, $option, $value);
        }

        $output = curl_exec($openidCurlResource);
        $outputHeaders = curl_getinfo($openidCurlResource);
        $errorNumber = curl_errno($openidCurlResource);
        $errorMessage = curl_error($openidCurlResource);

        curl_close($openidCurlResource);

        if ($errorNumber > 0) {
            throw new Exception('Curl error requesting "' .  $url . '": #' . $errorNumber . ' - ' . $errorMessage);
        }
        if ($outputHeaders['http_code'] != 200) {
            throw new InvalidResponseException($outputHeaders, $output, 'Request failed with code: ' . $outputHeaders['http_code'] . ', message: ' . $output);
        }
        $temp = [];
        preg_match('/callback\(\s+(.*?)\s+\)/i', $output,$temp);
        $outputOpenid = Json::decode($temp[1], true);  
        if (isset($outputOpenid['error'])) {
            throw new Exception('Response error: ' . $outputOpenid['error']);
        }
        return $outputOpenid;
    }
}
