<?php
namespace yungouospay;

use yungouospay\exceptions\InvalidConfigException;
use yungouospay\exceptions\InvalidSignException;
use yungouospay\base\Unit;

class YunGouOsPay
{
	private $config = array(
		// 扫码支付
		"native_url"    	=> "https://api.pay.yungouos.com/api/pay/wxpay/nativeApi",
		// 公众号支付
		"jspay_url"     	=> "https://api.pay.yungouos.com/api/pay/wxpay/jsapi",
		// 小程序支付
		"minapp_pay_url"    => "https://api.pay.yungouos.com/api/pay/wxpay/minAppApi", 
		// 授权url
		"oauth_url"     	=> "https://api.pay.yungouos.com/api/wxlogin/getOauthUrl",
		// 获取授权信息
		"get_oauth_url"		=> "https://api.pay.yungouos.com/api/wxlogin/getBaseOauthInfo",
		// 查询订单
		"query_order"   	=> "https://api.pay.yungouos.com/api/pay/wxpay/getWxPayOrderInfo",
		// 订单退款
		"refund_order" 		=> "https://api.pay.yungouos.com/api/pay/wxpay/refundOrder",
		// 查询退款结果
		"get_refund_result" => "https://api.pay.yungouos.com/api/pay/wxpay/getRefundResult",
		// 关闭订单
		"close_order" 		=> "https://api.pay.yungouos.com/api/pay/wxpay/closeOrder",
		// 撤销订单
		"reverse_order"	 	=> "https://api.pay.yungouos.com/api/pay/wxpay/reverseOrder",
		// 默认异步回调地址
		"notify_url" 		=> "",		
		// 默认同步地址
		"return_url" 		=> "",
	);

	// 错误信息
	private $errmsg = null;

	public function __construct($config = [])
	{
		$this->config = array_merge($this->config, $config);
		// 商户号
		if(empty($this->config["mch_id"]))
		{
			throw new InvalidConfigException("mch_id 参数错误, 微信支付商户号不能为空");
		}
		// 密钥
		if(empty($this->config["secret"]))
		{
			throw new InvalidConfigException("secret 参数错误, 商户密钥不能为空");
		}
	}

	/**
	 * 获取错误信息
	 */
	public function getError()
	{
		return $this->errmsg;
	}

	// 扫码支付
	public function native(array $order)
	{
		// 订单号
		$params['out_trade_no'] = !empty($order["out_trade_no"]) ? $order["out_trade_no"] : '';
		// 订单金额,单位：元
		$params['total_fee'] = !empty($order["total_fee"]) ? floatval($order["total_fee"]) : 0;
		// 商户号
		$params["mch_id"] = $this->config["mch_id"];
		// 商品描述
		$params['body'] = !empty($order["body"]) ? $order["body"] : '';
		if(empty($params["out_trade_no"]))
		{
			throw new InvalidConfigException("out_trade_no 参数错误, 订单号不能为空");
		}		
		if(empty($params["total_fee"]))
		{
			throw new InvalidConfigException("total_fee 参数错误, 订单金额不能为空");
		}
		if(empty($params["body"]))
		{
			throw new InvalidConfigException("body 参数错误, 商户描述不能为空");
		}
		// 数据签名, 非必填参数不参与签名
		$params['sign'] = Unit::SignArray($params, $this->config["secret"]);
		// 返回类型（1、返回微信原生的支付连接需要自行生成二维码；2、直接返回付款二维码地址，页面上展示即可。不填默认1 ）
		$params['type'] = !empty($order["type"]) ? $order["type"] : 1;
		// 附加数据
		$params['attach'] = !empty($order["attach"]) ? $order["attach"] : '';
		// 填写自己的回调地址
		$params['notify_url'] = !empty($order["notify_url"]) ? $order["notify_url"] : $this->config["notify_url"];
		// 同步地址（收银台模式才有效）。支付完毕后用户浏览器返回到该地址
		$params['return_url'] = !empty($order["return_url"]) ? $order["return_url"] : $this->config["return_url"];
	    $paramsStr = Unit::ToUrlParams($params);
		return Unit::CurlPost($this->config["native_url"], $paramsStr, true);
	}

