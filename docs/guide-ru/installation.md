Установка
============

## Установка расширения

Для установки расширения используйте Composer. Запустите
                                            
```
composer require --prefer-dist yiisoft/yii2-authclient "*"
```

или добавьте

```json
"yiisoft/yii2-authclient": "*"
```
в секцию `require` вашего composer.json.

## Настройка приложения

После установки расширения необходимо настроить компонент приложения auth client collection: 

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
                'clientSecret' => 'секретный_ключ_facebook_client',
            ],
            // и т.д.
        ],
    ]
    ...
]
```

Из коробки предоставляются следующие клиенты:

- [[\yii\authclient\clients\Facebook|Facebook]].
- [[yii\authclient\clients\GitHub|GitHub]].
- Google (с помощью [[yii\authclient\clients\GoogleOpenId|OpenID]] и [[yii\authclient\clients\GoogleOAuth|OAuth]]).
- [[yii\authclient\clients\LinkedIn|LinkedIn]].
- [[yii\authclient\clients\Live|Microsoft Live]].
- [[yii\authclient\clients\Twitter|Twitter]].
- [[yii\authclient\clients\VKontakte|VKontakte]].
- Яндекс (с помощью [[yii\authclient\clients\YandexOpenId|OpenID]] и [[yii\authclient\clients\YandexOAuth|OAuth]]).

Конфигурация для каждого клиента несколько отличается. Для OAuth, это обязательное получение ID клиента и секретного
ключа сервиса, который Вы собираетесь использовать. Для OpenID, в большинстве случаев, это работает из коробки.

## Хранение данных авторизации

Для того, что бы считать пользователя аутентифицированным при помощи внешнего сервиса, мы должны сохранить ID,
предоставленный при первой аутентификации, а потом проверять его при последующих попытках. Ограничивать варианты 
аутентификации только внешними сервисами, не самая лучшая идея, так как такой вид аутентификации может
потерпеть неудачу, тем самым не оставив других вариантов аутентификации для пользователя. Вместо этого лучше
обеспечить как возможность аутентификации через внешние сервисы, так и старый метод аутентификации с ипользованием 
логина и пароля.

Если мы храним информацию о пользователях в базе данных, то её схема может выглядеть следующим образом:

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

В приведённом выше SQL представлена стандартная таблица `user`, используемая в шаблоне проекта Advanced для хранения
информации о пользователях. Каждый пользователь может пройти аутентификацию используя несколько внешних сервисов,
поэтому каждая запись в `user` может относится ко нескольким записям в `auth`. Поле `source` в таблице `auth`
это название используемого провайдера аутентификации и `source_id` это уникальный идентификатор пользователя,
который предоставляется внешним сервисом после успешной аутентификации.

Используя таблицы, созданные ранее мы можем сгенерировать модель `Auth`. Дальнейшие настройки не требуются.

