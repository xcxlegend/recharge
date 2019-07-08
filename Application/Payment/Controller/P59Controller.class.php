<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/5/12
 * Time: 11:58
 */

namespace Payment\Controller;

use Org\Util\P59Pay;

/**
 * Class P59Controller
 * @package Pay\Controller
 * @breif 云短信平台充值接口
 */
class P59Controller extends PaymentController
{

    private $appkey;
    private $appsecret;
    private $codesmap;


    public function __construct()
    {
        parent::__construct();
        $this->appkey = C('P59PAY.APPKEY');
        $this->appsecret = C('P59PAY.APPSECT');
        $this->codesmap = C('P59PAY.CODE_MAPPING');
    }

    // 处理下单
    /*
    public function Pay($array)
    {
        $orderid     = I("request.pay_orderid");
        $body        = I('request.pay_productname');
        $notifyurl   = $this->_site . 'Pay_P59_notifyurl.html'; //异步通知
        $callbackurl = $this->_site . 'Pay_P59_callbackurl.html'; //返回通知

        $params = [
            'total_fee' => $array['pay_amount'],
            'out_trade_no' => $orderid,
            'trade_type' => $array['pay_bankcode'],
        ];

        $P59Pay = new P59Pay($this->appkey, $this->appsecret, $notifyurl);
        $result = $P59Pay->unifiedOrder($params);

        if ($result['code'] != '2000') {
            $this->showmessage($result['message']);
            return;
        }
        /**
         * array (size=3)
        'code' => string '2000' (length=4)
        'message' => string '创建订单成功' (length=18)
        'data' =>
            array (size=4)
            'orderId' => string 'MP190512165924198101828394' (length=26)
            'orderNo' => string 'E2019051208592222773' (length=20)
            'url' => string 'https://qr.alipay.com/upx01649mpzhpzptqgai20ea' (length=46)
            'createTime' => string '2019-05-12 16:59:24' (length=19)
         //

        $p59Order =$result['data']['orderId'];
        $resUri = $result['data']['url'];

        $parameter = array(
            'code'         => $array['pay_bankcode_ori'], // 通道名称
            'title'        => 'P59',
            'exchange'     => 1, // 金额比例
            'gateway'      => '',
            'orderid'      => $p59Order,
            'out_trade_id' => $orderid,
            'body'         => $body,
            'channel'      => $array['channel'],
            'amount'       => $array['pay_amount'],
        );


        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);
        $this->showQRcode($resUri, $parameter, $array['pay_bankcode']);
        return;
    }*/

    // 处理提现

    /**
     * @param $wttlList
     * @param $pfaList
     * @return array
     */
    public function PaymentExec($wttlList, $pfaList){

        return ['status' => 0, 'msg' => '未处理提现'];
        $notifyurl   = $this->_site . 'Payment_P59_notifyurl.html'; //异步通知

        $P59Pay = new P59Pay($this->appkey, $this->appsecret, $notifyurl);
        $result = $P59Pay->subdf($wttlList);
        if ($result['code'] != '6000') {
            return ['status' => 0, 'msg' => $result['message']];
        }

        $return = ['status' => 1, 'msg' => "OK"];
        return $return;
    }


    public function notifyurl() {
        /**
         * appKey=test01&orderNo=373557108010& payMoney =30.0&status=4&sign=d8d4705a4aba9c38cdc7f714c5fc8a92
         * orderId
         */
        $response = $_POST;
        $P59Pay = new P59Pay($this->appkey, $this->appsecret, '');
        $result = $P59Pay->notifydf($response);
        if ($result) {
            if ($response['status'] == '4') {
                exit('ok');
            }
        } else {
            logResult('收到P59代付推送, 签名验证失败');
            logResult($response);
            exit('error:check sign Fail!');
        }

    }


}