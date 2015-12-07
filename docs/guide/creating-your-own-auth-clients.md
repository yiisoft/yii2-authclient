Creating your own auth clients
==============================

You may create your own auth client for any external auth provider, which supports
OpenId or OAuth protocol. To do so, first of all, you need to find out which protocol is
supported by the external auth provider, this will give you the name of the base class
for your extension:

 - For OAuth 2 use [[yii\authclient\OAuth2]].
 - For OAuth 1/1.0a use [[yii\authclient\OAuth1]].
 - For OpenID use [[yii\authclient\OpenId]].

At this stage you can determine auth client default name, title and view options, declaring
corresponding methods:

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

Depending on actual base class, you will need to redeclare different fields and methods.

## [[yii\authclient\OpenId]]

All you need is to specify auth URL, by redeclaring [[yii\authclient\OpenId::authUrl|authUrl]] field.
You may also setup default required and/or optional attributes.
For example:

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

You will need to specify:

- Auth URL by redeclaring [[yii\authclient\OAuth2::authUrl|authUrl]] field.
- Token request URL by redeclaring [[yii\authclient\OAuth2::tokenUrl|tokenUrl]] field.
- API base URL by redeclaring [[yii\authclient\OAuth2::apiBaseUrl|apiBaseUrl]] field.
- User attribute fetching strategy by redeclaring [[yii\authclient\OAuth2::initUserAttributes()|initUserAttributes()]] 
method.

For example:

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

You may also specify default auth scopes.

> Note: Some OAuth providers may not follow OAuth standards clearly, introducing
  differences, and may require additional efforts to implement clients for.

## [[yii\authclient\OAuth1]]

You will need to specify:

- Auth URL by redeclaring [[yii\authclient\OAuth1::authUrl|authUrl]] field.
- Request token URL by redeclaring [[yii\authclient\OAuth1::requestTokenUrl|requestTokenUrl]] field.
- Access token URL by redeclaring [[yii\authclient\OAuth1::accessTokenUrl|accessTokenUrl]] field.
- API base URL by redeclaring [[yii\authclient\OAuth1::apiBaseUrl|apiBaseUrl]] field.
- User attribute fetching strategy by redeclaring [[yii\authclient\OAuth1::initUserAttributes()|initUserAttributes()]] 
method.

For example:

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

You may also specify default auth scopes.

> Note: Some OAuth providers may not follow OAuth standards clearly, introducing
  differences, and may require additional efforts to implement clients for.

