<?php
/**
 * Created by PhpStorm.
 * User: hjl
 * Date: 18-10-7
 * Time: 下午3:36
 */
date_default_timezone_set('Asia/shanghai');

require 'autoload.php';

$ret = [ // 返回值标准格式, 支持只返回其中一个
    'errCode' => conErrorCode::ERR_OK,
    'data' => []
];

if (!isset($_REQUEST['svc']) || !isset($_REQUEST['func'])) {
    Log::error(basename(__FILE__) . ', ' . __LINE__ . ', invalid param, param = ' . json_encode($_REQUEST));
    $ret['errCode'] = conErrorCode::ERR_CLIENT;
    echo json_encode($ret);
    ob_flush();
    exit();
}

if (isset($_REQUEST['param']) && !is_array($_REQUEST['param'])) {
    Log::error(basename(__FILE__) . ', ' . __LINE__ . ', invalid param, param is not array, 
        param = ' . json_encode($_REQUEST));
    $ret['errCode'] = conErrorCode::ERR_CLIENT;
    echo json_encode($ret);
    ob_flush();
    exit();
}

$_REQUEST['param'] = isset($_REQUEST['param']) ? $_REQUEST['param'] : $_REQUEST['param'];

$ret = (new $_REQUEST['svc'])->{$_REQUEST['func']}($_REQUEST['param']);

if (!is_array($ret)) {
    if (is_int($ret)) { // 只返回errCode
        $ret = [
            'errCode' => $ret,
            'data' => []
        ];
    } else {
        $ret = [
            'errCode' => conErrorCode::ERR_SERVER,
            'data' => []
        ];
    }

    Log::debug(basename(__FILE__) . ', ' . __LINE__ . ', ret = ' . json_encode($ret));

    echo json_encode($ret);
    ob_flush();
    exit();
}

if (!isset($ret['errCode'])) { // errCode和data都没返回
    if (empty($ret) || !is_array($ret)) {
        $ret = [
            'errCode' => conErrorCode::ERR_SERVER,
            'data' => []
        ];
    } else {
        $data = $ret;
        $ret = [
            'errCode' => conErrorCode::ERR_OK,
            'data' => $data
        ];
    }

    Log::debug(basename(__FILE__) . ', ' . __LINE__ . ', ret = ' . json_encode($ret));
    echo json_encode($ret);
    ob_flush();
    exit();
}

Log::debug(basename(__FILE__) . ', ' . __LINE__ . ', ret = ' . json_encode($ret));
echo json_encode($ret);
ob_flush();
exit();
