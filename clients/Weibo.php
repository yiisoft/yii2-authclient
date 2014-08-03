<?
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;


/**
 * Weibo allows authentication via Weibo OAuth.
 *
 * In order to use Weibo OAuth you must register your application at <http://open.weibo.com/apps>.
 *
 * Example application configuration:
 *
 * In config.ini
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'weibo' => [
 *                 'class' => 'yii\authclient\clients\Weibo',
 *                 'clientId' => 'weibo_client_id',
 *                 'clientSecret' => 'weibo_client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see http://open.weibo.com
 * @see http://open.weibo.com/wiki/%E9%A6%96%E9%A1%B5
 *
 * @author TroyFan <fan_ye@hotmail.com>
 * @since 2.0
 */


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
	public $apiBaseUrl = 'https://api.weibo.com';
	/**
	 * @inheritdoc
	 */
	public $scope = '';


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
	public function apiInternal($accessToken, $url, $method, array $params){
		$params ['uid'] = $accessToken->getParam('uid');
		$params['access_token'] = $accessToken->getToken();

		return $this->sendRequest($method, $url, $params);
	}

	/**
	 * @inheritdoc
	 */
	public function fetchAccessToken($authCode, array $params = [])
	{
		$token = parent::fetchAccessToken($authCode, $params = []);
		$token->setExpireDurationParamKey($token->getParam('expires_in'));
		$token->setToken($token->getParam('access_token'));

		return $token;
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
		return 'weibo';
	}

    /**
     * @inheritdoc
     */
	public function defaultNormalizeUserAttributeMap(){
		return  [
			'connect_id' => 'id',
			'name' => 'name',
			'screen_name'=>'screen_name',
			'avatar_large' => 'avatar_large',
			'avatar_small'=> 'profile_image_url',
			'location'=>'location',
			'description'=>'description',
			'url'=>'url',
			'weibo_status' =>'status',
		];
	}
}