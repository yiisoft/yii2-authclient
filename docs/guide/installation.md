Installation
============

## Installing an extension

In order to install extension use Composer. Either run
                                            
```
composer require --prefer-dist yiisoft/yii2-authclient "*"
```

or add

```json
"yiisoft/yii2-authclient": "*"
```
to the `require` section of your composer.json.

## Configuring application

After extension is installed you need to setup auth client collection application component:

```php
'components' => [
    'authClientCollection' => [
        'class' => 'yii\authclient\Collection',
        'clients' => [
            'google' => [
                'class' => 'yii\authclient\clients\GoogleOpenId'
            ],
            'facebook' => [
                'class' => 'yii\authclient\clients\Facebook',
                'clientId' => 'facebook_client_id',
                'clientSecret' => 'facebook_client_secret',
            ],
            // etc.
        ],
    ]
    ...
]
```

Out of the box the following clients are provided:

- [[\yii\authclient\clients\Facebook|Facebook]].
- [[yii\authclient\clients\GitHub|GitHub]].
- Google (via [[yii\authclient\clients\GoogleOpenId|OpenID]] and [[yii\authclient\clients\GoogleOAuth|OAuth]]).
- [[yii\authclient\clients\LinkedIn|LinkedIn]].
- [[yii\authclient\clients\Live|Microsoft Live]].
- [[yii\authclient\clients\Twitter|Twitter]].
- [[yii\authclient\clients\VKontakte|VKontakte]].
- Yandex (via [[yii\authclient\clients\YandexOpenId|OpenID]] and [[yii\authclient\clients\YandexOAuth|OAuth]]).

Configuration for each client is a bit different. For OAuth it's required to get client ID and secret key from
the service you're going to use. For OpenID it works out of the box in most cases.

## Storing authorization data

In order to recognize the user authenticated via external service we need to store ID provided on first authentication
and then check against it on subsequent authentications. It's not a good idea to limit login options to external
services only since these may fail and there won't be a way for the user to log in. Instead it's better to provide
both external authentication and good old login and password.

If we're storing user information in a database the schema could be the following:

```sql
CREATE TABLE user (
    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username varchar(255) NOT NULL,
    auth_key varchar(32) NOT NULL,
    password_hash varchar(255) NOT NULL,
    password_reset_token varchar(255),
    email varchar(255) NOT NULL,
    status smallint(6) NOT NULL DEFAULT 10,
    created_at int(11) NOT NULL,
    updated_at int(11) NOT NULL
);

CREATE TABLE auth (
    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id int(11) NOT NULL,
    source string(255) NOT NULL,
    source_id string(255) NOT NULL
);

ALTER TABLE auth ADD CONSTRAINT fk-auth-user_id-user-id
FOREIGN KEY user_id REFERENCES user(id);
```

In the SQL above `user` is a standard table that is used in advanced application template to store user
info. Each user can authenticate using multiple external services therefore each `user` record can relate to
multiple `auth` records. In the `auth` table `source` is the name of the auth provider used and `source_id` is
unique user identificator that is provided by external service after successful login.

Using tables created above we can generate `Auth` model. No further adjustments needed.

## Adding action to controller

Next step is to add [[yii\authclient\AuthAction]] to a web controller. Typically `SiteController`:

```php
class SiteController extends Controller
{
    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    public function onAuthSuccess($client)
    {
       $attributes = $client->getUserAttributes();

        /** @var Auth $auth */
        $auth = Auth::find()->where([
            'source' => $client->getId(),
            'source_id' => $attributes['id'],
        ])->one();
        
        if (Yii::$app->user->isGuest) {
            if ($auth) { // login
                $user = $auth->user;
                Yii::$app->user->login($user);
            } else { // signup
                if (isset($attributes['email']) && isset($attributes['username']) && User::find()->where(['email' => $attributes['email']])->exists()) {
                    Yii::$app->getSession()->setFlash('error', [
                        Yii::t('app', "User with the same email as in {client} account already exists but isn't linked to it. Login using email first to link it.", ['client' => $client->getTitle()]),
                    ]);
                } else {
                    $password = Yii::$app->security->generateRandomString(6);
                    $user = new User([
                        'username' => $attributes['login'],
                        'email' => $attributes['email'],
                        'password' => $password,
                    ]);
                    $user->generateAuthKey();
                    $user->generatePasswordResetToken();
                    $transaction = $user->getDb()->beginTransaction();
                    if ($user->save()) {
                        $auth = new Auth([
                            'user_id' => $user->id,
                            'source' => $client->getId(),
                            'source_id' => (string)$attributes['id'],
                        ]);
                        if ($auth->save()) {
                            $transaction->commit();
                            Yii::$app->user->login($user);
                        } else {
                            print_r($auth->getErrors());
                        }
                    } else {
                        print_r($user->getErrors());
                    }
                }
            }
        } else { // user already logged in
            if (!$auth) { // add auth provider
                $auth = new Auth([
                    'user_id' => Yii::$app->user->id,
                    'source' => $client->getId(),
                    'source_id' => $attributes['id'],
                ]);
                $auth->save();
            }
        }
    }
}
```

`successCallback` method is called when user was successfully authenticated via external service. Via `$client` instance
we can retrieve information received. In our case we'd like to:
 
- If user is guest and record found in auth then log this user in.
- If user is guest and record not found in auth then create new user and make a record in auth table. Then log in.
- If user is logged in and record not found in auth then try connecting additional account (save its data into auth table).

Although, all clients are different they shares same basic interface [[yii\authclient\ClientInterface]],
which governs common API.

Each client has some descriptive data, which can be used for different purposes:

- `id` - unique client id, which separates it from other clients, it could be used in URLs, logs etc.
- `name` - external auth provider name, which this client is match too. Different auth clients
  can share the same name, if they refer to the same external auth provider.
  For example: clients for Google OpenID and Google OAuth have same name "google".
  This attribute can be used inside the database, CSS styles and so on.
- `title` - user friendly name for the external auth provider, it is used to present auth client
  at the view layer.

Each auth client has different auth flow, but all of them supports `getUserAttributes()` method,
which can be invoked if authentication was successful.

This method allows you to get information about external user account, such as ID, email address,
full name, preferred language etc. Note that for each provider fields available may vary in both existence and
names.

Defining list of attributes, which external auth provider should return, depends on client type:

- [[yii\authclient\OpenId]]: combination of `requiredAttributes` and `optionalAttributes`.
- [[yii\authclient\OAuth1]] and [[yii\authclient\OAuth2]]: field `scope`, note that different
  providers use different formats for the scope.
  
### Getting additional data via extra API calls

Both [[yii\authclient\OAuth1]] and [[yii\authclient\OAuth2]] provide method `api()`, which
can be used to access external auth provider REST API. However this method is very basic and
it may be not enough to access full external API functionality. This method is mainly used to
fetch the external user account data.

To use API calls, you need to setup [[yii\authclient\BaseOAuth::apiBaseUrl]] according to the
API specification. Then you can call [[yii\authclient\BaseOAuth::api()]] method:

```php
use yii\authclient\OAuth2;

$client = new OAuth2;

// ...

$client->apiBaseUrl = 'https://www.googleapis.com/oauth2/v1';
$userInfo = $client->api('userinfo', 'GET');