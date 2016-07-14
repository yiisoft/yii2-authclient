快速开始
========

## 向控制器中添加动作

下一步是向 Web 控制器中添加 [[yii\authclient\AuthAction]]，然后实现 `successCallback` 方法，
该方法与你的实际需要保持一致。典型的最终控制器类似如下代码：

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

        /* @var $auth Auth */
        $auth = Auth::find()->where([
            'source' => $client->getId(),
            'source_id' => $attributes['id'],
        ])->one();
        
        if (Yii::$app->user->isGuest) {
            if ($auth) { // 登录
                $user = $auth->user;
                Yii::$app->user->login($user);
            } else { // 注册
                if (isset($attributes['email']) && User::find()->where(['email' => $attributes['email']])->exists()) {
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
        } else { // 用户已经登陆
            if (!$auth) { // 添加验证提供商（向验证表中添加记录）
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

当用户由外部服务验证通过后调用 `successCallback` 方法。通过 `$client` 实例我们可以检索收到的信息。
在我们的例子中，我们可以：
 
- 若用户是访客，且在验证信息中找到该用户，则登录该用户。
- 若用户是访客，却在验证信息中找不到该用户，则创建一个新用户，并添加记录到验证表中，然后登录。
- 若用户已登录，却在验证信息中找不到该用户，则尝试额外账户建立连接（将该用户的数据保存到验证表中）。

> 注意：不同的验证客户端在验证通过时可能需要不同的方法。例如：Twitter 不返回用户电子邮件，
  所以你需要单独处理此种情况。

### 验证客户端基本结构

尽管每个客户端不尽相同，但它们都共享同一个基础接口 [[yii\authclient\ClientInterface]]，
该接口包含了通用的 API。

每个客户端都有一些描述性的数据，分别用于不同的目的：

- `id` - 唯一客户 ID，用于与其它客户端区分，它可以用于 URL 和日志等。
- `name` - 外部验证提供商名称，与该客户端匹配。不同的客户端可以使用相同的名称，
  即它们指的是同一个外部验证提供商。
  例如：谷歌 Google 客户端和 Google Hybrid 客户端有一个相同的名称 “google”。
  该树形可以用在数据库内部、CSS 样式中等等。
- `title` - 用户友好的外部验证提供商名称，用于在验证客户端的视图层展示。

每个验证客户端都有不同的验证流程，但是它们都支持 `getUserAttributes()` 方法，
可在验证通过后调用。

该方法允许你获取外部用户账户的信息，如 ID、电子邮件账户、全名、首选语言等等。
注意：对于每个提供商，可用域的名称和存在性可能不同。

定义属性列表，用于通知外部验证提供商应当返回列表，根据不同的客户端类型：

- [[yii\authclient\OpenId]]: 同时定义 `requiredAttributes` 和 `optionalAttributes`.
- [[yii\authclient\OAuth1]] 和 [[yii\authclient\OAuth2]]: 定义 `scope` 域。注意，
  不同的提供商对于范围的格式定义可能不同。

> 提示：如果你正在使用若干个不同的客户端，你可以使用 [[yii\authclient\BaseClient::normalizeUserAttributeMap]] 统一返回属性的列表。

### 通过补充 API 调用获取额外数据

[[yii\authclient\OAuth1]] 和 [[yii\authclient\OAuth2]] 均提供了 `api()` 方法，
可以用于访问外部服务提供商的 REST API。然而该方法比较基础，可能并不足以访问
所有外部 API 功能。该方法主要用于取回外部用户账户数据。

要使用 API 调用，你需要根据 API 说明设置 [[yii\authclient\BaseOAuth::apiBaseUrl]]。
之后就可以调用 [[yii\authclient\BaseOAuth::api()]] 方法了：

```php
use yii\authclient\OAuth2;

$client = new OAuth2;

// ...

$client->apiBaseUrl = 'https://www.googleapis.com/oauth2/v1';
$userInfo = $client->api('userinfo', 'GET');
```

## 向登录视图添加小部件

[[yii\authclient\widgets\AuthChoice]] 小部件用于视图中：

```php
<?= yii\authclient\widgets\AuthChoice::widget([
     'baseAuthUrl' => ['site/auth'],
     'popupMode' => false,
]) ?>
```

