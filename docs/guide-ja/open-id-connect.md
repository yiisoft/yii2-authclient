OpenID �ڑ�
===========

���̃G�N�X�e���V�����́A[[\yii\authclient\OpenIdConnect]] �N���X��ʂ��āA[OpenId �ڑ�](http://openid.net/connect/) �F�؃v���g�R���̃T�|�[�g��񋟂��܂��B

�A�v���P�[�V�����ݒ�̗�:

```php
'components' => [
    'authClientCollection' => [
        'class' => 'yii\authclient\Collection',
        'clients' => [
            'google' => [
                'class' => 'yii\authclient\OpenIdConnect',
                'issuerUrl' => 'https://accounts.google.com',
                'clientId' => 'google_client_id',
                'clientSecret' => 'google_client_secret',
                'name' => 'google',
                'title' => 'Google OpenID �ڑ�',
            ],
        ],
    ]
    // ...
]
```

�F�؂̃��[�N�t���[�́AOAuth2 �̏ꍇ�ƑS�������ł��B

**����!** 'OpenID �ڑ�' �v���g�R���́A�F�؂̃v���Z�X���Z�L���A�ɂ��邽�߂ɁA [JWS](http://tools.ietf.org/html/draft-ietf-jose-json-web-signature) ���؂��g���܂��B
���̂悤�Ȍ��؂��g�����߂ɂ́B���̃G�N�X�e���V�������f�t�H���g�ł͗v�����Ă��Ȃ� `spomky-labs/jose` ���C�u�������C���X�g�[������K�v������܂��B

```
composer require --prefer-dist "spomky-labs/jose:~5.0.6"
```

�܂��́A���L�����Ȃ��� composer.joson ��`require` �Z�N�V�����ɒǉ����܂��B

```json
"spomky-labs/jose": "~5.0.6"
```

> Note: ���Ȃ����\���ɐM�����ꂽ 'OpenID �ڑ�' �v���o�C�_���g�����Ƃ���ꍇ�́A
[[\yii\authclient\OpenIdConnect::$validateJws]] �𖳌������A`spomky-labs/jose` ���C�u�����̃C���X�g�[�����璷�Ȃ��̂Ƃ��Ċ����ł��܂��B
�������A�v���g�R���̎d�l�ɔ����邱�Ƃł��̂ŁA�����߂͏o���܂���B
