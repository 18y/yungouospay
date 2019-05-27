<?php

namespace yungouospay\exceptions\base;

use Exception;

/**
 * 签名异常类
 */
class InvalidSignException extends Exception{

	public function errorMessage()
	{
		return $this->getMessage();
	}
}