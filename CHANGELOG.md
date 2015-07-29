Yii Framework 2 authclient extension Change Log
===============================================

2.0.5 under development
-----------------------

- Bug #25: `yii\authclient\BaseOAuth` now can be used without without `session` application component available (klimov-paul)
- Enh #27: Now allows sending HTTP requests via `file_get_contents()`, if not cURL extension. (hightman)


2.0.4 May 10, 2015
------------------

- Bug #7224: Fixed incorrect POST fields composition at `yii\authclient\OAuth1` (klimov-paul)
- Bug #7639: Automatic exception throw on 'error' key presence at `yii\authclient\BaseOAuth::processResponse()` removed (klimov-paul)
- Enh #17: Added `attributeNames` field to `yii\authclient\clients\VKontakte` and `yii\authclient\clients\LinkedIn`, which allows definition of attributes list fetched from API (klimov-paul)
- Enh #6743: Icon for Google at `yii\authclient\widgets\AuthChoice` fixed to follow the Google Brand guidelines (klimov-paul)
- Enh #7733: `yii\authclient\clients\VKontakte` now gets attributes from access token also (klimov-paul)
- Enh #7754: New client `yii\authclient\clients\GooglePlus` added to support Google recommended auth flow (klimov-paul)
- Chg: #7754: `yii\authclient\clients\GoogleOpenId` is now deprecated because this auth method is no longer supported by Google as of April 20, 2015 (klimov-paul)


2.0.3 March 01, 2015
--------------------

- Enh #6892: Default value of `yii\authclient\clients\Twitter::$authUrl` changed to 'authenticate', allowing usage of previous logged user without request an access (kotchuprik)


2.0.2 January 11, 2015
----------------------

- Bug #6502: Fixed `\yii\authclient\OAuth2::refreshAccessToken()` does not save fetched token (sebathi)
- Bug #6510: Fixed infinite redirect loop using default `\yii\authclient\AuthAction::cancelUrl` (klimov-paul)


2.0.1 December 07, 2014
-----------------------

- Bug #6000: Fixed CCS for `yii\authclient\widgets\AuthChoice` does not loaded if `popupMode` disabled (klimov-paul)


2.0.0 October 12, 2014
----------------------

- Enh #5135: Added ability to operate nested and complex attributes via `yii\authclient\BaseClient::normalizeUserAttributeMap` (zinzinday, klimov-paul)


2.0.0-rc September 27, 2014
---------------------------

- Bug #3633: OpenId return URL comparison advanced to prevent url encode problem (klimov-paul)
- Bug #4490: `yii\authclient\widgets\AuthChoice` does not preserve initial settings while opening popup (klimov-paul)
- Bug #5011: OAuth API Response with 20x status were not considered success (ychongsaytc)
- Enh #3416: VKontakte OAuth support added (klimov-paul)
- Enh #4076: Request HTTP headers argument added to `yii\authclient\BaseOAuth::api()` method (klimov-paul)
- Enh #4134: `yii\authclient\InvalidResponseException` added for tracking invalid remote server response (klimov-paul)
- Enh #4139: User attributes requesting at GoogleOAuth switched to Google+ API (klimov-paul)


2.0.0-beta April 13, 2014
-------------------------

- Initial release.
