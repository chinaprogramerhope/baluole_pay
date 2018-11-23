<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/13
 * Time: 14:33
 */
require_once '../tool/Http.php';

$url = 'http://127.0.0.1:80/';
$param = [
    'svc' => 'svcPay',
    'func' => 'callback',
    'param' => [
        'ServerID' => 1,
        'Ali' => 'test_ali1',
        'content' => [
            [
                'time' => time(),
                'money' => 100.00
            ],
            [
                'time' => time(),
                'money' => 100.00
            ]
        ]
    ]
];

Http::curlPost($url, $param);