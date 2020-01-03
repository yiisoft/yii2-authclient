OpenID Connect
==============

This extension provides support for [OpenId Connect](http://openid.net/connect/) authentication protocol via
[[\yii\authclient\OpenIdConnect]] class.

Application configuration example:

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
                'title' => 'Google OpenID Connect',
            ],
        ],
    ]
    // ...
]
```

Authentication workflow is exactly the same as for OAuth2.

**Heads up!** 'OpenID Connect' protocol uses [JWS](http://tools.ietf.org/html/draft-ietf-jose-json-web-signature) verification
for the authentication process securing. You will need to install `web-token/jwt-checker`, `web-token/jwt-key-mgmt`, `web-token/jwt-signature`, `web-token/jwt-signature-algorithm-hmac`, `web-token/jwt-signature-algorithm-ecdsa` and `web-token/jwt-signature-algorithm-rsa` libraries in order to use such verification. These libraries are not required by this extension by default. It can be done via composer:

```
composer require --prefer-dist "web-token/jwt-checker:>=1.0 <3.0" "web-token/jwt-signature:>=1.0 <3.0" "web-token/jwt-signature-algorithm-hmac:>=1.0 <3.0" "web-token/jwt-signature-algorithm-ecdsa:>=1.0 <3.0" "web-token/jwt-signature-algorithm-rsa:>=1.0 <3.0"
```

or add

```json
"web-token/jwt-checker": ">=1.0 <3.0",
"web-token/jwt-key-mgmt": ">=1.0  <3.0",
"web-token/jwt-signature": "~1.0 <3.0",
"web-token/jwt-signature-algorithm-hmac": "~1.0  <3.0",
"web-token/jwt-signature-algorithm-ecdsa": "~1.0  <3.0",
"web-token/jwt-signature-algorithm-rsa": "~1.0  <3.0"
```

to the `require` section of your composer.json.

> Note: if you are using well-trusted 'OpenID Connect' provider, you may disable [[\yii\authclient\OpenIdConnect::$validateJws]],
  making installation of `web-token` library redundant, however it is not recommended as it violates the protocol specification.
