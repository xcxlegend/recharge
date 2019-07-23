<?php

/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/18
 * Time: 20:45
 */

namespace Pay\Controller;

use \Think\Log;
use Common\Model\RedisCacheModel;


class PoolController extends PayController
{


    public function __construct()
    {
        parent::__construct();
    }

    public function Index()
    {

        /*
     *
     * phone
money
notify_url
channel
out_trade_id
     *
     *  appkey: 提供给商户的身份标识appkey 16位
        phone: 电话号码
        money: 金额 (单位分)
        out_trade_id: 商户系统的订单ID 用于回调
        notify_url:  回调地址 用于充值完成后回调
        channel: 手机对应运营商 1=移动 2=电信 3=联调
        sign: 签名  签名方案按照 k=v&k2=v2 k升序形式进行 最后并上  &key=APIKEY (APIKEY为分配给商户的密钥)  做md5 转小写.  具体查看后面的签名说明
     */

        // 增加地区判断参数 province_code area_code

        if (
            !$this->request['appkey']
            ||  !$this->request['phone']
            ||  !$this->request['money']
            ||  !$this->request['out_trade_id']
            ||  !$this->request['notify_url']
            ||  !$this->request['channel']
            ||  !$this->request['sign']
            ||  !$this->request['province_code']
            ||  !$this->request['area_code']
        ) {
            $this->result_error("param error", true);
            return;
        }

        $provider = M('PoolProvider')->where(['appkey' => $this->request['appkey']])->find();
        if (!$provider) {
            $this->result_error("no provider", true);
            return;
        }

        if (!$provider['status']) {
            $this->result_error("通道关闭", true);
            return;
        }

        $signArray = [
            "appkey"            => $this->request['appkey'],
            "phone"             => $this->request['phone'],
            "money"             => $this->request['money'],
            "out_trade_id"      => $this->request['out_trade_id'],
            "notify_url"        => $this->request['notify_url'],
            "channel"           => $this->request['channel'],
            "province_code"     => $this->request['province_code'],
            "area_code"         => $this->request['area_code'],
        ];

        $sign = createSign($provider['appsecret'], $signArray);
        if ($sign != $this->request['sign']) {
            $this->result_error("sign error", $sign);
            //            Log::write("sign:" . $sign);
            return;
        }


        // #TODO 增加号码商地区判断
        $codes = M('PhoneCode')->where(['code' => ['in', [$this->request['province_code'], $this->request['area_code']]]])->limit(2)->select();
        $useCode = null;
        foreach ($codes as $key => $code) {
            if ($code['status'] == 2) {
                continue;
            }
            if ($code['level'] == 0 && !$useCode) {
                $useCode = $code;
            } else {
                $useCode = $code;
            }
        }
        if (!$useCode) {
            // 没有找到可用的编码
            $this->result_error("没有可充值的地区标识");
            return;
        }

        if (M('PoolPhones')->where(['out_trade_id' => $this->request['out_trade_id'], 'pid' => $provider['id']])->count()) {
            $this->result_error("out_trade_id exist", $sign);
            return;
        }

        $data = $signArray;
        unset($data['appkey']);
        $data['pid'] = $provider['id'];
        $data['order_id'] = createUUID('PL');
        $data['time'] = $this->timestamp;
        $data['money'] = floatval($data['money'] / 100);
        $data['phone_code'] = $useCode['code'];

        // 创建失败回调的参数
        $this->createData($data, $provider);
        $result = M('PoolPhones')->add($data);
        if (!$result) {
            $this->result_error("save db error", true);
            Log::write(json_encode(M('PoolPhones')));
            return;
        }
        $data['id'] = $result;

        $this->setTimeout($data);

        $this->result_success(
            [
                'order_id' => $data['order_id'],
            ],
            "创建成功"
        );
    }

    protected function setTimeout(&$data)
    {
        $cache = RedisCacheModel::instance();
        $key = 'pool_phone_timeout';
        $cache->Client()->zAdd($key, $this->timestamp + 30, $data['id']);
    }

    protected function createData(&$data, &$provider)
    {

        /*
            appkey:     提供给商户的身份标识appkey 16位
            phone:      电话号码
            money:      金额 (单位分)
            status:     1 表示成功
        */
        $param2 = $param = [
            'appkey'        => $provider['appkey'],
            'phone'         => $this->request['phone'],
            'money'         => $this->request['money'],
            'out_trade_id'  => $data['out_trade_id'],
            'status'        => -1,
        ];
        $param['sign'] = createSign($provider['appsecret'], $param);
        $query = http_build_query($param);

        $param2['status'] = -2;
        $param2['sign'] = createSign($provider['appsecret'], $param2);
        $query2 = http_build_query($param2);
        $config = [
            'query_timeout' => $query,
            'query_nopay'   => $query2
        ];
        $data['data'] = json_encode($config);
    }

    public function Query()
    {
        if (
            !$this->request['appkey']
            ||  !$this->request['out_trade_id']
            ||  !$this->request['sign']
        ) {
            $this->result_error("param error", true);
            return;
        }

        $provider = M('PoolProvider')->where(['appkey' => $this->request['appkey']])->find();
        if (!$provider) {
            $this->result_error("no provider", true);
            return;
        }

        $signArray = [
            "appkey"        => $this->request['appkey'],
            "out_trade_id"  => $this->request['out_trade_id'],
        ];

        $sign = createSign($provider['appsecret'], $signArray);
        if ($sign != $this->request['sign']) {
            $this->result_error("sign error", $sign);
            //            Log::write("sign:" . $sign, Log::WARN);
            return;
        }


        $rec = M('PoolRec')->where(['out_trade_id' => $this->request['out_trade_id'], 'pid' => $provider['id']])->find();
        if (!$rec) {
            $this->result_error("订单未支付");
            return;
        }

        $data = json_decode($rec['data'], true);
        unset($data['id']);
        unset($data['pid']);
        unset($data['notify_url']);
        unset($data['lock']);
        $data['time'] = $rec['time'];
        $data['money'] = intval($data['money'] * 100);
        $this->result_success($data, "查询成功");
        return;
    }



    public function QueryBalance()
    {

        if (
            !$this->request['appkey']
            ||  !$this->request['time']
            ||  !$this->request['sign']
        ) {
            $this->result_error("param error", true);
            return;
        }

        $provider = M('PoolProvider')->where(['appkey' => $this->request['appkey']])->find();
        if (!$provider) {
            $this->result_error("no provider", true);
            return;
        }

        $signArray = [
            "appkey"    => $this->request['appkey'],
            "time"      => $this->request['time'],
        ];

        $sign = createSign($provider['appsecret'], $signArray);
        if ($sign != $this->request['sign']) {
            $this->result_error("sign error", $sign);
            return;
        }

        $data = [
            'time'      => $this->request['time'],
            'balance'   => number_format($provider['balance'], 2),
            'currency'  => 'RMB',
            'unit'      => '元',
        ];
        $this->result_success($data, "查询成功");
    }




    /* public function getPhone() {
        M()->startTrans();
        $phone = M('pool_phones')->lock(true)->where(['lock' => 0, 'money' => 1000])->find();
        print_r($phone);
        if ($phone) {
            $phone['lock'] = 1;
            M('pool_phones')->save($phone);
        }
        M()->commit();
    }*/
}
