<?php


namespace Common\Lib;


use Think\Exception;
use Think\Log;

/**
 * Class P361PhoneTranseLib
 * @package Common\Lib
 */
class P361PhoneTranseLib extends BaseTransLib implements IPoolTranser
{

    const MID    = "188913";
    const SECRET = "ab7a5a6e5b6849fca7d59687a3c8e5c0";
    const CHANNELS = [
        '1' => 'YDCZ',
        '2' => 'LTCZ',
        '3' => 'DXCZ'
    ];
    const CTYPE = "1";

    const GATEWAY = 'http://open.361zf.com'; // 避免配置错误

    const API_ORDER = '/czapi/push';
    const API_QUERY = '';



    public function order(&$pool, $notify_url)
    {
        /**
         * 商户ID	mchno	N	Y	商户id，由平台分配
        商户系统订单号	orderno	N	Y	必须唯一
        充值手机	account	N	Y	手机号
        充值金额	amount	N	Y	充值金额，单位元
        充值产品编码	chanelcode	N	Y	YDCZ：移动充值
        DXCZ：电信充值
        LTCZ：联通充值
        充值产品类型	chaneltype	N	Y	1：手机
        2：流量
        3：固话
        省地区编码	pareacode	N	N	省地区编码
        市地区编码	careacode	N	N	市地区编码
        订单生成时间	ordertime	N	Y	格式：yyyyMMddHHmmss
        通知地址	callbackurl	N	Y	通过该地址通知商户订单状态
        MD5签名	sign	N	-	32位小写MD5签名值，UTF-8编码
        返回参数实例：
        {
        “status”: ”10000”,
        “msg”: “请求成功”
        }

         */

        $params = [
            'mchno'         => self::MID,
            'orderno'       => $pool['order_id'],
            'account'       => $pool['phone'],
            'amount'        => $pool['money'],
            'chanelcode'    => self::CHANNELS[$pool['channel']] ?: 'YDCZ',
            'chaneltype'    => self::CTYPE,
            'ordertime'     => date('YmdHis'),
            'callbackurl'   => $notify_url,
        ];

        $params['sign'] = $this->sign($params);
        $api = ($this->channel['gateway'] ?: self::GATEWAY) . self::API_ORDER;
        /*LogApiQuery($api, $params, 'test ok');
        return true;*/

        $res = sendForm($api, $params);

        LogApiQuery($api, $params, $res);
        $resData = json_decode($res, true);
        return $resData['status'] == "10000";
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
        /**
         * 商户ID	mchno	Y	商户id，由平台分配
        充值系统订单号	sysorderno	Y	平台生成订单号
        商户系统订单号	orderno	Y	必须唯一
        充值手机	account	Y	手机号
        充值金额	amount	Y	单位元，保留两位小数
        充值产品编码	chanelcode	Y	YDCZ：移动充值
        DXCZ：电信充值
        LTCZ：联通充值
        充值产品类型	chaneltype	Y	1：手机
        2：流量
        3：固话
        订单时间	ordertime	Y	格式：yyyyMMddHHmmss
        完成时间	dealtime	Y	格式：yyyyMMddHHmmss
        状态	status	Y	0：待匹配
        1：匹配成功
        2：支付超时
        3：支付成功
        -1：匹配失败
        -2：支付失败
        5：超时支付
        说明	msg	N	状态描述内容
        签名	sign	N	请验证签名
        流水号	serialno	N	运营商流水号
         */

        $data = [
            'mchno'         => $request['mchno'],
            'sysorderno'    => $request['sysorderno'],
            'orderno'       => $request['orderno'],
            'account'       => $request['account'],
            'amount'        => $request['amount'],
            'chanelcode'    => $request['chanelcode'],
            'chaneltype'    => $request['chaneltype'],
            'ordertime'     => $request['ordertime'],
            'dealtime'      => $request['dealtime'],
            'status'        => $request['status'],
        ];
        $sign = $this->sign($data);
        if ($sign !== $request['sign']) {
            Log::write("sign err: {$sign} !== {$request['sign']}");
            throw new Exception('sign error');
        }
        return new ChannelNotifyData($request['orderno'], $request['serialno'], '' );

    }

    /**
     * sign
     * @param $params
     * @return string
     */
    protected function sign($params) {
        $datas = $params;
        foreach ($datas as $key => $value) {
            if (empty($value)) {
                unset($datas[$key]);
            }
        }
        return createSign(self::SECRET, $datas);
    }

    public function notifySuccess()
    {
//        echo 'SUCCESS';
        return 'SUCCESS';
    }


}