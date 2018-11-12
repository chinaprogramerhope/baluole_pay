<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 11:48
 */

class clsPay {
    /**
     * 创建订单
     * @param $serverId
     * @param $account
     * @param $amount
     * @return int
     */
    public static function createOrder($serverId, $account, $amount) {
        // todo 防止短时间内大量重复发包

        // 获取支付地址
        $moneyLimit = 20 * 10000;
        $aliArr = daoPay::getAli($moneyLimit, 'JJ');

        // 随机选取一个支付宝账号
        $index = array_rand($aliArr, 1);
        $aliAddress = $aliArr[$index]['Ali'];
        $moneyNow = $aliArr[0]['Money']; // todo 为什么是0 不是index; 转为float
        Log::info(__METHOD__ . ', 获得支付宝账号: ' . $aliAddress . ', 余额: ' . $moneyNow);

        // 获取金额
//        $amount = getPayDec($amount); // todo

        // 获取支付url
        $payUrl = daoPay::getPayUrl($aliAddress, $amount); // todo 获取一条记录
        Log::info(__METHOD__ . ', payUrl: ' . $payUrl);

        // 生成订单id
        $orderId = self::getOrderIdByPrefix('JJ');

        // 生成订单
        $timeNow = date('Y-m-d H:i:s');
        $orderStatus = 0;
        $userId = 0; // todo delay 目前userId和gameId赋默认值
        $gameId = 0;
        return daoPay::insertOrder($orderId, $userId, $gameId, $account, $amount, $serverId, $aliAddress, $timeNow, $orderStatus);
    }

    public static function updateOrder() {

    }

    /**
     * 构造订单号(形如：xx_201811120553417833496026)
     * @return string
     */
    public static function getOrderIdByPrefix($prefix) {
        $orderIdMain = date('YmdHis') . rand(10000000, 99999999);
        // 订单号码主题长度
        $orderIdLen = strlen($orderIdMain);
        $orderIdSum = 0;
        for ($i = 0; $i < $orderIdLen; $i++) {
            $orderIdSum += (int)(substr($orderIdMain, $i, 1));
        }
        // 唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
        $orderId = $orderIdMain . str_pad((100 - $orderIdSum % 100) % 100, 2, '0', STR_PAD_LEFT);
        return $prefix . '_' . $orderId;
    }
}