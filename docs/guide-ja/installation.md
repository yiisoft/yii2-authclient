インストール
============

## エクステンションをインストールする

エクステンションをインストールするためには、Composer を使います。次のコマンドを実行します。

```
composer require --prefer-dist yiisoft/yii2-authclient "*"
```

または、あなたの composer.json の `require` セクションに次の行を追加します。

```json
"yiisoft/yii2-authclient": "*"
```

## アプリケーションを構成する

エクステンションがインストールされた後に、認証クライアントコレクションのアプリケーションコンポーネントをセットアップする必要があります。

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

特別な設定なしに使用できる次のクライアントが提供されています。

- [[\yii\authclient\clients\Facebook|Facebook]]
- [[yii\authclient\clients\GitHub|GitHub]]
- Google ([[yii\authclient\clients\GoogleOpenId|OpenID]] または [[yii\authclient\clients\GoogleOAuth|OAuth]] で)
- [[yii\authclient\clients\LinkedIn|LinkedIn]]
- [[yii\authclient\clients\Live|Microsoft Live]]
- [[yii\authclient\clients\Twitter|Twitter]]
- [[yii\authclient\clients\VKontakte|VKontakte]]
- Yandex ([[yii\authclient\clients\YandexOpenId|OpenID]] または [[yii\authclient\clients\YandexOAuth|OAuth]] で)

それぞれのクライアントの構成は少しずつ異なります。
OAuth では、使おうとしているサービスからクライアント ID と秘密キーを取得することが必要です。
OpenID では、たいていの場合、何も設定しなくても動作します。


## 認証データを保存する

外部サービスによって認証されたユーザを認識するために、最初の認証のときに提供された ID を保存し、以後の認証のときにはそれをチェックする必要があります。
ログインのオプションを外部サービスに限定するのは良いアイデアではありません。
外部サービスによる認証が失敗して、ユーザがログインする方法がなくなるかもしれないからです。
そんなことはせずに、外部認証と昔ながらのパスワードによるログインの両方を提供する方が適切です。

ユーザの情報をデータベースに保存しようとする場合、スキーマは次のようなものになります。

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
FOREIGN KEY user_id REFERENCES auth(id) ON DELETE CASCADE ON UPDATE CASCADE;
```

上記の SQL における `user` は、アドバンストプロジェクトテンプレートでユーザ情報を保存するために使われている標準的なテーブルです。
全てのユーザはそれぞれ複数の外部サービスを使って認証できますので、全ての `user` レコードはそれぞれ複数の `auth` レコードと関連を持ち得ます。
`auth` テーブルにおいて `source` は使用される認証プロバイダの名前であり、`source_id` はログイン成功後に外部サービスから提供される一意のユーザ識別子です。

上記で作成されたテーブルを使って `Auth` モデルを生成することが出来ます。これ以上の修正は必要ありません。

