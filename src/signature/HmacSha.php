<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\authclient\signature;

use yii\base\NotSupportedException;

/**
 * HmacSha1 represents 'HMAC SHA' signature method.
 *
 * > **Note:** This class requires PHP "Hash" extension(<https://php.net/manual/en/book.hash.php>).
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.3
 */
class HmacSha extends BaseMethod
{
    /**
     * @var string hash algorithm, e.g. `sha1`, `sha256` and so on.
     * @see https://php.net/manual/ru/function.hash-algos.php
     */
    public $algorithm;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (!function_exists('hash_hmac')) {
            throw new NotSupportedException('PHP "Hash" extension is required.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'HMAC-' . strtoupper($this->algorithm);
    }

    /**
     * {@inheritdoc}
     */
    public function generateSignature($baseString, $key)
    {
        return base64_encode(hash_hmac($this->algorithm, $baseString, $key, true));
    }
}