	// 公众号支付
	public function jsapi(array $order)
	{
		// 订单号
		$params['out_trade_no'] = !empty($order["out_trade_no"]) ? $order["out_trade_no"] : '';
		// 订单金额,单位：元
		$params['total_fee'] = !empty($order["total_fee"]) ? floatval($order["total_fee"]) : 0;
		// 商户号
		$params["mch_id"] = $this->config["mch_id"];
		// 商品描述
		$params['body'] = !empty($order["body"]) ? $order["body"] : '';
		// openid
		$params["openId"] = $this->getOpenId();
		// 用户openId
		if(empty($params["openId"]))
		{
			throw new InvalidConfigException("openId 参数错误, openId不能为空");
		}
		if(empty($params["out_trade_no"]))
		{
			throw new InvalidConfigException("out_trade_no 参数错误, 订单号不能为空");
		}		
		if(empty($params["total_fee"]))
		{
			throw new InvalidConfigException("total_fee 参数错误, 订单金额不能为空");
		}
		if(empty($params["body"]))
		{
			throw new InvalidConfigException("body 参数错误, 商户描述不能为空");
		}
		// 数据签名, 非必填参数不参与签名
		$params['sign'] = Unit::SignArray($params, $this->config["secret"]);
		// 附加数据
		$params['attach'] = !empty($order["attach"]) ? $order["attach"] : '';
		// 填写自己的回调地址
		$params['notify_url'] = !empty($order["notify_url"]) ? $order["notify_url"] : $this->config["notify_url"];
		// 同步地址（收银台模式才有效）。支付完毕后用户浏览器返回到该地址
		$params['return_url'] = !empty($order["return_url"]) ? $order["return_url"] : $this->config["return_url"];
	    $paramsStr = Unit::ToUrlParams($params);
		return Unit::CurlPost($this->config["jspay_url"], $paramsStr, true);
	}

	/**
	 * 支付回调
	 * @anotherdate 2019-05-28T10:48:46+0800
	 * @param       boolean                  $callback [description]
	 * @return      [type]                             [description]
	 */
	public function notify($callback = false)
	{
		if($_POST)
		{
			$out_trade_no = !empty($_POST["outTradeNo"]) ? $_POST['outTradeNo'] : '';
			if(!empty($out_trade_no))
			{
				$params["code"] = $_POST["code"];
				$params["orderNo"] = $_POST["orderNo"];
				$params["outTradeNo"] = $_POST["outTradeNo"];
				$params["wxPayNo"] = $_POST["wxPayNo"];
				$params["money"] = $_POST["money"];
				$params["mchId"] = $_POST["mchId"];
				if($_POST["sign"] != Unit::SignArray($params, $this->config["secret"]))
				{
					$this->errmsg = "订单签名错误";
					return false;
				}
				// 查询订单状态
				if($result = $this->queryOrder($out_trade_no))
				{
					if(!$result)
					{
						$this->errmsg = "订单查询失败";
						return false;
					}
					// 查询成功
					if($result["code"] == 0)
					{
						if($callback !== false)
						{
				            $res = call_user_func($callback, $_POST);
				            if(false === $res)
				            {
								$this->errmsg = "订单确认失败";
				            	return false;
				            }
						}
						// 确认完成返回
						echo "SUCCESS";
						exit;
					}else{
						$this->errmsg = $result["msg"];
						return false;
					}
				}

			}

		}
	}

	/**
	 * 小程序支付
	 * 无法直接发起支付，只返回支付参数，将支付参数传给yungouos收银台小程序发起支付
	 * 文档地址 http://open.pay.yungouos.com/#/api/api/pay/wxpay/minPay
	 * @anotherdate 2019-05-21T14:33:42+0800
	 * @param       array                    $order 订单参数
	 * @return      [type]                          [description]
	 */
	public function minAppApi(array $order)
	{
		// 订单号
		$params['out_trade_no'] = !empty($order["out_trade_no"]) ? $order["out_trade_no"] : '';
		// 订单金额,单位：元
		$params['total_fee'] = !empty($order["total_fee"]) ? floatval($order["total_fee"]) : 0;
		// 商户号
		$params["mch_id"] = $this->config["mch_id"];
		// 商品描述
		$params['body'] = !empty($order["body"]) ? $order["body"] : '';

		if(empty($params["out_trade_no"]))
		{
			throw new InvalidConfigException("out_trade_no 参数错误, 订单号不能为空");
		}		
		if(empty($params["total_fee"]))
		{
			throw new InvalidConfigException("total_fee 参数错误, 订单金额不能为空");
		}
		if(empty($params["body"]))
		{
			throw new InvalidConfigException("body 参数错误, 商户描述不能为空");
		}
		// 数据签名, 非必填参数不参与签名
		$params['sign'] = Unit::SignArray($params, $this->config["secret"]);
		// 支付收银小程序页面顶部的title 可自定义品牌名称 不传默认为 “收银台” 如传递参数 “海底捞” 页面则显示 “海底捞-收银台”
		$params['title'] = !empty($order["title"]) ? $order["title"] : '';
		// 附加数据
		$params['attach'] = !empty($order["attach"]) ? $order["attach"] : '';
		// 填写自己的回调地址
		$params['notify_url'] = !empty($order["notify_url"]) ? $order["notify_url"] : $this->config["notify_url"];
	    // $paramsStr = Unit::ToUrlParams($params);
		// return Unit::CurlPost($this->config["minapp_pay_url"], $paramsStr, true);
	    return json_encode($params);
	}

