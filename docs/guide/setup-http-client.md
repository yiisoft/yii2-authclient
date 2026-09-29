Setup HTTP Client
=================

This extension uses [yii2-httpclient](https://github.com/yiisoft/yii2-httpclient) for HTTP requests.
You may need to adjust default HTTP client configuration to be used, for example, in case you need to use
special request transport.

Each Auth client has a property `httpClient`, which can be used to setup HTTP client used by Auth client.
For example:

```php
use yii\authclient\Google;

$authClient = new Google([
    'httpClient' => [
        'transport' => 'yii\httpclient\CurlTransport',
    ],
]);
```

In case you are using [[\yii\authclient\Collection]] component, you can use its property `httpClient` to setup
HTTP client configuration to all internal Auth clients at once.
Application configuration example:

```php
return [
    'components' => [
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            // all Auth clients will use this configuration for HTTP client:
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

Custom response formats
-----------------------

If a provider returns a format the HTTP client cannot parse, configure its `afterSend` event to
convert that response before the auth client reads it. This replaces the old `processResponse()`
override used before the switch to `yii2-httpclient`.

For example, the JSONP response `callback({"openid":"123"});` can be handled in that provider's
`httpClient` configuration:

```php
'httpClient' => [
    'on afterSend' => static function (\yii\httpclient\RequestEvent $event) {
        $response = $event->response;
        if ($response === null) {
            return;
        }

        $content = $response->getContent();
        if (!empty($content) && preg_match('/\A\s*callback\s*\((.*)\)\s*;?\s*\z/s', $content, $matches)) {
            $response->setData(\yii\helpers\Json::decode($matches[1]));
        }
    },
],
```

This example accepts only the known `callback(...)` wrapper and decodes its contents as JSON;
it does not execute JavaScript. Ordinary JSON responses, including token responses, retain their
normal parsing. Adjust the wrapper to the provider's documented response format. Malformed JSON
still raises a parsing exception, and non-successful HTTP responses still raise the auth client's
response exception.
