OAuth 2.0 直接認証
==================

OAuth プロトコル 2.0 版では、追加のワークフローがいくつか利用可能であり、OAuth プロバイダのウェブサイトを訪問する必要のない、直接認証が可能になっています。

> Note: この節で説明されている認証ワークフローは、通常は、OAuth プロバイダによってサポートされていません。
  なぜなら、通常の認証ワークフローより安全性が低いからです。
  これらのワークフローを利用しようと試みる前に、あなたのプロバイダがそれをサポートしているかどうか、確認して下さい。


## リソース・オーナー・パスワード・クレデンシャル・グラント

[リソース・オーナー・パスワード・クレデンシャル・グラント](https://tools.ietf.org/html/rfc6749#section-4.3) のワークフローは、
OAuth プロバイダのウェブサイトにリダイレクトすることなく、ユーザ名/パスワードのペアによる直接のユーザ認証を可能にするものです。
([4.3.  リソースオーナーパスワードクレデンシャルグラント](http://openid-foundation-japan.github.io/rfc6749.ja.html#grant-password) を参照)

[[\yii\authclient\OAuth2::authenticateUser()]] を使うと、このワークフローによってユーザを認証することが出来ます。
例えば、

```php
$loginForm = new LoginForm();

if ($loginForm->load(Yii::$app->request->post()) && $loginForm->validate()) {
    /* @var $client \yii\authclient\OAuth2 */
    $client = Yii::$app->authClientCollection->getClient('someOAuth2');

    try {
        // ユーザ名とパスワードによる直接認証
        $accessToken = $client->authenticateUser($loginForm->username, $loginForm->password);
    } catch (\Exception $e) {
        // 認証の失敗。詳細は `$e->getMessage()` で取得
    }
    // ...
}
```


## クライアント・クレデンシャル・グラント

[クライアント・クレデンシャル・グラント](https://tools.ietf.org/html/rfc6749#section-4.4) ワークフローは、OAuth クライアント (あなたのアプリケーション) のみを、そのサードパーティ (実際のユーザ) とは無関係に認証するものです。
ユーザには関係のない、何らかの一般的な API にだけアクセス出来れば良いという場合に使います。

[[\yii\authclient\OAuth2::authenticateClient()]] を使うと、このワークフローによってクライアントだけを認証することが出来ます。
例えば、

```php
/* @var $client \yii\authclient\OAuth2 */
$client = Yii::$app->authClientCollection->getClient('someOAuth2');

// クライアントだけの直接認証
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
