Настройка HTTP клиента
======================

Расширение использует [yii2-httpclient](https://github.com/yiisoft/yii2-httpclient) для отправки HTTP запросов.
Вам может понадобиться изменить конфигурацию по умолчанию для используемого HTTP клиента, например, в случае если вам
нужно использовать особый транспорт для запросов.

Каждый Auth клиент имеет свойство `httpClient`, которое может быть использовано для задания HTTP клиента для Auth клиента.
Например:

```php
use yii\authclient\Google;

$authClient = new Google([
    'httpClient' => [
        'transport' => 'yii\httpclient\CurlTransport',
    ],
]);
```

В случае, если вы используете компонент [[\yii\authclient\Collection]], вы можете воспользоваться его свойством `httpClient`
для задания конфигурации HTTP клиента для всех внутренних Auth клиентов.
Пример конфигурации приложения:

```php
return [
    'components' => [
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            // все Auth клиенты будут использовать эту конфигурацию для HTTP клиента:
            'httpClient' => [
                'transport' => 'yii\httpclient\CurlTransport',
            ],
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
        //...
    ],
    // ...
];
```
