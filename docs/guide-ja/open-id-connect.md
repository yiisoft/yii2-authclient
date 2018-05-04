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
`spomky-labs/jose` ライブラリをインストールする必要があります。

```
composer require --prefer-dist "spomky-labs/jose:~5.0.6"
```

または、

```json
"spomky-labs/jose": "~5.0.6"
```

をあなたの composer.joson の`require` セクションに追加します。

> Note: あなたが十分に信頼された 'OpenID 接続' プロバイダを使おうとする場合は、[[\yii\authclient\OpenIdConnect::$validateJws]] を無効化し、`spomky-labs/jose` ライブラリのインストールを冗長なものとして割愛できます。
ただし、プロトコルの仕様に反することですので、お奨めは出来ません。
