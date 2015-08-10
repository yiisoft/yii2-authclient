<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\widgets;

use yii\web\AssetBundle;

/**
 * AuthChoiceAsset is an asset bundle for [[AuthChoice]] widget.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class AuthChoiceStyleAsset extends AssetBundle
{
    public $css = [
        'authchoice.css',
    ];

    public function init()
    {
        $this->sourcePath = __DIR__."/../assets";
        parent::init();
    }
}