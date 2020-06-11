OpenID 接続
===========

このエクステンションは、[[\yii\authclient\OpenIdConnect]] クラスを通じて、
[OpenId 接続](http://openid.net/connect/) 認証プロトコルのサポートを提供します。

アプリケーション設定の例:

```php
'components' => [
    'authClientCollection' => [
        'class' => 'yii\authclient\Collection',
        'clients' => [
            'google' => [
                'class' => 'yii\authclient\OpenIdConnect',
                'issuerUrl' => 'https://accounts.google.com',
                'clientId' => 'google_client_id',
                'clientSecret' => 'google_client_secret',
                'name' => 'google',
                'title' => 'Google OpenID 接続',
            ],
        ],
    ]
    // ...
]
```

認証のワークフローは、OAuth2 の場合と全く同じです。

**注意!** 'OpenID 接続' プロトコルは、認証のプロセスをセキュアにするために、 [JWS](http://tools.ietf.org/html/draft-ietf-jose-json-web-signature) 検証を使います。
そのような検証を使うためには。このエクステンションがデフォルトでは要求していない
`web-token/jwt-checker`, `web-token/jwt-key-mgmt`, `web-token/jwt-signature`, `web-token/jwt-signature-algorithm-hmac`, `web-token/jwt-signature-algorithm-ecdsa`, `web-token/jwt-signature-algorithm-rsa` ライブラリをインストールする必要があります。

```
composer require --prefer-dist "web-token/jwt-checker:>=1.0 <3.0" "web-token/jwt-signature:>=1.0 <3.0" "web-token/jwt-signature-algorithm-hmac:>=1.0 <3.0" "web-token/jwt-signature-algorithm-ecdsa:>=1.0 <3.0" "web-token/jwt-signature-algorithm-rsa:>=1.0 <3.0"
```

または、

```json
"web-token/jwt-checker": ">=1.0 <3.0",
"web-token/jwt-key-mgmt": ">=1.0  <3.0",
"web-token/jwt-signature": "~1.0 <3.0",
"web-token/jwt-signature-algorithm-hmac": "~1.0  <3.0",
"web-token/jwt-signature-algorithm-ecdsa": "~1.0  <3.0",
"web-token/jwt-signature-algorithm-rsa": "~1.0  <3.0"
```

をあなたの composer.joson の`require` セクションに追加します。

> Note: あなたが十分に信頼された 'OpenID 接続' プロバイダを使おうとする場合は、[[\yii\authclient\OpenIdConnect::$validateJws]] を無効化し、`web-token` ライブラリのインストールを冗長なものとして割愛できます。
ただし、プロトコルの仕様に反することですので、お奨めは出来ません。
