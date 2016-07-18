Getting additional data via extra API calls
===========================================

Both [[\yii\authclient\OAuth1]] and [[\yii\authclient\OAuth2]] provide method `api()`, which
can be used to access external auth provider REST API.

To use API calls, you need to setup [[\yii\authclient\BaseOAuth::apiBaseUrl]] according to the
API specification. Then you can call [[\yii\authclient\BaseOAuth::api()]] method:

```php
use yii\authclient\OAuth2;

$client = new OAuth2;

// ...

$client->apiBaseUrl = 'https://www.googleapis.com/oauth2/v1';
$userInfo = $client->api('userinfo', 'GET');
```

Method [[\yii\authclient\BaseOAuth::api()]] is very basic and does not provide enough flexibility required for
some API commands. You may use [[\yii\authclient\BaseOAuth::createApiRequest()]] instead - it will create an
instance of [[\yii\httpclient\Request]], which allows much more control over HTTP request composition.
For example:

```php
/* @var $client \yii\authclient\OAuth2 */
$client = Yii::$app->authClientCollection->getClient('someOAuth2');

// find user to add to external service:
$user = User::find()->andWhere(['email' => 'johndoe@domain.com'])->one();

$response = $client->createApiRequest()
    ->setMethod('GET')
    ->setUrl('users')
    ->setData([
        'id' => $user->id,
    ])
    ->send();

if ($response->statusCode != 404) {
    throw new \Exception('User "johndoe@domain.com" already exist');
}

$response = $client->createApiRequest()
    ->setMethod('PUT')
    ->setUrl('users')
    ->setData($user->attributes)
    ->addHeaders([
        'MyHeader' => 'my-value'
    ])
    ->send();

if (!$response->isOk) {
    // failure
}
echo $response->data['id'];
```

Please refer to [yii2-httpclient](https://github.com/yiisoft/yii2-httpclient) documentation for details about HTTP
request sending.

Request created via [[\yii\authclient\BaseOAuth::createApiRequest()]] will be automatically signed up (in case of
OAuth 1.0 usage) and have access token applied before being sent. If you wish to gain full control over these processes,
you should use [[\yii\authclient\BaseClient::createRequest()]] instead.
You may use [[\yii\authclient\BaseOAuth::applyAccessTokenToRequest()]] and [[yii\authclient\OAuth1::signRequest()]] method
to perform missing actions for the API request.
For example:

```php
/* @var $client \yii\authclient\OAuth1 */
$client = Yii::$app->authClientCollection->getClient('someOAuth1');

$request = $client->createRequest()
    ->setMethod('GET')
    ->setUrl('users');

$client->applyAccessTokenToRequest($request, $myAccessToken); // use custom access token for API
$client->signRequest($request, $myAccessToken); // sign request with custom access token

$response = $request->send();
```
