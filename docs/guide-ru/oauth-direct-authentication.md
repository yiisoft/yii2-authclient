Непосредственная аутентификация по OAuth 2.0
============================================

Протокол OAuth версии 2.0 предоставляет несколько дополнительных процессов, которые позволяет производить
аутентификацию без посещения веб сайта OAuth провайдера.

> Замечание: процессы, описанные в этом разделе, обычно не поддерживаються OAuth провайдерами, т.к. они менне
  защищенные чем стандартный. Убедитесь, что ваш OAuth провайдер поддерживает нужный процесс, прежде чем
  пыттаться им воспользоваться.


## Resource Owner Password Credentials Grant

[Resource Owner Password Credentials Grant](https://tools.ietf.org/html/rfc6749#section-4.3) позволяет аутентифицировать
пользователя напрямую используя пару "имя пользователя / пароль" без перенаправления на сайт OAuth провайдера.

Вы можете аутентифицировать пользователя по этому процессу, используя [[\yii\authclient\OAuth2::authenticateUser()]].
Например:

```php
$loginForm = new LoginForm();

if ($loginForm->load(Yii::$app->request->post()) && $loginForm->validate()) {
    /* @var $client \yii\authclient\OAuth2 */
    $client = Yii::$app->authClientCollection->getClient('someOAuth2');

    try {
        // аутентификация напрямую через имя пользователя и пароль:
        $accessToken = $client->authenticateUser($loginForm->username, $loginForm->password);
    } catch (\Exception $e) {
        // аутентификация завершилась неудачей, используйте `$e->getMessage()` для полной информации
    }
    // ...
}
```


## Client Credentials Grant

[Client Credentials Grant](https://tools.ietf.org/html/rfc6749#section-4.4) позволяет аутентифицировать исключительно
OAuth клиента (ваше приложение) без задействования третьей стороны (пользователя). Этот процесс используется, если
вам нужно использовать только какое-то API общего назначения, которое не требует участия пользователя.

Вы можете аутентифицировать исключительно клиента, используя [[\yii\authclient\OAuth2::authenticateClient()]].
Например:

```php
/* @var $client \yii\authclient\OAuth2 */
$client = Yii::$app->authClientCollection->getClient('someOAuth2');

// аутентификация исключительно клиета напрямую:
$accessToken = $client->authenticateClient();
```
