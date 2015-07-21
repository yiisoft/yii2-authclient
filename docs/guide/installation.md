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
    source varchar(255) NOT NULL,
    source_id varchar(255) NOT NULL
);

ALTER TABLE auth ADD CONSTRAINT fk-auth-user_id-user-id
FOREIGN KEY user_id REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE;
```

In the SQL above `user` is a standard table that is used in advanced project template to store user
info. Each user can authenticate using multiple external services therefore each `user` record can relate to
multiple `auth` records. In the `auth` table `source` is the name of the auth provider used and `source_id` is
unique user identificator that is provided by external service after successful login.

Using tables created above we can generate `Auth` model. No further adjustments needed.

