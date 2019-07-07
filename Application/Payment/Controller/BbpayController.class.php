<?php
namespace Pay\Controller;

use Org\Util\WxH5Pay;

class BbpayController extends PayController
{
    public function __construct()
    {
        parent::__construct();
    }
	
	function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }
        
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        
        return $data;
    }

    public function Pay($array)
    {
		$orderid = I("request.pay_orderid");
        $body = I('request.pay_productname');
        $notifyurl = $this->_site . 'Pay_Bbpay_notifyurl.html'; //异步通知
        $callbackurl = $this->_site . 'Pay_Bbpay_callbackurl.html'; //返回通知

        $parameter = array(
            'code' => 'Bbpay', // 通道名称
            'title' => '微信扫码',
            'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid' => '',
            'out_trade_id' => $orderid,
            'body'=>$body,
            'channel'=>$array
        );

        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);

        $data = array(
            'pay_memberid' => '10382',
            'pay_bankcode' => '901',
            'pay_orderid' => $return['orderid'],
            'pay_notifyurl' => $notifyurl,
            'pay_callbackurl' => $callbackurl,
            'pay_amount' => $return['amount'],
            'pay_md5sign' => '',
			'pay_applydate' => date("Y-m-d H:i:s"),
        );

        ksort($data);
		$Md5key = 'qa7sgnz84siz2cbymhlzh8hl3lac0kwz';
        $signText = '';
		foreach ($data as $key => $val) {
            if($key == "pay_md5sign") continue;
			$signText = $signText . $key . "=" . $val . "&";
		}
		$signText .= "key=" . $Md5key;
		$data['pay_md5sign'] = strtoupper(md5($signText));


        $url = 'https://kkapi.kuai168.cc/Pay_Index.html';
        $parameters = '';
        foreach($data as $k => $v){
            $parameters .= $k.'='.$v.'&';
        }

        $parameters = rtrim($parameters, '&');

        #header('Location: '.$url.'?'.$parameters);
        #$res = $this->request_post($url, $parameters);
        $res = curlPost($url, http_build_query($data));
		print_r($res);
    }

	 public function topay() {
        $orderid = $_REQUEST["orderid"];
        if(!$orderid) {
            $this->showmessage("参数错误");
        }
        $order = M('order')->where(array('pay_orderid'=>$orderid))->find();
        if(empty($order)) {
            $this->showmessage("订单不存在");
        }
        if($order['pay_status'] != 0) {
            $this->showmessage("订单已支付");
        }  
        $notifyurl = $this->_site . 'Pay_Bbpay_notifyurl.html'; //异步通知
        $redirect_uri = $this->_site . 'Pay_Bbpay_callbackurl.html?orderid='.$orderid;
        $wxwapPay = new WxH5Pay($order["account"], $order["memberid"], $notifyurl, $order["key"]);
        $params['body'] = "商城订单";                    //商品描述
        $params['out_trade_no'] = $orderid;    //自定义的订单号
        $params['total_fee'] = $order['pay_amount']*100;        //订单金额 只能为整数 单位为分
        $params['trade_type'] = 'MWEB';                  //交易类型 JSAPI | NATIVE | APP | WAP
        $params['scene_info'] = '{"h5_info": {"type":"Wap","wap_url": "h'.$this->site.'","wap_name": "'."商城订单".'"}}';
        $result = $wxwapPay->unifiedOrder( $params );
        $url = $result['mweb_url'].'&redirect_url='.$redirect_uri;
        redirect($url);
        exit();
    }
	
    public function callbackurl()
    {
        $Order = M("Order");
        $pay_status = $Order->where(['pay_orderid' => $_REQUEST["out_trade_no"]])->getField("pay_status");
        if($pay_status <> 0){
            $this->EditMoney($data['out_trade_no'], 'Bbpay', 1);

            exit('交易成功！');
        }else{
            exit("error");
        }
    }

    // 服务器点对点返回
    public function notifyurl()
    {
        $data = array(
            'memberid' => !isset($_REQUEST['memberid']) ? '' : trim($_REQUEST['memberid']), // 商户ID
            'orderid' => !isset($_REQUEST['orderid']) ? '' : trim($_REQUEST['orderid']), // 订单号
            'amount' => !isset($_REQUEST['amount']) ? '' : trim($_REQUEST['amount']), // 交易金额
            'datetime' => !isset($_REQUEST['datetime']) ? '' : trim($_REQUEST['datetime']), // 交易时间
            'transaction_id' => !isset($_REQUEST['transaction_id']) ? '' : trim($_REQUEST['transaction_id']), // 支付流水号
            'returncode' => !isset($_REQUEST['returncode']) ? '' : trim($_REQUEST['returncode']),
        );

        if($data['returncode'] != '00') exit('trade fail');

        ksort($data);
		$Md5key = 'qa7sgnz84siz2cbymhlzh8hl3lac0kwz';
        $signText = '';
		foreach ($data as $key => $val) {
            if($key == "pay_md5sign") continue;
			$signText = $signText . $key . "=" . $val . "&";
		}
		$signText .= "key=" . $Md5key;
		$sign = strtoupper(md5($signText));

        #if($sign != strtolower($data['sign'])) exit('sign err');

        $this->EditMoney($data['orderid'], 'Bbpay', 0);

        exit("OK");
    }
}

?>
