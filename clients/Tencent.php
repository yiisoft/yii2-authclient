<?php
namespace yii\authclient\clients;

use yii\authclient\OAuth2;
use yii\helpers\Json;

//https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=1101994141&redirect_uri=www.qq.com/my.php&scope=get_user_info
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

        $temp = array();
        preg_match('/callback\(\s+(.*?)\s+\)/i', $output,$temp);
        $outputOpenid = Json::decode($temp[1], true);  
        if (isset($outputOpenid['error'])) {
            throw new Exception('Response error: ' . $outputOpenid['error']);
        }
        return $outputOpenid;
    }
}
