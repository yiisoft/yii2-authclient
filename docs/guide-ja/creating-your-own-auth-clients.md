あなた自身の認証クライアントを作成する
======================================

どの外部認証プロバイダでも、あなた自身の認証クライアントを作成して、OpenID または 
OAuth プロトコルをサポートすることが出来ます。
そうするためには、最初に、外部認証プロバイダによってどのプロトコルがサポートされているかを見出す必要があります。
それによって、あなたのエクステンションの基底クラスの名前が決ります。

 - OAuth 2 のためには [[yii\authclient\OAuth2]] を使います。
 - OAuth 1/1.0a のためには [[yii\authclient\OAuth1]] を使います。
 - OpenID のためには [[yii\authclient\OpenId]] を使います。

この段階で、対応するメソッドを宣言することによって、認証クライアントのデフォルトの名前、タイトル、
および、ビュー・オプションを決定することが出来ます。

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

使用する基底クラスによって、宣言し直さなければならないフィールドやメソッドが異なります。

## [[yii\authclient\OpenId]]

必要なことは、[[yii\authclient\OpenId::authUrl|authUrl]] フィールドを宣言し直して URL を指定することだけです。
デフォルトの 必須属性 および/または オプション属性を設定することも可能です。
例えば、

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

以下のものを指定する必要があります。

- 認証 URL - [[yii\authclient\OAuth2::authUrl|authUrl]] フィールド。
- トークン・リクエスト URL - [[yii\authclient\OAuth2::tokenUrl|tokenUrl]] フィールド。
- API のベース URL - [[yii\authclient\OAuth2::apiBaseUrl|apiBaseUrl]] フィールド。
- ユーザ属性取得ストラテジー - [[yii\authclient\OAuth2::initUserAttributes()|initUserAttributes()]]
  メソッド。

例えば、

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

デフォルトの auth スコープを指定することも出来ます。

> Note: OAuth プロバイダの中には、OAuth の標準を厳格に遵守せず、標準と異なる仕様を導入しているものもあります。
  そのようなものに対してクライアントを実装するためには、追加の労力が必要になることがあります。

## [[yii\authclient\OAuth1]]

以下のものを指定する必要があります。

- 認証 URL - [[yii\authclient\OAuth1::authUrl|authUrl]] フィールド。
- リクエスト・トークン URL - [[yii\authclient\OAuth1::requestTokenUrl|requestTokenUrl]] フィールド。
- アクセス・トークン URL - [[yii\authclient\OAuth1::accessTokenUrl|accessTokenUrl]] フィールド。
- API のベース URL - [[yii\authclient\OAuth1::apiBaseUrl|apiBaseUrl]] フィールド。
- ユーザ属性取得ストラテジー - [[yii\authclient\OAuth1::initUserAttributes()|initUserAttributes()]]
  メソッド。

例えば、

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

デフォルトの auth スコープを指定することも出来ます。

> Note: OAuth プロバイダの中には、OAuth の標準を厳格に遵守せず、標準と異なる仕様を導入しているものもあります。
  そのようなものに対してクライアントを実装するためには、追加の労力が必要になることがあります。

