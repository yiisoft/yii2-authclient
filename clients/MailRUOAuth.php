<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * Mail.RU allows authentication via Mail.RU OAuth.
 *
 * In order to use Mail.Ru OAuth you must register your application at <http://api.mail.ru/docs/guides/oauth/sites>.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'mailru' => [
 *                 'class' => 'yii\authclient\clients\MailRUOAuth',
 *                 'clientId' => 'mailru_client_id',
 *                 'clientSecret' => 'mailru_client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see http://api.mail.ru/docs/guides/oauth/sites/
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * Marat Paritov <qstatix@gmail.com>
 * @since 2.0
 * 
 */
 
 
 
 use  yii\base\Exception;
 use  yii\helpers\BaseVarDumper;
 use  yii;
 use yii\helpers\Json;
 
class MailRUOAuth extends OAuth2
{
	/**
     * @inheritdoc
    */
    
    public $authUrl = 'https://connect.mail.ru/oauth/authorize';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://connect.mail.ru/oauth/token';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'http://www.appsmail.ru/platform';
   
    
        /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {		
    	return $this->api('api', 'GET');
    }


	/**
     * @inheritdoc
     */
     
     
protected function processResponse($rawResponse, $contentType = self::CONTENT_TYPE_AUTO)
    {	
    	if(!isset(json_decode($rawResponse)->access_token)){
    		$r = Json::decode($rawResponse, true);
    		return $r[0];
    	}
    
        if (empty($rawResponse)) {
            return [];
        }
        switch ($contentType) {
            case self::CONTENT_TYPE_AUTO: {
                $contentType = $this->determineContentTypeByRaw($rawResponse);
                if ($contentType == self::CONTENT_TYPE_AUTO) {
                    throw new Exception('Unable to determine response content type automatically.');
                }
                $response = $this->processResponse($rawResponse, $contentType);
                break;
            }
            case self::CONTENT_TYPE_JSON: {
                $response = Json::decode($rawResponse, true);
                break;
            }
            case self::CONTENT_TYPE_URLENCODED: {
                $response = [];
                parse_str($rawResponse, $response);
                break;
            }
            case self::CONTENT_TYPE_XML: {
                $response = $this->convertXmlToArray($rawResponse);
                break;
            }
            default: {
                throw new Exception('Unknown response type "' . $contentType . '".');
            }
        }
        return $response;
    }

    
    protected function apiInternal($accessToken, $url, $method, array $params, array $headers)
    {	
    	$headers['content_type'] = 'json';
    	$params['app_id'] = $this->clientId;
    	$params['format'] = 'json';
		$params['method'] = 'users.getInfo';
		$params['session_key'] = $accessToken->getParam('access_token');
		$params['secure'] = 1;
		
		ksort($params);
		$fparams = '';
		foreach ($params as $k => $v) {
			$fparams .= $k . '=' . $v;
		}
		$params['sig'] = md5($fparams . $this->clientSecret);
		
        return $this->sendRequest($method, $url, $params, $headers);
    }
    

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'mailru';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Mail.ru';
    }

}
