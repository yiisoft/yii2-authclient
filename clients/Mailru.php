<?php
	namespace kroshilin\authclient\clients;
	use yii\authclient\OAuth2;
	/**
	 * Mailru allows authentication via Mail.ru OAuth.
	 *
	 * In order to use Mail.ru OAuth you must register your application
	 * at <http://api.mail.ru/sites/my/add>.
	 *
	 * Example application configuration:
	 *
	 * ~~~
	 * 'components' => [
	 *     'authClientCollection' => [
	 *         'class' => 'yii\authclient\Collection',
	 *         'clients' => [
	 *             'mailru' => [
	 *                  'class' => 'yii\authclient\clients\Mailru',
	 *                  'clientId' => 'mailru_app_id',
	 *                  'clientSecret' => 'mailru_app_secret_key',
	 *             ],
	 *         ],
	 *     ]
	 *     ...
	 * ]
	 * ~~~
	 *
	 * @see    http://api.mail.ru/sites/my/add
	 * @see    http://api.mail.ru/docs/guides/oauth/sites/
	 * @see    http://api.mail.ru/docs/reference/js/users.getInfo/
	 *
	 * @author Kazan1000 <kazan1000@gmail.com>
	 *
	 */
	class Mailru extends OAuth2 {
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
		public $apiBaseUrl = 'http://www.appsmail.ru/platform/api';
		/**
		 * @inheritdoc
		 */
		protected function initUserAttributes() {
			return $this->api('users.getInfo', 'GET');
		}
		public function api($apiSubUrl, $method = 'GET', array $params = [ ], array $headers = [ ]) {
			$params['method'] = $apiSubUrl;
			return parent::api($this->apiBaseUrl, $method, $params, $headers);
		}
		protected function determineContentTypeByRaw($rawContent) {
			//determine json array's too
			if (preg_match('/^\\[.*\\]$/is', $rawContent)) {
				return self::CONTENT_TYPE_JSON;
			}
			return parent::determineContentTypeByRaw($rawContent);
		}
		/**
		 * @inheritdoc
		 */
		protected function apiInternal($accessToken, $url, $method, array $params, array $headers) {
			$params['format'] = 'json';
			$params['secure'] = 1;
			$params['app_id'] = $this->clientId;
			$token = $accessToken->getToken();
			$params['session_key'] = $token;
			//sign up params - http://api.mail.ru/docs/guides/restapi/#server
			ksort($params);
			$str = '';
			foreach ($params as $key => $value) {
				$str .= "$key=$value";
			}
			$params['sig'] = md5($str . $this->clientSecret);
			return $this->sendRequest($method, $url, $params, $headers);
		}
		/**
		 * @inheritdoc
		 */
		protected function defaultName() {
			return 'mailru';
		}
		/**
		 * @inheritdoc
		 */
		protected function defaultTitle() {
			return 'Mailru';
		}
		/**
		 * @inheritdoc
		 */
		protected function defaultNormalizeUserAttributeMap() {
			return [
				'id' => [
					0,
					'uid'
				],
			];
		}
	}