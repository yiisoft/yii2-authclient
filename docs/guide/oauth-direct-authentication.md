OAuth 2.0 Direct Authentication
===============================

OAuth protocol of 2.0 version allows several additional work flows, which allows direct
authentication without visiting OAuth provider web site.

> Note: the authentication work flows, described in this section, usually are not supported by OAuth provider,
  because they are less secure then regular one. Make sure your provider does support particular work flow
  before attempt to use it.


## Resource Owner Password Credentials Grant

[Resource Owner Password Credentials Grant](https://tools.ietf.org/html/rfc6749#section-4.3) work flow allows direct
user authentication by username/password pair without redirect to OAuth provider web site.

You may authenticate user via this work flow using [[\yii\authclient\OAuth2::authenticateUser()]].
For example:

```php
$loginForm = new LoginForm();

if ($loginForm->load(Yii::$app->request->post()) && $loginForm->validate()) {
    /* @var $client \yii\authclient\OAuth2 */
    $client = Yii::$app->authClientCollection->getClient('someOAuth2');

    try {
        // direct authentication via username and password:
        $accessToken = $client->authenticateUser($loginForm->username, $loginForm->password);
    } catch (\Exception $e) {
        // authentication failed, use `$e->getMessage()` for details
    }
    // ...
}
```


## Client Credentials Grant

[Client Credentials Grant](https://tools.ietf.org/html/rfc6749#section-4.4) work flow authenticates only OAuth client
(your application) without any third party (actual user) being involved. It is used, if you need to access only
some general API, which is not related to the user.

You may authenticate client only via this work flow using [[\yii\authclient\OAuth2::authenticateClient()]].
For example:

```php
/* @var $client \yii\authclient\OAuth2 */
$client = Yii::$app->authClientCollection->getClient('someOAuth2');

// direct authentication of client only:
$accessToken = $client->authenticateClient();
```


## JSON Web Token (JWT)

JSON Web Token (JWT) work flow allows authentication of the particular account using [JSON Web Signature (JWS)](https://tools.ietf.org/html/rfc7515).
The following example allows authentication of [Google Service Account](https://developers.google.com/identity/protocols/OAuth2ServiceAccount):

```php
use yii\authclient\clients\Google;
use yii\authclient\signature\RsaSha;

$oauthClient = new Google();

$accessToken = $oauthClient->authenticateUserJwt(
    'your-service-account-id@developer.gserviceaccount.com',
    [
        'class' => RsaSha::className(),
        'algorithm' => OPENSSL_ALGO_SHA256,
        'privateCertificate' => "-----BEGIN PRIVATE KEY-----   ...   -----END PRIVATE KEY-----\n"
    ]
);
```
