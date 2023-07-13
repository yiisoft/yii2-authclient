<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\authclient;

use yii\base\Exception;

/**
 * InvalidResponseException represents an exception caused by a non-successful server response status code.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 * @see \yii\httpclient\Response::getIsOk()
 */
class InvalidResponseException extends Exception
{
    /**
     * @var \yii\httpclient\Response HTTP response instance.
     * @since 2.1
     */
    public $response;


    /**
     * Constructor.
     * @param \yii\httpclient\Response $response response body
     * @param string $message error message
     * @param int $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($response, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->response = $response;
        parent::__construct($message, $code, $previous);
    }
}
