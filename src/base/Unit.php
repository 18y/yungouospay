<?php
namespace yungouospay\base;

class Unit 
{
	/**
	 * 数据签名
	 * @anotherdate 2019-05-20T15:28:19+0800
	 * @param       array                    $params 支付参数
	 * @param       [type]                   $key    支付密钥
	 */
	public static function SignArray(array $params, $key)
	{
		ksort($params);
		$blankStr = self::ToUrlParams($params);
		$sign = strtoupper(md5(urldecode($blankStr).'&key='.$key));
		return $sign;
	}

	/**
	 * 拼接签名字符串
	 */
	public static function ToUrlParams(array $array)
	{
		$buff = "";
		foreach ($array as $k => $v)
		{
			if($v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}

	public static function CurlPost($url,$params, $json = false){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//如果不加验证,就设false,商户自行处理
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		 
		$output = curl_exec($ch);
		curl_close($ch);
		if($json)
		{
			return json_decode($output,true);
		}
		return  $output;
	}

	
	public static function CurlGet($url, $json = false)
	{
		//初始化curl
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
		//运行curl，结果以jason形式返回
		$output = curl_exec($ch);
		curl_close($ch);
		if($json)
		{
			return json_decode($output,true);
		}
		return $output;
	}
}