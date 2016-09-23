Установка
============

## Установка расширения

Для установки расширения используйте Composer. Запустите
                                            
```
composer require --prefer-dist yiisoft/yii2-authclient "~2.1.0"
```

или добавьте

```json
"yiisoft/yii2-authclient": "~2.1.0"
```

в секцию `require` вашего composer.json.

## Настройка приложения

После установки расширения необходимо настроить компонент приложения auth client collection: 

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
                    'clientSecret' => 'секретный_ключ_facebook_client',
                ],
                // и т.д.
            ],
        ]
        // ...
    ],
    // ...
];
```

Из коробки предоставляются следующие клиенты:

- [[\yii\authclient\clients\Facebook|Facebook]].
- [[yii\authclient\clients\GitHub|GitHub]].
- Google (с помощью [[yii\authclient\clients\Google|OAuth]] и [[yii\authclient\clients\GoogleHybrid|OAuth Hybrid]]).
- [[yii\authclient\clients\LinkedIn|LinkedIn]].
- [[yii\authclient\clients\Live|Microsoft Live]].
- [[yii\authclient\clients\Twitter|Twitter]].
- [[yii\authclient\clients\VKontakte|VKontakte]].
- [[yii\authclient\clients\Yandex|Yandex]].

Конфигурация для каждого клиента несколько отличается. Для OAuth, это обязательное получение ID клиента и секретного
ключа сервиса, который Вы собираетесь использовать. Для OpenID, в большинстве случаев, это работает из коробки.

## Хранение данных авторизации

Для того, что бы считать пользователя аутентифицированным при помощи внешнего сервиса, мы должны сохранить ID,
предоставленный при первой аутентификации, а потом проверять его при последующих попытках. Ограничивать варианты 
аутентификации только внешними сервисами, не самая лучшая идея, так как такой вид аутентификации может
потерпеть неудачу, тем самым не оставив других вариантов аутентификации для пользователя. Вместо этого лучше
обеспечить как возможность аутентификации через внешние сервисы, так и старый метод аутентификации с ипользованием 
логина и пароля.

Если мы храним информацию о пользователях в базе данных, то код соответвующей миграции может выглядеть следующим образом:

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

В приведённом выше примере представлена стандартная таблица `user`, используемая в шаблоне проекта Advanced для хранения
информации о пользователях. Каждый пользователь может пройти аутентификацию используя несколько внешних сервисов,
поэтому каждая запись в `user` может относится к нескольким записям в `auth`. Поле `source` в таблице `auth`
это название используемого провайдера аутентификации и `source_id` это уникальный идентификатор пользователя,
который предоставляется внешним сервисом после успешной аутентификации.

Используя таблицы, созданные ранее мы можем сгенерировать модель `Auth`. Дальнейшие настройки не требуются.

