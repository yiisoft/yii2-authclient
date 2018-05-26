Quick Start
===========

## Adding action to controller

Next step is to add [[yii\authclient\AuthAction]] to a web controller and provide a `successCallback` implementation,
which is suitable for your needs. Typically, the final controller code will look like the following:

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

> Note that it's important for the `auth` action to be publically accessible, so make sure it's not denied by access control filter.

The following code shows an example AuthHandler, which will need modifying for your app.

Notes
* The namespaces for models `Auth` and `User` reflect the basic app template. Put e.g. `use common\models\Auth;` for the advanced template.
* You need to generate the `Auth` model from the table mentioned [here](installation.md)
* The attribute names `email`, `sub` and `nickname` at the start of `function handle()` are defined by OpenIdConnect. 
If your provider is OAuth, these names might be different.
* You might need to set a specific `scope` value in your provider config to get back the required attributes. For OpenIdConnect, 
you will need a scope of `openid email profile` to get the claims below returned. If not using nickname, you can use `openid email`. 
Other providers will have unique scope to claim mappings.
* This code demonstrates a custom field `github` in `User` which is both set and updated by `function updateUserInfo(User $user)` 
when the user is created and every time they login to ensure it stays up to date. The example migration does not include this 
custom field, if you don't need it, remove the code below.
* Different Auth clients may require different approaches while handling authentication success. For example: Twitter
does not return the user email, so you have to deal with this somehow.
* This code does not handle a new user having a username (nickname) that matches an existing username (where the emails 
are different) and the database insert will fail. You can code it to generate a unique id in this case (see below for example) or 
otherwise redirect the user to a page to allow them to set a username.

```php
<?php
namespace app\components;

use app\models\Auth;
use app\models\User;
use Yii;
use yii\authclient\ClientInterface;
use yii\helpers\ArrayHelper;

/**
 * AuthHandler handles successful authentication via Yii auth component
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
        $id = ArrayHelper::getValue($attributes, 'sub');
        $nickname = ArrayHelper::getValue($attributes, 'nickname');

        /* @var Auth $auth */
        $auth = Auth::find()->where([
            'source' => $this->client->getId(),
            'source_id' => $id,
        ])->one();

        if (Yii::$app->user->isGuest) {
            if ($auth) { // login
                /* @var User $user */
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

### Generate unique username in AuthHandler

If you do not want the user to interact with your system when their external nickname matches an existing username in the database,
you can use the following code snippet to add a number onto the end until a unique username is found. This code is just before
`$user = new User`.

```php
if ( !isset($nickname) )
{
    // Provider might not return a suitable nickname
    // so start by taking the part before the @ sign as a nickname
    $nickname = substr($email,0,strpos($email,'@'));
}
// If this username already exists, create another one with a number on the end
if ( User::find()->where(['username'=>$nickname])->exists() )
{
    $index = 1;
    // Check username is not already taken
    while ( User::find()->where(['username'=>$nickname.$index])->exists()) { ++$index; }
    $nickname = $nickname.$index;
}
$user = new User([
```

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
  using [[yii\authclient\BaseClient::$normalizeUserAttributeMap]].


## Adding widget to login view

There's ready to use [[yii\authclient\widgets\AuthChoice]] widget to use in views:

```php
<?= yii\authclient\widgets\AuthChoice::widget([
     'baseAuthUrl' => ['site/auth'],
     'popupMode' => false,
]) ?>
```

