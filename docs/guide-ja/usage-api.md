追加の API 呼び出しで追加のデータを取得する
===========================================

[[yii\authclient\OAuth1]] と [[yii\authclient\OAuth2]] は、ともに、`api()` メソッドをサポートしており、
これによって外部認証プロバイダの REST API にアクセスすることが出来ます。

API の呼び出しを使用するためには、API の仕様に従って [[yii\authclient\BaseOAuth::apiBaseUrl]] をセットアップする必要があります。
そうすれば [[yii\authclient\BaseOAuth::api()]] メソッドを呼ぶことが出来ます。

```php
use yii\authclient\OAuth2;

$client = new OAuth2;

// ...

$client->apiBaseUrl = 'https://www.googleapis.com/oauth2/v1';
$userInfo = $client->api('userinfo', 'GET');
```

[[\yii\authclient\BaseOAuth::api()]] メソッドは非常に基本的なものであり、いくつかの API コマンドで要求されるだけの柔軟性を提供していません。
代りに [[\yii\authclient\BaseOAuth::createApiRequest()]] を使うことが出来ます。
これによって生成される [[\yii\httpclient\Request]] のインスタンスは、HTTP リクエストの作成に関してより強力な制御を行うことを可能にします。
例えば、

```php
/* @var $client \yii\authclient\OAuth2 */
$client = Yii::$app->authClientCollection->getClient('someOAuth2');

// 外部サービスに追加すべきユーザを探す
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
    // 失敗
}
echo $response->data['id'];
```

HTTP リクエストの送信に関する詳細は、[yii2-httpclient](https://github.com/yiisoft/yii2-httpclient)
のドキュメントを参照して下さい。

[[\yii\authclient\BaseOAuth::createApiRequest()]] によって生成されたリクエストは、
自動的にサインアップされ(OAuth 1.0 を使用する場合)、送信される前にアクセス・トークンを適用されます。
これらのプロセスに対する完全な制御を獲得したい場合は、代りに [[\yii\authclient\BaseClient::createRequest()]] を使わなければなりません。
[[\yii\authclient\BaseOAuth::applyAccessTokenToRequest()]] および [[yii\authclient\OAuth1::signRequest()]] のメソッドを使って、
その API リクエストに必要なアクションを実行することが出来ます。
例えば、

```php
/* @var $client \yii\authclient\OAuth1 */
$client = Yii::$app->authClientCollection->getClient('someOAuth1');

$request = $client->createRequest()
    ->setMethod('GET')
    ->setUrl('users');

$client->applyAccessTokenToRequest($request, $myAccessToken); // API のためのカスタム・アクセス・トークンを使う
$client->signRequest($request, $myAccessToken); // カスタム・アクセス・トークンでリクエストにサインをする

$response = $request->send();
```
