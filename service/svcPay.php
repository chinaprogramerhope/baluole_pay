<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 11:48
 */

class svcPay {
    /**
     * 创建订单
     * @param $param
     * @return int
     */
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

    public function callback($param) {
        if (!isset($param['content']) || !isset($param['Ali'])
            || !isset($param['ServerID'])) {
            Log::error(__METHOD__ . ' invalid param');
            return conErrorCode::ERR_INVALID_PARAM;
        }

        $ali = $param['Ali'];
        $serverId = $param['ServerID'];
        $bills = json_decode($param['content'], true);

        if (!is_array($bills)) {
            Log::error(__METHOD__ . ', ' . __LINE__ . ', invalid param, content wrong, '
                . ' type of content = ' . gettype($param['content'])
                . ', content = ' . $param['content']);
        }

        return clsPay::callback($serverId, $ali, $bills);
    }
}