<?php
/**
 * Created by PhpStorm.
 * User: Legend.Xie
 * Date: 2019/7/18
 * Time: 19:30
 */

namespace Common\Model;
use Common\Lib\RedisCacheModel;

class RedisQueueModel
{
    protected $rds;
    
    function __construct(argument)
    {
        $this->rds = RedisCacheModel::instance();
    }

    public function TaskExportOrder( $request )
    {
        $payload = [
            'module'        => 'export',
            'key'           => 'export_order',
            'quest'         => json_encode($request),
            'create_time'   => time(),
        ];
        $this->rds->Client()->Lpush("queue", json_encode(['key' => 'task', 'payload' => json_encode($payload)]));
    }


}