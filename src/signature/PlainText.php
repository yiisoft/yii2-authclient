<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\authclient\signature;

/**
 * PlainText represents 'PLAINTEXT' signature method.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class PlainText extends BaseMethod
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'PLAINTEXT';
    }

    /**
     * {@inheritdoc}
     */
    public function generateSignature($baseString, $key)
    {
        return $key;
    }
}
