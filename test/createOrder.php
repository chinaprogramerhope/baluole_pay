<?php
/**
 * Created by PhpStorm.
 * User: hjl
 * Date: 18-10-7
 * Time: 下午4:27
 */

require_once '../tool/Http.php';

$url = 'http://192.168.1.202:8888/';
$param = [
    'svc' => 'svcPay',
    'func' => 'createOrder',
    'param' => [
        'serverid' => 1,
        'account' => 'ok1',
        'amount' => 100
    ]
];

Http::curlPost($url, $param);
