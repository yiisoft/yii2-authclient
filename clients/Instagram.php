<?php

	namespace yii\authclient\clients;

	use yii\authclient\OAuth2;

	/**
	 * Instagram allows authentication via Instagram OAuth 2.
	 *
	 * In order to use Instagram you must register your app at <http://instagram.com/developer/register/>
	 *
	 * Example application configuration:
	 *
	 * ~~~
	 * 'components' => [
	 *     'authClientCollection' => [
	 *         'class' => 'yii\authclient\Collection',
	 *         'clients' => [
	 *             'instagram' => [
	 *                 'class' => 'yii\authclient\clients\Instagram',
	 *                 'clientId' => 'instagram_client_id',
	 *                 'clientSecret' => 'instagram_client_secret',
	 *             ],
	 *         ],
	 *     ]
	 *     ...
	 * ]
	 * ~~~
	 *
	 * @see        http://instagram.com/developer/authentication/
	 * @see        http://instagram.com/developer/endpoints/users/#get_users
	 *
	 * @author     Kazan1000 <kazan1000@gmail.com>
	 *
	 */
	class Instagram extends OAuth2 {
		/**
		 * @inheritdoc
		 */
		public $authUrl = 'https://api.instagram.com/oauth/authorize/';
		/**
		 * @inheritdoc
		 */
		public $tokenUrl = 'https://api.instagram.com/oauth/access_token';
		/**
		 * @inheritdoc
		 */
		public $apiBaseUrl = 'https://api.instagram.com/v1';

		/**
		 * @inheritdoc
		 */
		protected function initUserAttributes() {
			return $this->api('users/self', 'GET');
		}

		/**
		 * @inheritdoc
		 */
		protected function defaultName() {
			return 'instagram';
		}

		/**
		 * @inheritdoc
		 */
		protected function defaultTitle() {
			return 'Instagram';
		}

		/**
		 * @inheritdoc
		 */
		protected function defaultNormalizeUserAttributeMap() {
			return [
				'id' => [
					'data',
					'id'
				],
			];
		}

	}
