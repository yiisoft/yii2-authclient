<?php
	namespace kroshilin\authclient\clients;
	use yii\authclient\OAuth2;
	/**
	 * Odnoklassniki allows authentication via Odnoklassniki OAuth.
	 *
	 * In order to use Odnoklassniki OAuth you must register your application
	 * at <http://www.odnoklassniki.ru/dk?st.cmd=appEdit&st._aid=Apps_Info_MyDev_AddApp>.
	 *
	 * Example application configuration:
	 *
	 * ~~~
	 * 'components' => [
	 *     'authClientCollection' => [
	 *         'class' => 'yii\authclient\Collection',
	 *         'clients' => [
	 *             'odnoklassniki' => [
	 *                  'class' => 'yii\authclient\clients\Odnoklassniki',
	 *                  'clientId' => 'odnoklassniki_app_id',
	 *                   clientPublic' => 'odnoklassniki_app_public_key',
	 *                  'clientSecret' => 'odnoklassniki_app_secret_key',
	 *             ],
	 *         ],
	 *     ]
	 *     ...
	 * ]
	 * ~~~
	 *
	 * @see    http://apiok.ru/wiki/pages/viewpage.action?pageId=42476652
	 * @see    http://www.odnoklassniki.ru/dk?st.cmd=appEdit&st._aid=Apps_Info_MyDev_AddApp
	 * @see    http://apiok.ru/wiki/pages/viewpage.action?pageId=81822109
	 * @see    http://apiok.ru/wiki/pages/viewpage.action?pageId=75989046
	 *
	 * @author Kazan1000 <kazan1000@gmail.com>
	 *
	 */
	class Odnoklassniki extends OAuth2 {
		/**
		 * @inheritdoc
		 */
		public $authUrl = 'http://www.odnoklassniki.ru/oauth/authorize?layout=m';
		/**
		 * @inheritdoc
		 */
		public $tokenUrl = 'https://api.odnoklassniki.ru/oauth/token.do';
		/**
		 * @inheritdoc
		 */
		public $apiBaseUrl = 'http://api.odnoklassniki.ru/fb.do';
		/**
		 * @var string OAuth client public key.
		 */
		public $clientPublic;
		/**
		 * @inheritdoc
		 */
		protected function initUserAttributes() {
			return $this->api('users.getCurrentUser', 'GET');
		}
		public function api($apiSubUrl, $method = 'GET', array $params = [ ], array $headers = [ ]) {
			$params['method'] = $apiSubUrl;
			return parent::api($this->apiBaseUrl, $method, $params, $headers);
		}
		/**
		 * @inheritdoc
		 */
		protected function apiInternal($accessToken, $url, $method, array $params, array $headers) {
			$params['format'] = 'json';
			$params['application_key'] = $this->clientPublic;
			//sign up params - http://apiok.ru/wiki/pages/viewpage.action?pageId=75989046
			ksort($params);
			$str = '';
			foreach ($params as $key => $value) {
				$str .= "$key=$value";
			}
			$token = $accessToken->getToken();
			$params['access_token'] = $token;
			$params['sig'] = md5($str . md5($token . $this->clientSecret));
			return $this->sendRequest($method, $url, $params, $headers);
		}
		/**
		 * @inheritdoc
		 */
		protected function defaultName() {
			return 'odnoklassniki';
		}
		/**
		 * @inheritdoc
		 */
		protected function defaultTitle() {
			return 'Odnoklassniki';
		}
		/**
		 * @inheritdoc
		 */
		protected function defaultNormalizeUserAttributeMap() {
			return [
				'id' => 'uid',
			];
		}
	}