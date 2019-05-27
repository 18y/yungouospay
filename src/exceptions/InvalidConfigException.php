<?php

namespace yungouospay\exceptions;

use Exception;

/**
 * 支付配置异常类
 */
class InvalidConfigException extends Exception{

	public function errorMessage()
	{
		return $this->getMessage();
	}
}