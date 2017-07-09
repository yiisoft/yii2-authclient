<?php

namespace yii\authclient\clients;

use Yii;
use yii\helpers\Url;
use yii\authclient\OAuth2;
use yii\base\ErrorException;

/**
 * Slack allows authentication via Slack OAuth.
 *
 * In order to use Slack OAuth you must register your application at <https://api.slack.com/apps/YOUR_APP/oauth>.
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'slack' => [
 *                 'class' => 'yii\authclient\clients\Slack',
 *                 'clientId' => 'slack_client_id',
 *                 'clientSecret' => 'slack_client_secret',
 *                 'scope' => 'identity.basic,identity.email,identity.team,identity.avatar',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ```
 *
 * @see http://developer.slack.com/v3/oauth/
 * @see https://slack.com/settings/applications/new
 *
 * @author Mir Adnan <contact@miradnan.com>
 * @since 2.0
 */
class Slack extends OAuth2 {

    /**
     * @inheritdoc
     */
    public $authUrl = 'https://slack.com/oauth/authorize';

    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://slack.com/api/oauth.access';

    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://slack.com';

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        if ($this->scope === null) {
            $this->scope = 'identity.basic';
        }

        // https://api.slack.com/docs/sign-in-with-slack
        $scopes = explode(',', $this->scope);
        if (!in_array('identity.basic', $scopes)) {
            throw new ErrorException("If you're just logging users in, set this to identity.basic. You can't ask for identity.email, identity.team, or identity.avatar without also asking for identity.basic.");
        }
    }

    /**
     * 
     * @param array $params
     * @return type
     */
    public function buildAuthUrl(array $params = array()) {
        $params['state'] = 'login';
        return parent::buildAuthUrl($params);
    }

    /**
     * @inheritdoc
     * @return type
     * @throws \yii\base\InvalidConfigException
     */
    protected function initUserAttributes() {
        $params = $$this->getAccessToken()->getParams();

        if (!$params['ok']) {
            throw new \yii\base\InvalidConfigException("Invalid Slack Configuration");
        }

        return $params;
    }

    /**
     * 
     * @return type
     */
    protected function defaultReturnUrl() {
        $url = parent::defaultReturnUrl();
        $url = str_replace('&state=login', '', $url);
        return $url;
    }

    /**
     * @inheritdoc
     */
    protected function defaultName() {
        return 'slack';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle() {
        return 'Slack';
    }

}
