<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 11:48
 */

class svcPay {
    public function createOrder($param) {
        if (!isset($param['account']) || !isset($param['amount'])
            || !isset($param['serverid'])) {
            Log::error(__METHOD__ . ' invalid param');
            return conErrorCode::ERR_INVALID_PARAM;
        }

        $account = $param['account'];
        $amount = round($param['amount'], 2);
        $serverId = $param['serverid'];
        return clsPay::createOrder($serverId, $account, $amount);
    }
}