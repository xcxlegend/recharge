<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/15
 * Time: 10:18
 */

namespace Pay\Controller;
use Common\Model\RedisCacheModel;


class PayController
{

    protected $_site;
    protected $timestamp;
    protected $request;
    protected $cache;

    public function __construct()
    {
        $this->timestamp = time();
        $this->_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';
        $this->request = I('request.');
        $this->cache = RedisCacheModel::instance();
    }

    protected function result( $data ) {
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }


    protected function result_error( $info , $with_log = false ) {
        $data = [
            'status'  => "error",
            "message" => $info,
        ];
        if ($with_log) {
            if ( !is_string($with_log) ){
                if (is_bool($with_log)) {
                    $with_log = $this->request;
                }
                $with_log = json_encode($with_log);
            }
            \Think\Log::write($info . ': ' . $with_log, \Think\Log::WARN);
        }
        $this->result($data);
    }

    protected function result_success( $param, $info = "ok") {
        $data = [
            'status' => "success",
            'message'   => $info,
            'data'   => $param
        ];
        $this->result($data);
    }


}