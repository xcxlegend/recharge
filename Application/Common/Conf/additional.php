<?php
return [
    'GROUP_ID'=>[
        '4'=>'普通商户',
        '5'=>'普通代理商户',
        '6'=>'中级代理商户',
        '7'=>'高级代理商户',
    ],

    'RPC_POOL_PHONE' => 'http://127.0.0.1:8080',
    'RPC_POOL_PHONE_SECRET' => 'af63e960f62aeb43ae26931471bcbf2c',

    'RPC_PHONE_MID'  => '',
    'RPC_PHONE_MKEY' => '',

    'ARTICLE_GROUP_ID'=>[
        '0'=>'平台可见',
        '1'=>'商户可见',
        '2'=>'代理可见',
    ],
    /**
        901	微信H5
        902 微信扫码
        903	支付宝扫码
        904	支付宝H5
     */
    'P59PAY' => [
        'CODE_MAPPING' => [
            '901' => 1005,
            '902' => 1003,
            '903' => 1002,
            '904' => 1007,
        ],
        'APPKEY'  => 'YlLPzfT3ij',
        'APPSECT' => 'tGnAwMqpf8ANaPryblDM',
    ],

    'P361ZF' => [
        'CODE_MAPPING' => [
            '901' => 2004,
            '902' => 1004,
            '903' => 992,
            '904' => 2992,
        ],
        'APPKEY'  => '188902',
        'APPSECT' => '94440afbb3fd4016b996d4b02ef6d30d',
    ],
    'PHaitong' => [
        'APPKEY'  => '11011',
        'APPSECT' => '3d8efd9f56fcde403798babbcbaba833001f01d0',
        'CODE_MAPPING' => [
            '901' => 'wechat',
            '902' => 'weixin',
            '903' => 'alipaywap',
            '904' => 'alipay',
        ]
    ]

];