<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/6/15
 * Time: 10:18
 */

namespace Pay\Controller;


class PayController
{

    public function __construct()
    {

    }

    protected function result( $data ) {
        echo json_encode($data);
        exit;
    }


    protected function result_error( $info ) {
        $data = [
            'status'=> 1,
            "info"  => $info,
        ];
        $this->result($data);
    }

    protected function result_success( $param, $info = "ok") {
        $data = [
            'status' => 0,
            'info'   => $info,
            'data'   => $param
        ];
        $this->result($data);
    }


}