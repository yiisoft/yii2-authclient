创建你自己的验证客户端
======================

你可以为任意的外部验证服务商创建你自己的验证客户端，且支持 OpenId 或 OAuth 协议。
若要这么做，首先，你需要确认外部验证服务商支持哪些协议，以下为你的扩展提供的基类的名称：

 - 对于 OAuth 2 使用 [[yii\authclient\OAuth2]].
 - 对于 OAuth 1/1.0a 使用 [[yii\authclient\OAuth1]].
 - 对于 OpenID 使用 [[yii\authclient\OpenId]].

在此步骤中，你可以决定验证客户端的默认名称、标题和视图选项，声明相应的方法：

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

根据实际使用的基类，你需要重新声明不同的域和方法。

## [[yii\authclient\OpenId]]

你要做的所有工作就是通过重新声明 `authUrl` 域指定验证 URL。
你可能还需要设置默认的必需属性，以及可选属性。
例如：

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

你需要指定：

- 验证 URL（通过重新声明 `authUrl` 域）。
- 令牌请求 URL（通过重新声明 `tokenUrl` 域）。
- API 基础 URL（通过重新声明 `apiBaseUrl` 域）。
- User 属性取回策略（通过重新声明 `initUserAttributes()` 方法）。

例如：

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

你也可以指定默认的验证范围。

> 注意：某些 OAuth 提供商可能并不严格遵循 OAuth 标准，或没有清晰说明与 OAuth 标准的差别，
  可能需要为实现这些可客户端做额外的工作。

## [[yii\authclient\OAuth1]]

你需要指定：

- 验证 URL（通过重新声明 `authUrl` 域）。
- 请求令牌 URL（通过重新声明 `requestTokenUrl` 域）。
- 访问令牌 URL（通过重新声明 `accessTokenUrl` 域）。
- API 基础 URL（通过重新声明 `apiBaseUrl` 域）。
- User 属性取回策略（通过重新声明 `initUserAttributes()` 方法）。

例如：

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

你也可以指定默认的验证范围。

> 注意：某些 OAuth 提供商可能并不严格遵循 OAuth 标准，或没有清晰说明与 OAuth 标准的差别，
  可能需要为实现这些可客户端做额外的工作。

