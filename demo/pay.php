<?php 
require_once __DIR__ . '/../vendor/autoload.php';  
header("Content-type: text/html; charset=utf-8");
ini_set('date.timezone','Asia/Shanghai');

use yungouospay\YunGouOsPay;

class Pay
{
	private $api;

	public $config  = array(
		"mch_id" => "你的mch_id",
		"secret" => "你的secret",
	);

	public function __construct()
	{
		$this->api = new YunGouOsPay($this->config);
	}

	/**
	 * 扫码支付
	 * @anotherdate 2019-05-21T11:17:01+0800
	 * @return      [type]                   [description]
	 */
	public function native()
	{
		try {
			$order = array();
			$order["out_trade_no"] = date("YmdHis");
			$order["total_fee"] = 0.01;
			$order["body"] = 'test';
			$order["type"] = 2;
			$result = $this->api->native($order);
			// 下单成功，得到支付链接
			if($result["code"] === 0)
			{
				// 利用生成二维码
				$img_url = $result["data"];
				echo '<img src="'.$img_url.'" alt="支付二维码">';
				exit();
			}else{
				echo $result["msg"];
			}

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	/**
	 * 公众号支付
	 * @anotherdate 2019-05-21T11:16:53+0800
	 * @return      [type]                   [description]
	 */
	public function jspay()
	{
		try {
			$order = array();
			$order["out_trade_no"] = date("YmdHis");
			$order["total_fee"] = 0.01;
			$order["body"] = 'test';
			$result = $this->api->jsapi($order);
			// 下单成功，得到支付参数
			if($result["code"] === 0)
			{
				// js 支付参数
				$jsApiParameters = $result["data"];
			    // 支付成功url
			    $success_url = "";
			    // 支付失败跳转
			    $error_url = "";
			    echo <<<EOT
			            <html>
			            <head>
			                <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
			                <meta name="viewport" content="width=device-width, initial-scale=1"/> 
			                <title>微信支付</title>
			            </head>
			            <body>
			            </body>
			            </html>
			            <script>
			            //调用微信JS api 支付
			            function jsApiCall()
			            {
			                WeixinJSBridge.invoke(
			                    'getBrandWCPayRequest',$jsApiParameters,
			                    function(res){
			                        WeixinJSBridge.log(res.err_msg);
			                        if(res.err_msg == "get_brand_wcpay_request:ok" ) {
			                            window.location.href = "$success_url";
			                        } else {
			                            alert('交易取消'+res.err_msg);
			                            window.location.href = "$error_url";
			                        }
			                    }
			                );
			            }
			             
			            function callpay()
			            {
			                if (typeof WeixinJSBridge == "undefined"){
			                    if( document.addEventListener ){
			                        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
			                    }else if (document.attachEvent){
			                        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
			                        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
			                    }
			                }else{
			                    jsApiCall();
			                }
			            }
			            callpay();
			            </script>
EOT;
			}else{
				echo $result["msg"];
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}	
	}

	/**
	 * 支付回调
	 * @anotherdate 2019-05-28T11:17:54+0800
	 * @return      [type]                   [description]
	 */
	public function notify()
	{
		try {
			$this->api->notify(function($order){
				return true; 
			});
			
		} catch (\Exception $e) {
			echo $e->getMessage();
		}		
	}

	/**
	 * 小程序支付
	 * 无法直接发起支付，只返回支付参数，将支付参数传给yungouos收银台小程序发起支付
	 * @anotherdate 2019-05-21T11:16:40+0800
	 * @return      [type]                   [description]
	 */
	public function minAppPay()
	{
		try {
			$order = array();
			$order["out_trade_no"] = date("YmdHis");
			$order["total_fee"] = 1;
			$order["body"] = 'test';
			// 品牌名称
			$order["title"] = '我的名称';
			$result = $this->api->minAppApi($order);
			// 下单成功，得到支付链接
			echo $result;

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	/**
	 * 订单查询
	 * @anotherdate 2019-05-21T14:12:58+0800
	 * @return      [type]                   [description]
	 */
	public function queryOrder()
	{
		try {
			$out_trade_no = "WA1558411699053696259";
			$result = $this->api->queryOrder($out_trade_no);
			// 下单成功，得到支付链接
			dump($result);
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	/**
	 * 订单退款
	 * @anotherdate 2019-05-21T14:39:36+0800
	 * @return      [type]                   [description]
	 */
	public function refundOrder()
	{
		try {
			$out_trade_no = "WA1558411699053696259";
			$money = 1;
			$result = $this->api->refundOrder($out_trade_no, $money);
			// 下单成功，得到支付链接
			dump($result);
		} catch (\Exception $e) {
			echo $e->getMessage();
		}	
	}

	/**
	 * 查询退款结果
	 * @anotherdate 2019-05-21T15:00:49+0800
	 * @return      [type]                   [description]
	 */
	public function getRefundResult()
	{
		try {
			$refund_no = "123";
			$result = $this->api->getRefundResult($refund_no);
			// 下单成功，得到支付链接
			dump($result);
		} catch (\Exception $e) {
			echo $e->getMessage();
		}	
	}

	/**
	 * 关闭订单
	 * @anotherdate 2019-05-21T15:31:46+0800
	 * @param       [type]                   $out_trade_no [description]
	 * @return      [type]                                 [description]
	 */
	public function closeOrder()
	{
		try {
			$out_trade_no = "WA1558411699053696259";
			$result = $this->api->closeOrder($out_trade_no);
			// 下单成功，得到支付链接
			dump($result);
		} catch (\Exception $e) {
			echo $e->getMessage();
		}	
	}

	/**
	 * 撤销订单
	 * 支付交易返回失败或支付系统超时，调用该接口撤销交易。如果此订单用户支付失败，微信支付系统会将此订单关闭；
	 * 如果用户支付成功，微信支付系统会将此订单资金退还给用户。 
	 * 注意：7天以内的交易单可调用撤销，其他正常支付的单如需实现相同功能请调用申请退款API。
	 * 提交支付交易后调用【查询订单API】，没有明确的支付结果再调用【撤销订单API】。
	 * 调用支付接口后请勿立即调用撤销订单API，建议支付后至少15s后再调用撤销订单接口。
	 * @anotherdate 2019-05-21T15:36:37+0800
	 * @return      [type]                   [description]
	 */
	public function reverseOrder()
	{
		try {
			$out_trade_no = "WA1558411699053696259";
			$result = $this->api->reverseOrder($out_trade_no);
			// 下单成功，得到支付链接
			dump($result);
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}
}

function dump($data)
{
	echo "<pre/>";
	var_dump($data);
}

$type = !empty($_GET["type"]) ? $_GET["type"] : '';
$api = new Pay;
switch ($type) {
	case 'native':
		$api->native();
		break;
	case 'jspay':
		$api->jspay();
		break;
	case 'notify':
		$api->notify();
		break;
	default:
		echo "请求有误";
		exit();
		break;
}
