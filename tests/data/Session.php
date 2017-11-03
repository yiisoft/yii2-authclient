<?php

namespace yiiunit\extensions\authclient\data;

/**
 * Web session class mock.
 */
class Session extends \yii\web\Session
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // blank, override, preventing shutdown function registration
    }

    public function open()
    {
        // blank, override, preventing session start
    }
}