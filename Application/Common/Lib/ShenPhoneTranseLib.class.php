<?php


namespace Common\Lib;


use Think\Exception;
use Think\Log;


class ShenPhoneTranseLib extends BaseTransLib implements IPoolTranser
{

    
    const MID    = "H001";
    const APIORDER = 'http://118.123.244.180/api/receive_upstream_data/'; 





    public function order(&$pool, $notify_url)
    {

        $params = [
            'dealer_name'         => self::MID, //用户名
            'out_trade_id'       => $pool['order_id'],
            'phone'       => $pool['phone'], //所充话费的手机号码
            'channel'    => $pool['channel'], 
            'amount'    => strval(intval($pool['money']*100)), 
        ];

        $params['sign'] = $this->sign($params);
        $api = self::APIORDER;
        $res = sendJson($api, $params);
        LogApiQuery($api, $params, $res);
        $resData = json_decode($res, true);
        return $resData['status'] == "success";
    }

    public function query(&$poolOrder)
    {
        // TODO: Implement query() method.
    }

    /**
     * @param $request
     * @return array
     */
    public function notify(&$request)
    {
        $params=file_get_contents("php://input");
        Log::write($params);
        $params=json_decode($params,true);

        $data = [
            'order_id'         => $params['order_id'],
            'serial_number'    => $params['serial_number'],
            'mobile'           => $params['mobile'],
            'amount'           => $params['amount'],
            'order_status'     => $params['order_status'] ? "true" : "false"
        ];
        $sign = $this->sign($data);
        if ($sign !== $params['sign']) {
            Log::write("sign err: {$sign} !== {$params['sign']}");
            throw new Exception('sign error');
        }
        if(!$params['order_status']){//失败处理
            $pool = M('PoolOrder')->where(['order_id' => $params['order_id']])->find();
            $this->cache->Client()->zAdd('pool_phone_timeout', time(), $pool['id']);
            
        }else{
            return new ChannelNotifyData($params['order_id'], $params['serial_number'], '' );
        }
        

    }

    /**
     * sign
     * @param $params
     * @return string
     */
    protected function sign($params) {

        ksort($params);
        $str = "";
        // foreach ($params as $key => $val) {
        //     $str = $str . $key . "=" . $val . "&";
        // }

        $str = http_build_query($params);

        // $arr = preg_split('/(?<!^)(?!$)/u', rtrim($str,"&"));
        // foreach($arr as &$v){
        //     $temp = unpack('H*', $v);
        //     $v = base_convert($temp[1], 16, 2);
        //     unset($temp);
        // }

        return md5(rtrim($str,"&"));
    }

    public function notifySuccess()
    {
        return '{"callback_status":True}';
    }


}
