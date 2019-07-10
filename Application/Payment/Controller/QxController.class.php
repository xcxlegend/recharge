<?php
/**
 * Created by PhpStorm.
 * User: win 10
 * Date: 2019/3/27
 * Time: 11:41
 */

namespace Payment\Controller;

/**
 * 支付代付测试
 *
 * Class JJPlusController
 * @package Payment\Controller
 */
class QxController extends PaymentController
{
    //代付状态
    const PAYMENT_SUBMIT_SUCCESS = 1; //处理中
    const PAYMENT_PAY_SUCCESS    = 2; //已打款
    const PAYMENT_PAY_FAILED     = 3; //已驳回
    const PAYMENT_PAY_UNKNOWN    = 4; //待确认


    public function __construct()
    {
        parent::__construct();
    }
	
	private function request_post($url = '', $param = '') {
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

    public function PaymentExec($data, $config)
    {
		$requestData = array(
            'mchid' => '10109',					#商户号
            'out_trade_no' => $data['orderid'],			#商户订单号
            'money' => $data['money'],		#金额
            'bankname' => $data['bankname'],	#开户行名称
            #'subbranch' => $data['bankzhiname'],	#支行名称
			'subbranch' => '福州分行',	#支行名称
            'accountname' => $data['bankfullname'],	#开户名
            'cardnumber' => $data['banknumber'],	#银行卡号
            'province' => $data['sheng'],	#省份
            'city' => $data['shi'],		#城市
			'pay_md5sign' => '',		#加密
			#'extends' => '',
        );

        ksort($requestData);
		$Md5key = 'deale5avahkiatt3bhz63t4ppzp86cmp';
        $signText = '';
		foreach ($requestData as $key => $val) {
            if($key == "pay_md5sign") continue;
			$signText = $signText . $key . "=" . $val . "&";
		}
		$signText .= "key=" . $Md5key;
		#$signText = str_replace("/r/n", '', $signText);
		$requestData['pay_md5sign'] = strtoupper(md5($signText));

        $url = 'https://www.quxiangpay.com/Payment_Dfpay_add.html';
        $parameters = '';
        foreach($requestData as $k => $v){
            $parameters .= $k.'='.$v.'&';
        }

        $parameters = rtrim($parameters, '&');

        $response = json_decode(curlPost($url, http_build_query($requestData)), true);

		if(empty($response))
        {
            $return = ['status' => self::PAYMENT_PAY_FAILED, 'msg' => "错误：服务不可用"];
        }
        else
        {
            if($response['status'] === 'success')
            {
                $return = ['status' => self::PAYMENT_SUBMIT_SUCCESS, 'msg' => '提交成功'];
            }
            else
            {
                $return = [
                    'status' => self::PAYMENT_PAY_FAILED,
                    'msg'    => "错误：{$response['status']}：{$response['msg']}"
                ];
            }
        }

        return $return;
    }

    public function PaymentQuery($data, $config)
    {
		$requestData = array(
            'mchid' => '10109',					#商户号
            'out_trade_no' => $data['orderid'],			#商户订单号
			'pay_md5sign' => '',		#加密
        );

        ksort($requestData);
		$Md5key = 'deale5avahkiatt3bhz63t4ppzp86cmp';
        $signText = '';
		foreach ($requestData as $key => $val) {
            if($key == "pay_md5sign") continue;
			$signText = $signText . $key . "=" . $val . "&";
		}
		$signText .= "key=" . $Md5key;
		$requestData['pay_md5sign'] = strtoupper(md5($signText));


        $url = 'https://www.quxiangpay.com/Payment_Dfpay_query.html';
        $response = json_decode(curlPost($url, http_build_query($requestData)), true);
		
		if(empty($response))
        {
            $return = ['status' => self::PAYMENT_PAY_FAILED, 'msg' => "错误：服务不可用"];
        }
        else
        {			
            if($response['status'] === 'success')
            {
				if($response['refCode'] === '1')
				{
					$return = ['status' => self::PAYMENT_PAY_SUCCESS, 'msg' => '成功'];
				}else if($response['refCode'] === '2')
				{
					$return = ['status' => self::PAYMENT_PAY_FAILED, 'msg' => '失败'];
				}else if($response['refCode'] === '3')
				{
					$return = ['status' => self::PAYMENT_SUBMIT_SUCCESS, 'msg' => '处理中'];
				}else if($response['refCode'] === '4')
				{
					$return = ['status' => self::PAYMENT_SUBMIT_SUCCESS, 'msg' => '待处理'];
				}else if($response['refCode'] === '5')
				{
					$return = ['status' => self::PAYMENT_PAY_FAILED, 'msg' => '审核驳回'];
				}else if($response['refCode'] === '6')
				{
					$return = ['status' => self::PAYMENT_SUBMIT_SUCCESS, 'msg' => '待审核'];
				}else if($response['refCode'] === '7')
				{
					$return = ['status' => self::PAYMENT_PAY_FAILED, 'msg' => '交易不存在'];
				}
            }
            else
            {
                $return = [
                    'status' => self::PAYMENT_PAY_FAILED,
                    'msg'    => "错误：{$response['status']}：{$response['msg']}"
                ];
            }
        }

        return $return;
    }

    public function notifyurl()
    {
        exit('ok');
    }


    /**
     * 规则是:按参数名称a-z排序,遇到空值的参数不参加签名。
     */
    private function _createSign($data, $key)
    {
        $jsonData = json_encode($data);

        return md5($jsonData . '|' . $key);
    }
}