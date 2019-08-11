<?php
return [
    'GROUP_ID' => [
        '4' => '普通商户',
        '5' => '普通代理商户',
        '6' => '中级代理商户',
        '7' => '高级代理商户',
    ],

    'RPC_POOL_PHONE' => 'http://127.0.0.1:8080',
    'RPC_POOL_PHONE_SECRET' => 'af63e960f62aeb43ae26931471bcbf2c',

    'RPC_PHONE_MID' => '2019828315',
    'RPC_PHONE_MKEY' => 'a854278887892da7e3dadad7d7ae34f7',

    'POOL_PHONE_TIMEOUT' => 10, // 号码匹配超时
    'POOL_PHONE_ORDER_WX_TIMEOUT' => 90, // 订单微信超时
    'POOL_PHONE_ORDER_ALI_TIMEOUT' => 240, // 订单支付宝超时

    'RECHARGE_API_SECRET' => 'ed5d1e382669ca27',

    'ARTICLE_GROUP_ID' => [
        '0' => '平台可见',
        '1' => '商户可见',
        '2' => '代理可见',
    ],
];
