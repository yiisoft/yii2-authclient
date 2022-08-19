<?php
/**
 * yii2 mailru authclient
 */

namespace yii\authclient\clients;


use yii\authclient\OAuth2;

/**
 * In order to use Mail.ru OAuth you must register your application at <https://oauth.mail.ru/app/>.
 *
 */
class MailRu extends OAuth2{

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
   public $apiBaseUrl = 'http://www.appsmail.ru/platform/api?method=';


    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
        return $this->api('info', 'GET');
    }

    /**
     * {@inheritdoc}
     */
    public function applyAccessTokenToRequest($request, $accessToken)
    {
        $data = $request->getData();
        if (!isset($data['format'])) {
            $data['format'] = 'json';
        }
        $data['oauth_token'] = $accessToken->getToken();
        $request->setData($data);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return 'mailru';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'MailRu';
    }
}