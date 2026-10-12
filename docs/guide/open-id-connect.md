OpenID Connect
==============

This extension provides support for [OpenId Connect](https://openid.net/connect/) authentication protocol via
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

**Heads up!** 'OpenID Connect' protocol uses [JWS](https://tools.ietf.org/html/draft-ietf-jose-json-web-signature) verification
for the authentication process securing. You will need to install `web-token/jwt-checker`, `web-token/jwt-key-mgmt`, `web-token/jwt-signature`, `web-token/jwt-signature-algorithm-hmac`, `web-token/jwt-signature-algorithm-ecdsa` and `web-token/jwt-signature-algorithm-rsa` libraries in order to use such verification. These libraries are not required by this extension by default. It can be done via composer:

```
composer require --prefer-dist "web-token/jwt-checker:>=1.0 <3.0" "web-token/jwt-key-mgmt:>=1.0 <3.0" "web-token/jwt-signature:>=1.0 <3.0" "web-token/jwt-signature-algorithm-hmac:>=1.0 <3.0" "web-token/jwt-signature-algorithm-ecdsa:>=1.0 <3.0" "web-token/jwt-signature-algorithm-rsa:>=1.0 <3.0"
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

Reading group claims
--------------------

Custom claims returned by the provider are available through `getUserAttributes()`. Group claim
names depend on the provider; map the configured source claim to a common name with
[[\yii\authclient\BaseClient::$normalizeUserAttributeMap]]:

```php
'normalizeUserAttributeMap' => [
    'groups' => 'roles', // Use the claim name returned by your provider.
],
```

For a nested claim, use a path such as `'groups' => ['realm_access', 'roles']`. In your authentication
success callback, read `$client->getUserAttributes()['groups'] ?? []` and apply your application's
own membership or permission rules. Mapping does not request additional claims or assign Yii RBAC
roles. Configure the provider and any required scopes to return the claim first.

When discovery specifies a `userinfo_endpoint`, this client reads attributes from that endpoint.
Otherwise, it extracts non-metadata claims from the ID token. If your provider supplies groups only
in the ID token but also exposes UserInfo, configure it to return the group claim in UserInfo;
claims from those two sources are not automatically merged.

RP-Initiated Logout
-------------------

Since version 2.2.19, [[\yii\authclient\OpenIdConnect::buildLogoutUrl()]] supports
[OpenID Connect RP-Initiated Logout](https://openid.net/specs/openid-connect-rpinitiated-1_0.html).
It builds a URL for redirecting the user's browser to the identity provider to request logout.
The provider must support this specification; OIDC login support alone does not imply logout support.

The client reads `end_session_endpoint` from the provider configuration. You can set `logoutUrl`
explicitly if the provider does not advertise its endpoint. A null or empty `logoutUrl` uses discovery.
If neither endpoint is available, the method throws `InvalidConfigException`. On a cold configuration
cache, discovery makes a blocking HTTP request and can throw `InvalidResponseException` or
`\yii\httpclient\Exception`. Handle failures so they do not prevent local logout.

For example, in a controller logout action restricted to POST by `VerbFilter`, with Yii's CSRF
validation enabled:

```php
/** @var \yii\authclient\OpenIdConnect $client */
$client = Yii::$app->authClientCollection->getClient('my-oidc-provider');

$logoutUrl = null;
try {
    // Build this before logout destroys the session containing the provider's ID token.
    $logoutUrl = $client->buildLogoutUrl([
        'post_logout_redirect_uri' => \yii\helpers\Url::to(['/site/logged-out'], true),
    ]);
} catch (\Exception $e) {
    Yii::warning('Unable to initiate provider logout: ' . get_class($e), __METHOD__);
}

$client->setAccessToken(null);
Yii::$app->user->logout();

if ($logoutUrl === null) {
    Yii::$app->session->setFlash(
        'warning',
        'You are logged out of this application, but provider logout could not be completed.'
    );
    return $this->redirect(['/site/logged-out']);
}

return $this->redirect($logoutUrl);
```

Use the client that authenticated the current user. Register the exact `post_logout_redirect_uri`
with that provider and make the destination accessible to guests. It is separate from the login
callback (`returnUrl`) and is not added automatically. If you omit it, the provider controls where
the browser ends up.

The method includes `client_id` and, when available, the stored ID token as `id_token_hint`.
It does not refresh expired access tokens. You can supply an ID token explicitly through
`id_token_hint`, or set a default parameter to `null` to omit it. Providers may ask for logout
confirmation, especially when no ID token is supplied. Other parameters, such as `logout_hint`
and `ui_locales`, can also be passed in the array. Empty client IDs and empty stored ID tokens
are omitted from the defaults.

This helper uses GET, so the ID token may appear in browser history or request logs, and large
tokens may exceed URL length limits. Pass `'id_token_hint' => null` to omit it when your provider
supports logout without that hint.

The optional `state` parameter is passed through without generation, storage or validation.
If your callback needs to correlate a logout request, generate an unpredictable value, retain it
across local logout, then compare and consume it in the callback. Do not rely on state stored in
a session that `Yii::$app->user->logout()` destroys. A public, informational logged-out page does
not need to perform session-changing work on return.

`buildLogoutUrl()` only builds the URL. Your application must end its local session even if the
user never returns from the provider. It does not revoke tokens or implement front-channel or
back-channel logout notifications for other applications.
