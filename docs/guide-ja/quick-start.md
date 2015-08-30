クイックスタート
================

## コントローラにアクションを追加する

次のステップは、ウェブのコントローラに [[yii\authclient\AuthAction]] を追加して、あなたの必要に応じた `successCallback` の実装を提供することです。
典型的には、コントローラのコードは、最終的に、次のようなものになります。


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
            if ($auth) { // ログイン
                $user = $auth->user;
                Yii::$app->user->login($user);
            } else { // ユーザ登録
                if (isset($attributes['email']) && User::find()->where(['email' => $attributes['email']])->exists()) {
                    Yii::$app->getSession()->setFlash('error', [
                        Yii::t('app', "{client} のアカウントと同じメールアドレスを持つユーザが既に存在しますが、まだそのアカウントとリンクされていません。リンクするために、まずメールアドレスを使ってログインしてください。", ['client' => $client->getTitle()]),
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
        } else { // ユーザは既にログインしている
            if (!$auth) { // 認証プロバイダを追加
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

外部サービスによるユーザの認証が成功すると `successCallback` メソッドが呼ばれます。
`$client` インスタンスを通じて、外部サービスから受け取った情報を取得することが出来ます。
私たちの例では、次のことをしようとしています。

- ユーザがゲストであり、auth にレコードが見つかった場合は、そのユーザをログインさせる。
- ユーザがゲストであり、auth にレコードが見つからなかった場合は、新しいユーザを作成して、auth テーブルにレコードを作成する。そして、ログインさせる。
- ユーザがログインしており、auth にレコードが見つからなかった場合は、追加のアカウントにも接続するようにする (そのデータを auth テーブルに保存する)。

> Note|注意: Auth クライアントの違いによって、認証の成功を処理するときの方法も違ったものになります。
  たとえば、Twitter はユーザの email を返すことを許していませんので、何らかの方法でそれに対処しなければなりません。


### Auth クライアントの基本的な構造

全ての Auth クライアントには違いがありますが、同じインタフェイス  [[yii\authclient\ClientInterface]] を共有し、共通の API によって管理されます。

各クライアントは、異なる目的に使用できるいくつかの説明的なデータを持っています。

- `id` - クライアントを他のクライアントから区別する一意の ID。
  URL やログに使うことが出来ます。
- `name` - このクライアントが属する外部認証プロバイダの名前。
  認証クライアントが異なっても、同じ外部認証プロバイダを参照している場合は、同じ名前になることがあります。
  例えば、Google OpenID のクライアントと Google OAuth のクライアントは同じ名前 "google" を持ちます。
  この属性は内部的にデータベースや CSS スタイルなどにおいて使用することが出来ます。
- `title` - 外部認証プロバイダのユーザフレンドリな名前。ビューのレイヤにおいて認証クライアントを表示するのに使用されます。

それぞれの認証クライアントは異なる認証フローを持ちますが、すべてのものが `getUserAttributes()` メソッドをサポートしており、認証が成功した後にこのメソッドを呼び出すことが出来ます。

このメソッドによって、外部のユーザアカウントの情報、例えば、ID、メールアドレス、フルネーム、優先される言語などを取得することが出来ます。
ただし、プロバイダごとに利用できるフィールドの有無や名前が異なることに注意してください。

外部認証プロバイダが返すべき属性を定義するリストは、クライアントのタイプに依存します。

- [[yii\authclient\OpenId]]: `requiredAttributes` と `optionalAttributes` の組み合わせ。
- [[yii\authclient\OAuth1]] と [[yii\authclient\OAuth2]]: `scope` フィールド。
  プロバイダによってスコープの形式が異なることに注意。


> Tip|ヒント: いくつかの異なるクライアントを使用する場合は、[[yii\authclient\BaseClient::normalizeUserAttributeMap]] を使って、クライアントが返す属性を統一することが出来ます。


### API 呼び出しによって追加のデータを取得する

[[yii\authclient\OAuth1]] と [[yii\authclient\OAuth2]] は、ともに、`api()` メソッドをサポートしており、これによって外部認証プロバイダの REST API にアクセスすることが出来ます。
ただし、このメソッドは非常に基本的なもので、外部 API の完全な機能にアクセスするためには、十分なものではありません。
このメソッドは、主として、外部のユーザアカウントの情報を取得するために使用されます。

API の呼び出しを使用するためには、API の仕様に従って [[yii\authclient\BaseOAuth::apiBaseUrl]] をセットアップする必要があります。
そうすれば [[yii\authclient\BaseOAuth::api()]] メソッドを呼ぶことが出来ます。

```php
use yii\authclient\OAuth2;

$client = new OAuth2;

// ...

$client->apiBaseUrl = 'https://www.googleapis.com/oauth2/v1';
$userInfo = $client->api('userinfo', 'GET');
```

## ログインビューにウィジェットを追加する

そのまま使える [[yii\authclient\widgets\AuthChoice]] ウィジェットをビューで使用することが出来ます。

```php
<?= yii\authclient\widgets\AuthChoice::widget([
     'baseAuthUrl' => ['site/auth'],
     'popupMode' => false,
]) ?>
```

