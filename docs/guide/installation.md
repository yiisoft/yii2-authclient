Installation
============

## Installing an extension

In order to install extension use Composer. Either run

```
composer require --prefer-dist yiisoft/yii2-authclient "~2.1.0"
```

or add

```json
"yiisoft/yii2-authclient": "~2.1.0"
```

to the `require` section of your composer.json.

## Configuring application

After extension is installed you need to setup auth client collection application component:

```php
return [
    'components' => [
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'google' => [
                    'class' => 'yii\authclient\clients\Google',
                    'clientId' => 'google_client_id',
                    'clientSecret' => 'google_client_secret',
                ],
                'facebook' => [
                    'class' => 'yii\authclient\clients\Facebook',
                    'clientId' => 'facebook_client_id',
                    'clientSecret' => 'facebook_client_secret',
                ],
                // etc.
            ],
        ]
        // ...
    ],
    // ...
];
```

Out of the box the following clients are provided:

- [[\yii\authclient\clients\Facebook|Facebook]].
- [[yii\authclient\clients\GitHub|GitHub]].
- Google (via [[yii\authclient\clients\Google|OAuth]] and [[yii\authclient\clients\GoogleHybrid|OAuth Hybrid]]).
- [[yii\authclient\clients\LinkedIn|LinkedIn]].
- [[yii\authclient\clients\Live|Microsoft Live]].
- [[yii\authclient\clients\Twitter|Twitter]].
- [[yii\authclient\clients\VKontakte|VKontakte]].
- [[yii\authclient\clients\Yandex|Yandex]].

Configuration for each client is a bit different. For OAuth it's required to get client ID and secret key from
the service you're going to use. For OpenID it works out of the box in most cases.

## Storing authorization data

In order to recognize the user authenticated via external service we need to store ID provided on first authentication
and then check against it on subsequent authentications. It's not a good idea to limit login options to external
services only since these may fail and there won't be a way for the user to log in. Instead it's better to provide
both external authentication and good old login and password.

If we're storing user information in a database the corresponding migration code could be the following:

```php
class m??????_??????_auth extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull(),
            'auth_key' => $this->string()->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->notNull(),
            'email' => $this->string()->notNull(),
            'github' => $this->string(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createTable('auth', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'source' => $this->string()->notNull(),
            'source_id' => $this->string()->notNull(),
        ]);

        $this->addForeignKey('fk-auth-user_id-user-id', 'auth', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('auth');
        $this->dropTable('user');
    }
}
```

In the above example `user` is a standard table that is used in advanced project template to store user info.
Each user can authenticate using multiple external services therefore each `user` record can relate to
multiple `auth` records. In the `auth` table `source` is the name of the auth provider used and `source_id` is
unique user identifier that is provided by external service after successful login.

Using tables created above we can generate `Auth` model. No further adjustments needed.