	/**
	 * 订单查询
	 * @anotherdate 2019-05-21T14:16:53+0800
	 * @param       string                    $out_trade_no 商户订单号
	 */
	public function queryOrder($out_trade_no)
	{
		// 订单号
		$params['out_trade_no'] = !empty($out_trade_no) ? $out_trade_no : '';
		// 商户号
		$params["mch_id"] = $this->config["mch_id"];
		if(empty($params["out_trade_no"]))
		{
			throw new InvalidConfigException("out_trade_no 参数错误, 订单号不能为空");
		}
		$params['sign'] = Unit::SignArray($params, $this->config["secret"]);
	    $paramsStr = Unit::ToUrlParams($params);
	    $url = $this->config["query_order"].'?'.$paramsStr;
		return Unit::CurlGet($url, true);
	}

	/**
	 * 订单发起退款
	 * @anotherdate 2019-05-21T14:32:25+0800
	 * @param       [type]                   $out_trade_no 退款单号
	 * @param       [type]                   $money        退款金额
	 * @return      [type]                                 
	 */
	public function refundOrder($out_trade_no, $money)
	{
		// 订单号
		$params['out_trade_no'] = !empty($out_trade_no) ? $out_trade_no : '';
		// 退款金额
		$params['money'] = !empty($money) ? $money : 0;
		// 商户号
		$params["mch_id"] = $this->config["mch_id"];
		if(empty($params["out_trade_no"]))
		{
			throw new InvalidConfigException("out_trade_no 参数错误, 订单号不能为空");
		}
		if(empty($params["money"]))
		{
			throw new InvalidConfigException("money 参数错误, 退款金额不能为空");
		}
		$params['sign'] = Unit::SignArray($params, $this->config["secret"]);
	    $paramsStr = Unit::ToUrlParams($params);
		return Unit::CurlPost($this->config["refund_order"], $paramsStr, true);
	}

	/**
	 * 查询退款结果
	 * @anotherdate 2019-05-21T14:57:59+0800
	 * @param       [type]                   $refund_no 发起退款时所获得的单号
	 */
	public function getRefundResult($refund_no)
	{
		// 退款单号
		$params['refund_no'] = !empty($refund_no) ? $refund_no : '';
		// 商户号
		$params["mch_id"] = $this->config["mch_id"];
		if(empty($params["refund_no"]))
		{
			throw new InvalidConfigException("refund_no 参数错误, 退款单号不能为空");
		}
		$params['sign'] = Unit::SignArray($params, $this->config["secret"]);
	    $paramsStr = Unit::ToUrlParams($params);
	    $url = $this->config["get_refund_result"].'?'.$paramsStr;
		return Unit::CurlGet($url, true);
	}

	/**
	 * 关闭订单
	 * 对已经发起的订单进行关闭，订单如果已支付不能关闭。已支付订单需要关闭请使用撤销订单接口
	 * @anotherdate 2019-05-21T15:18:50+0800
	 * @param       string                    $out_trade_no 商户订单号
	 */
	public function closeOrder($out_trade_no)
	{
		// 订单号
		$params['out_trade_no'] = !empty($out_trade_no) ? $out_trade_no : '';
		// 商户号
		$params["mch_id"] = $this->config["mch_id"];
		if(empty($params["out_trade_no"]))
		{
			throw new InvalidConfigException("out_trade_no 参数错误, 订单号不能为空");
		}
		$params['sign'] = Unit::SignArray($params, $this->config["secret"]);
	    $paramsStr = Unit::ToUrlParams($params);
		return Unit::CurlPost($this->config["close_order"], $paramsStr, true);
	}

	/**
	 * 撤销订单
	 * @anotherdate 2019-05-21T15:35:31+0800
	 * @param       string                    $out_trade_no 商户订单号
	 */
	public function reverseOrder($out_trade_no)
	{
		// 订单号
		$params['out_trade_no'] = !empty($out_trade_no) ? $out_trade_no : '';
		// 商户号
		$params["mch_id"] = $this->config["mch_id"];
		if(empty($params["out_trade_no"]))
		{
			throw new InvalidConfigException("out_trade_no 参数错误, 订单号不能为空");
		}
		$params['sign'] = Unit::SignArray($params, $this->config["secret"]);
	    $paramsStr = Unit::ToUrlParams($params);
		return Unit::CurlPost($this->config["reverse_order"], $paramsStr, true);
	}

	/**
	 * 获取用户openid
	 * @anotherdate 2019-05-21T14:33:29+0800
	 */
	public function getOpenId()
	{
		if(empty($_GET["code"]))
		{
			$params = array();
			$params["url"] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$result = Unit::CurlPost($this->config["oauth_url"], $params, true);
			if($result["code"] === 0)
			{
				Header("Location: $result[data]");
				exit();
			}
		}else{
			//获取code码，以获取openid
		    $code = $_GET['code'];
		    $url = $this->config["get_oauth_url"]."?code=".$code;
		    $result = Unit::CurlGet($url, true);
		    // 授权成功
		    if($result["code"] == 0)
		    {
		    	return $result["data"]["openId"];
		    }else{
				throw new InvalidConfigException('获取openid失败，'.$result["msg"]);
		    }
		}
	}
}
