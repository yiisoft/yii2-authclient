Quick Start
===========

## Adding action to controller

Next step is to add [[yii\authclient\AuthAction]] to a web controller and provide a `successCallback` implementation,
which is suitable for your needs. Typically final controller code may look like following:

```php
use app\components\AuthHandler;

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
        (new AuthHandler($client))->handle();
    }
}
```

Note that it's important for `auth` action to be publically accessible so make sure it's not denied by access control filter.

Where AuthHandler implementation could be like this:

```php
<?php
namespace app\components;

use app\models\Auth;
use app\models\User;
use Yii;
use yii\authclient\ClientInterface;
use yii\helpers\ArrayHelper;

/**
 * AuthHandler handles successful authentification via Yii auth component
 */
class AuthHandler
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function handle()
    {
        $attributes = $this->client->getUserAttributes();
        $email = ArrayHelper::getValue($attributes, 'email');
        $id = ArrayHelper::getValue($attributes, 'id');
        $nickname = ArrayHelper::getValue($attributes, 'login');

        /** @var Auth $auth */
        $auth = Auth::find()->where([
            'source' => $this->client->getId(),
            'source_id' => $id,
        ])->one();

        if (Yii::$app->user->isGuest) {
            if ($auth) { // login
                /** @var User $user */
                $user = $auth->user;
                $this->updateUserInfo($user);
                Yii::$app->user->login($user, Yii::$app->params['user.rememberMeDuration']);
            } else { // signup
                if ($email !== null && User::find()->where(['email' => $email])->exists()) {
                    Yii::$app->getSession()->setFlash('error', [
                        Yii::t('app', "User with the same email as in {client} account already exists but isn't linked to it. Login using email first to link it.", ['client' => $this->client->getTitle()]),
                    ]);
                } else {
                    $password = Yii::$app->security->generateRandomString(6);
                    $user = new User([
                        'username' => $nickname,
                        'github' => $nickname,
                        'email' => $email,
                        'password' => $password,
                    ]);
                    $user->generateAuthKey();
                    $user->generatePasswordResetToken();

                    $transaction = User::getDb()->beginTransaction();

                    if ($user->save()) {
                        $auth = new Auth([
                            'user_id' => $user->id,
                            'source' => $this->client->getId(),
                            'source_id' => (string)$id,
                        ]);
                        if ($auth->save()) {
                            $transaction->commit();
                            Yii::$app->user->login($user, Yii::$app->params['user.rememberMeDuration']);
                        } else {
                            Yii::$app->getSession()->setFlash('error', [
                                Yii::t('app', 'Unable to save {client} account: {errors}', [
                                    'client' => $this->client->getTitle(),
                                    'errors' => json_encode($auth->getErrors()),
                                ]),
                            ]);
                        }
                    } else {
                        Yii::$app->getSession()->setFlash('error', [
                            Yii::t('app', 'Unable to save user: {errors}', [
                                'client' => $this->client->getTitle(),
                                'errors' => json_encode($user->getErrors()),
                            ]),
                        ]);
                    }
                }
            }
        } else { // user already logged in
            if (!$auth) { // add auth provider
                $auth = new Auth([
                    'user_id' => Yii::$app->user->id,
                    'source' => $this->client->getId(),
                    'source_id' => (string)$attributes['id'],
                ]);
                if ($auth->save()) {
                    /** @var User $user */
                    $user = $auth->user;
                    $this->updateUserInfo($user);
                    Yii::$app->getSession()->setFlash('success', [
                        Yii::t('app', 'Linked {client} account.', [
                            'client' => $this->client->getTitle()
                        ]),
                    ]);
                } else {
                    Yii::$app->getSession()->setFlash('error', [
                        Yii::t('app', 'Unable to link {client} account: {errors}', [
                            'client' => $this->client->getTitle(),
                            'errors' => json_encode($auth->getErrors()),
                        ]),
                    ]);
                }
            } else { // there's existing auth
                Yii::$app->getSession()->setFlash('error', [
                    Yii::t('app',
                        'Unable to link {client} account. There is another user using it.',
                        ['client' => $this->client->getTitle()]),
                ]);
            }
        }
    }

    /**
     * @param User $user
     */
    private function updateUserInfo(User $user)
    {
        $attributes = $this->client->getUserAttributes();
        $github = ArrayHelper::getValue($attributes, 'login');
        if ($user->github === null && $github) {
            $user->github = $github;
            $user->save();
        }
    }
}
```

`successCallback` method is called when user was successfully authenticated via external service. Via `$client` instance
we can retrieve information received. In our case we'd like to:
 
- If user is guest and record found in auth then log this user in.
- If user is guest and record not found in auth then create new user and make a record in auth table. Then log in.
- If user is logged in and record not found in auth then try connecting additional account (save its data into auth table).

> Note: different Auth clients may require different approaches while handling authentication success. For example: Twitter
  does not allow returning of the user email, so you have to deal with this somehow.

### Auth client basic structure

Although, all clients are different they shares same basic interface [[yii\authclient\ClientInterface]],
which governs common API.

Each client has some descriptive data, which can be used for different purposes:

- `id` - unique client id, which separates it from other clients, it could be used in URLs, logs etc.
- `name` - external auth provider name, which this client is match too. Different auth clients
  can share the same name, if they refer to the same external auth provider.
  For example: clients for Google and Google Hybrid have same name "google".
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

> Tip: If you are using several different clients, you can unify the structure of the attributes, which they return,
  using [[yii\authclient\BaseClient::normalizeUserAttributeMap]].

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
```

## Adding widget to login view

There's ready to use [[yii\authclient\widgets\AuthChoice]] widget to use in views:

```php
<?= yii\authclient\widgets\AuthChoice::widget([
     'baseAuthUrl' => ['site/auth'],
     'popupMode' => false,
]) ?>
```

