<?php


namespace Pay\Controller;


use Common\Lib\TranserManager;
use Think\Exception;
use \Think\Log;

class TransController extends OrderController
{
    /**
     * 处理回调
     */
    public function Notify()
    {
        $method = $this->request['Method'];
        $channel = M('Channel')->where(['code' => $method])->find();
        try{
            $manager = new TranserManager($channel);
            $res = $manager->notify($this->request);

            $resParams=file_get_contents("php://input");
            $resParams=json_decode($resParams,true);

            $order_id = $resParams['order_id'];
            $order = D('PoolOrder')->where(['order_id' => $order_id])->find();
            if (!$order) {
                throw new Exception("no order");
                return;
            }
            $provider = D('PoolProvider')->find($order['pid']);
            if (!$provider) {
                throw new Exception("no provider");
                return;
            }
            $pool = D('PoolPhones')->find($order['pool_id']);
            if (!$pool) {
                throw new Exception("no pool phones");
                return;
            }

            if ($res) {
               
                if($order['status']!=1){
                    $this->handlePoolOrderSuccess($pool, $provider,$res->trans_id);
                    D('PoolOrder')->where(['id' => $order['id']])->setField([
                        'status' => 1,
                        'finish_time' => $this->timestamp,
                        'trans_id' => $res->trans_id
                    ]);
                }
                $res = $manager->notifySuccess();
                $this->log($res);
                echo $res;
                return;
                $this->result_success('');
            }else{

                //号码商通知
                $params = [
                    'appkey'        => $provider['appkey'],
                    'phone'         => $pool['phone'],
                    'money'         => intval($pool['money'] * 100),
                    'out_trade_id'  => $pool['out_trade_id'],
                    'status'        => -2,
                ];
        
                $sign = $this->createSign($provider['appsecret'], $params);
                $params["sign"] = $sign;
                $params['trans_id'] = $res->trans_id;
        
                $contents = sendForm($pool['notify_url'], $params);
        
                Log::write(" pool notify faild: ". $order["order_id"] . " url: " . $pool["notify_url"] . http_build_query($params) . " resp: " . $contents);
                M('PoolPhones')->where(['id' => $pool['id']])->delete();

                $res = $manager->notifySuccess();
                echo $res;
            }
        } catch (Exception $e) {
            $this->result_error($e->getMessage());
            exit;
        }
    }


}