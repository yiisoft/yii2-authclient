Upgrading Instructions for Yii Framework v2 AuthClient Extension
================================================================

!!!IMPORTANT!!!

The following upgrading instructions are cumulative. That is,
if you want to upgrade from version A to version C and there is
version B between A and C, you need to following the instructions
for both A and B.

Upgrade from yii2-authclient 2.0.6
----------------------------------

* The signature of the following methods has been changed: `yii\authclient\BaseOAuth::sendRequest()`,
  `yii\authclient\BaseOAuth::api()`.
  Make sure you invoke those methods correctly. In case you are
  extending related classes, you should check, if overridden methods match parent declaration.

* Virtual property `yii\authclient\BaseOAuth::curlOptions` and related methods have been removed -
  use `yii\authclient\BaseOAuth::requestOptions` instead.

* Following methods have been removed: `yii\authclient\BaseOAuth::processResponse()`, `yii\authclient\BaseOAuth::apiInternal()`.
  Make sure you do not invoke them.

* Class `yii\authclient\InvalidResponseException` reworked: fields `responseHeaders` and `responseBody` have been removed,
  field `response` added instead holding `yii\httpclient\Response` instance, class constructor adjusted accordingly.
  Make sure you throw and process this exception correctly.

* Classes `yii\authclient\clients\GoogleOpenId` and `yii\authclient\clients\YandexOpenId` have been removed,
  since Google and Yandex no longer supports OpenID protocol. Make sure you do not use or refer these classes.

* Class `yii\authclient\clients\GoogleOAuth` has been renamed to `yii\authclient\clients\Google`.
  Make sure you are using correct name for this class.

* Class `yii\authclient\clients\YandexOAuth` has been renamed to `yii\authclient\clients\Yandex`.
  Make sure you are using correct name for this class.



