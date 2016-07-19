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

[クライアント・クレデンシャル・グラント](https://tools.ietf.org/html/rfc6749#section-4.4) ワークフローは、work flow authenticates only OAuth client
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
