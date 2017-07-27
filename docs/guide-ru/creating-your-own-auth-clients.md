Создание собственных клиентов аутентификации
==============================

Вы можете создать собственный клиент для любого внешнего сервиса аутентификации, который поддерживает протокол OpenId 
или OAuth. Для этого, в первую очередь, необходимо выяснить, какой протокол поддерживает внешний сервис аутентификации, 
что даст Вам имя базового класса для расширения:

 - Для OAuth 2 используйте [[yii\authclient\OAuth2]].
 - Для OAuth 1/1.0a используйте [[yii\authclient\OAuth1]].
 - Для OpenID используйте [[yii\authclient\OpenId]].

На данном этапе можно установить для клиента аутентификации базовые значения имени, заголовка и параметров 
представления, переопределив соответствующие методы:

```php
use yii\authclient\OAuth2;

class MyAuthClient extends OAuth2
{
    protected function defaultName()
    {
        return 'my_auth_client';
    }

    protected function defaultTitle()
    {
        return 'My Auth Client';
    }

    protected function defaultViewOptions()
    {
        return [
            'popupWidth' => 800,
            'popupHeight' => 500,
        ];
    }
}
```

В зависимости от актуального базового класса, Вам нужно будет переопределить различные свойства и методы.

## [[yii\authclient\OpenId]]

Всё, что Вам нужно - это задать URL аутентификации, путём определения свойства 
[[yii\authclient\OpenId::authUrl|authUrl]].
Вы так же можете настроить обязательные и/или дополнительные атрибуты по умолчанию.
Например:

```php
use yii\authclient\OpenId;

class MyAuthClient extends OpenId
{
    public $authUrl = 'https://www.my.com/openid/';

    public $requiredAttributes = [
        'contact/email',
    ];

    public $optionalAttributes = [
        'namePerson/first',
        'namePerson/last',
    ];
}
```

## [[yii\authclient\OAuth2]]

Вам нужно будет указать:

- URL аутентификации путём определения свойства [[yii\authclient\OAuth2::authUrl|authUrl]].
- URL получения токена путём определения свойства [[yii\authclient\OAuth2::tokenUrl|tokenUrl]].
- Базовый URL к API путём определения свойства [[yii\authclient\OAuth2::apiBaseUrl|apiBaseUrl]].
- Стратегии извлечения пользовательских атрибутов путём определения метода 
[[yii\authclient\OAuth2::initUserAttributes()|initUserAttributes()]].

Например:

```php
use yii\authclient\OAuth2;

class MyAuthClient extends OAuth2
{
    public $authUrl = 'https://www.my.com/oauth2/auth';

    public $tokenUrl = 'https://www.my.com/oauth2/token';

    public $apiBaseUrl = 'https://www.my.com/apis/oauth2/v1';

    protected function initUserAttributes()
    {
        return $this->api('userinfo', 'GET');
    }
}
```

Вы так же можете указать области доступа аутентификации по умолчанию.

> Примечание: Некоторые  OAuth сервисы могут не следовать четким стандартам протокола OAuth, имея отличия, что может 
потребовать дополнительных усилий при реализации клиентов для таких сервисов.

## [[yii\authclient\OAuth1]]

Вам нужно будет указать:

- URL аутентификации путём определения свойства [[yii\authclient\OAuth1::authUrl|authUrl]].
- URL получения токена путём определения свойства [[yii\authclient\OAuth1::requestTokenUrl|requestTokenUrl]].
- URL получения токена доступа путём определения свойства [[yii\authclient\OAuth1::accessTokenUrl|accessTokenUrl]].
- Базовый URL к API путём определения свойства [[yii\authclient\OAuth1::apiBaseUrl|apiBaseUrl]].
- Стратегии извлечения пользовательских атрибутов путём определения метода 
[[yii\authclient\OAuth1::initUserAttributes()|initUserAttributes()]].

Например:

```php
use yii\authclient\OAuth1;

class MyAuthClient extends OAuth1
{
    public $authUrl = 'https://www.my.com/oauth/auth';

    public $requestTokenUrl = 'https://www.my.com/oauth/request_token';

    public $accessTokenUrl = 'https://www.my.com/oauth/access_token';

    public $apiBaseUrl = 'https://www.my.com/apis/oauth/v1';

    protected function initUserAttributes()
    {
        return $this->api('userinfo', 'GET');
    }
}
```

Вы так же можете указать области доступа аутентификации по умолчанию.

> Примечание: Некоторые  OAuth сервисы могут не следовать четким стандартам протокола OAuth, имея отличия, что может 
потребовать дополнительных усилий при реализации клиентов для таких сервисов.

