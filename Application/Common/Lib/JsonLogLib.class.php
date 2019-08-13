<?php

namespace Common\Lib;
use Think\Log;

class JsonLogLib extends Log
{
    static function write($message,$level=self::INFO,$type='',$destination='') {
        if(!self::$storage){
            $type 	= 	$type ? : C('LOG_TYPE');
            $class  =   'Think\\Log\\Driver\\'. ucwords($type);
            $config['log_path'] = C('LOG_PATH');
            self::$storage = new $class($config);
        }
        if(empty($destination)){
            $destination = C('LOG_PATH').date('y_m_d').'.log';
        }
        if (is_array($message)) {
            $message['level'] = $level;
            $message['timestamp'] = time();
            $message['datetime'] = date('Y-m-d H:i:s');
            self::$storage->write(json_encode($message, JSON_UNESCAPED_UNICODE), $destination);
        }
    }

}