クイック・スタート
==================

## コントローラにアクションを追加する

次のステップは、ウェブのコントローラに [[yii\authclient\AuthAction]] を追加して、あなたの必要に応じた `successCallback` の実装を提供することです。
典型的な場合、コントローラのコードは、最終的には次のようなものになります。

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

`auth` アクションがパブリックにアクセス可能であることが重要ですので、アクセス・コントロール・フィルタでアクセスが拒否されないように注意して下さい。

AuthHandler の実装は次のようなものになります。

```php
<?php
namespace app\components;

use app\models\Auth;
use app\models\User;
use Yii;
use yii\authclient\ClientInterface;
use yii\helpers\ArrayHelper;

/**
 * Yii の auth コンポーネントによって認証の成功を処理する AuthHandler
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
        $id = ArrayHelper::getValue($attributes, 'id');
        $nickname = ArrayHelper::getValue($attributes, 'login');

        /* @var $auth Auth */
        $auth = Auth::find()->where([
            'source' => $client->getId(),
            'source_id' => $attributes['id'],
        ])->one();

        if (Yii::$app->user->isGuest) {
            if ($auth) { // ログイン
                /* @var User $user */
                $user = $auth->user;
                $this->updateUserInfo($user);
                Yii::$app->user->login($user, Yii::$app->params['user.rememberMeDuration']);
            } else { // ユーザ登録
                if ($email !== null && User::find()->where(['email' => $email])->exists()) {
                    Yii::$app->getSession()->setFlash('error', [
                        Yii::t('app', "{client} のアカウントと同じメール・アドレスを持つユーザが既に存在しますが、まだそのアカウントとリンクされていません。リンクするために、まずメール・アドレスを使ってログインしてください。", ['client' => $this->client->getTitle()]),
                    ]);
                } else {
                    $password = Yii::$app->security->generateRandomString(6);
                    $user = new User([
                        'username' => $nickname,
                        'github' => $nickname,
                        'email' => $email,
                        'password' => $password,
                        // 'status' => User::STATUS_ACTIVE // 状態を正しくセットすること
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
                                Yii::t('app', '{client} のアカウントを保存することが出来ません: {errors}', [
                                    'client' => $this->client->getTitle(),
                                    'errors' => json_encode($auth->getErrors()),
                                ]),
                            ]);
                        }
                    } else {
                        Yii::$app->getSession()->setFlash('error', [
                            Yii::t('app', 'ユーザを保存することが出来ません: {errors}', [
                                'client' => $this->client->getTitle(),
                                'errors' => json_encode($user->getErrors()),
                            ]),
                        ]);
                    }
                }
            }
        } else { // ユーザは既にログインしている
            if (!$auth) { // 認証プロバイダを追加
                $auth = new Auth([
                    'user_id' => Yii::$app->user->id,
                    'source' => $this->client->getId(),
                    'source_id' => (string)$attributes['id'],
                ]);
                if ($auth->save()) {
                    /* @var User $user */
                    $user = $auth->user;
                    $this->updateUserInfo($user);
                    Yii::$app->getSession()->setFlash('success', [
                        Yii::t('app', '{client} のアカウントをリンクしました。', [
                            'client' => $this->client->getTitle()
                        ]),
                    ]);
                } else {
                    Yii::$app->getSession()->setFlash('error', [
                        Yii::t('app', '{client} のアカウントをリンクすることが出来ません: {errors}', [
                            'client' => $this->client->getTitle(),
                            'errors' => json_encode($auth->getErrors()),
                        ]),
                    ]);
                }
            } else { // 既に使用されている
                Yii::$app->getSession()->setFlash('error', [
                    Yii::t('app',
                        '{client} のアカウントをリンクすることが出来ません。それを使用している別のユーザがいます。',
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

外部サービスによるユーザの認証が成功すると `successCallback` メソッドが呼ばれます。
`$client` インスタンスを通じて、外部サービスから受け取った情報を取得することが出来ます。私たちの例では、次のことをしようとしています。

- ユーザがゲストであり、auth にレコードが見つかった場合は、そのユーザをログインさせる。
- ユーザがゲストであり、auth にレコードが見つからなかった場合は、新しいユーザを作成して、auth テーブルにレコードを作成する。そして、ログインさせる。
- ユーザがログインしており、auth にレコードが見つからなかった場合は、追加のアカウントにも接続するようにする (そのデータを auth テーブルに保存する)。

> Note: Auth クライアントの違いによって、認証の成功を処理するときの方法も違ったものになります。
  たとえば、Twitter はユーザの email を返すことを許していませんので、何らかの方法でそれに対処しなければなりません。

### Auth クライアントの基本的な構造

全ての Auth クライアントには違いがありますが、同じインタフェイス  [[yii\authclient\ClientInterface]] を共有し、
共通の API によって管理されます。

各クライアントは、異なる目的に使用できるいくつかの説明的なデータを持っています。

- `id` - クライアントを他のクライアントから区別する一意の ID。URL やログに使うことが出来ます。
- `name` - このクライアントが属する外部認証プロバイダの名前。
  認証クライアントが異なっても、同じ外部認証プロバイダを参照している場合は、同じ名前になることがあります。
  例えば、Google のクライアントと Google Hybrid のクライアントは同じ名前 "google" を持ちます。
  この属性は内部的にデータベースや CSS スタイルなどにおいて使用することが出来ます。
- `title` - 外部認証プロバイダのユーザ・フレンドリな名前。
  ビューのレイヤにおいて認証クライアントを表示するのに使用されます。

それぞれの認証クライアントは異なる認証フローを持ちますが、すべてのものが `getUserAttributes()` メソッドをサポートしており、
認証が成功した後にこのメソッドを呼び出すことが出来ます。

このメソッドによって、外部のユーザ・アカウントの情報、例えば、ID、メール・アドレス、フル・ネーム、
優先される言語などを取得することが出来ます。
ただし、プロバイダごとに利用できるフィールドの有無や名前が異なることに注意してください。

外部認証プロバイダが返すべき属性を定義するリストは、クライアントのタイプに依存します。

- [[yii\authclient\OpenId]]: `requiredAttributes` と `optionalAttributes` の組み合わせ。
- [[yii\authclient\OAuth1]] と [[yii\authclient\OAuth2]]: `scope` フィールド。
  プロバイダによってスコープの形式が異なることに注意。

> Tip: いくつかの異なるクライアントを使用する場合は、[[yii\authclient\BaseClient::normalizeUserAttributeMap]] を使って、
  クライアントが返す属性を統一することが出来ます。


## ログイン・ビューにウィジェットを追加する

そのまま使える [[yii\authclient\widgets\AuthChoice]] ウィジェットをビューで使用することが出来ます。

```php
<?= yii\authclient\widgets\AuthChoice::widget([
     'baseAuthUrl' => ['site/auth'],
     'popupMode' => false,
]) ?>
```

## GitHub クライアントに関する注意

最近 GitHub のコールバック処理に変更があったらしく、そのために、GitHub がユーザを我々のアプリにリダイレクトして返す時の 404 エラーを避けるためには、権限コールバックURL のクエリ・パラメータに `authclient=github` を含める必要があるようになりました。

例: アプリは `https://example.com` にあるとしましょう。「GitHub でログイン」機能をアプリに実装するために、`example.com` のための 新しい 0auth アプリを https://github.com/settings/applications/new で作成します。「権限コールバックURL (Authorization callback URL)」の入力フィールドに `https://example.com/site/auth` を入力すると、[`run()`](https://github.com/yiisoft/yii2-authclient/blob/master/src/AuthAction.php#L213) メソッドが 404 エラーを返すことになります。この問題を解決するために上記のクエリ・パラメータが必要になります。つまり、「権限コールバックURL」の値を `https://example.com/site/auth?authclient=github` とすることが必要になります。
