Authentication configuration and recovery
=======================================

## Using one authentication provider

To send guests directly to a provider, point the application's `user.loginUrl` at the auth action:

```php
'components' => [
    'user' => [
        'identityClass' => 'app\models\User',
        'loginUrl' => ['/site/auth', 'authclient' => 'google'],
    ],
],
```

Use the client ID from `authClientCollection` in place of `google`. Keep the auth action accessible
to guests. When the application calls `Yii::$app->user->loginRequired()`, it will start this flow
without displaying a provider-choice page. Existing links to a separate login page must also be
updated if you want them to start this flow.

Since 2.2.12, you can alternatively configure `defaultClientId` on the `AuthAction` and use
`'loginUrl' => ['/site/auth']`. This also selects the client when the callback omits `authclient`.
An explicitly supplied client ID still takes precedence. The action does not automatically detect
whether the collection contains only one client.

## Session cookies on authentication callbacks

By default, [[yii\authclient\SessionStateStorage]] stores authentication state in the application
session. The browser must send the same session cookie when it returns from the provider.
`SameSite=Strict` prevents the cookie from being sent on a cross-site callback, which can result in
an invalid authentication state or lost session data.

For providers that return through a top-level GET redirect, configure the session cookie with
`SameSite=Lax`:

```php
'components' => [
    'session' => [
        'cookieParams' => [
            'httpOnly' => true,
            'secure' => true, // The application must use HTTPS.
            'sameSite' => \yii\web\Cookie::SAME_SITE_LAX,
        ],
    ],
],
```

For a custom callback that handles cross-site POST responses, such as OpenID Connect `form_post`,
use `SameSite=None` and `Secure` for this session cookie. `Lax` does not cover cross-site POST requests.
The built-in `AuthAction` reads the authorization code from the query string.
See [SameSite cookie behavior](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Set-Cookie#samesitesamesite-value).
Keep OAuth2 `validateAuthState` enabled; disabling state validation does not repair session continuity.


## Handling an expired refresh token

An expired or revoked refresh token cannot obtain a new access token. The provider's HTTP 4xx
response raises [[\yii\authclient\ClientErrorResponseException]], a subclass of
[[\yii\authclient\InvalidResponseException]]. Handle reauthentication in your application.
With automatic refresh enabled, the exception can arise from `getAccessToken()` or an API call.

For example, in a controller action using an OAuth2 provider that reports `invalid_grant`:

```php
/** @var \yii\authclient\OAuth2 $client */
$client = Yii::$app->authClientCollection->getClient('google');

try {
    $attributes = $client->api('userinfo', 'GET');
} catch (\yii\authclient\ClientErrorResponseException $e) {
    $error = $e->response->getData();
    if (!is_array($error) || ($error['error'] ?? null) !== 'invalid_grant') {
        throw $e;
    }

    $client->setAccessToken(null); // Clear the unusable token from the client and its state storage.
    return $this->redirect(['/site/auth', 'authclient' => $client->getId()]);
}
```

Adapt the error check to your provider's documented response. `invalid_grant` can also mean a
revoked or otherwise invalid grant, not just an expired refresh token. Do not treat every HTTP
error as expiration. Clearing the token starts a new authorization flow; it does not log the user
out of your application or the provider.
