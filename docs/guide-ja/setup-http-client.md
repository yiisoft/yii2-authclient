HTTP クライアントをセットアップする
===================================

このエクステンションは、HTTP リクエストのために [yii2-httpclient](https://github.com/yiisoft/yii2-httpclient) を使用します。
例えば、特別なリクエスト伝送方法を使う必要があるなどに、
使用される HTTP クライアントのデフォルト構成を修正する必要があるでしょう。

各認証クライアントは `httpClient` というプロパティを持っており、これを通じて、認証クライアントによって使用される HTTP クライアントを設定することが出来ます。
例えば、

```php
use yii\authclient\Google;

$authClient = new Google([
    'httpClient' => [
        'transport' => 'yii\httpclient\CurlTransport',
    ],
]);
```

[[\yii\authclient\Collection]] コンポーネントを使うのであれば、その中の全ての認証クライアントに対して、
`httpClient` プロパティを使って HTTP クライアントの構成を一度にまとめてセットアップすることが出来ます。
アプリケーション構成の例を示します。

```php
return [
    'components' => [
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            // 全ての認証クライアントは HTTP クライアントにこの構成を使用する
            'httpClient' => [
                'transport' => 'yii\httpclient\CurlTransport',
            ],
            'clients' => [
                'google' => [
                    'class' => 'yii\authclient\clients\Google',
                    'clientId' => 'google_client_id',
                    'clientSecret' => 'google_client_secret',
                ],
                'facebook' => [
                    'class' => 'yii\authclient\clients\Facebook',
                    'clientId' => 'facebook_client_id',
                    'clientSecret' => 'facebook_client_secret',
                ],
                // etc.
            ],
        ]
        //...
    ],
    // ...
];
```
