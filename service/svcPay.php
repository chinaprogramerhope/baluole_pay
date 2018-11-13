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
            Log::pay(__METHOD__ . ' invalid param');
            return conErrorCode::ERR_INVALID_PARAM;
        }

        $account = $param['account'];
        $amount = round($param['amount'], 2);
        $serverId = $param['serverid'];
        return clsPay::createOrder($serverId, $account, $amount);
    }

    /**
     * 支付回调 - 游戏服务端回调php后台
     * @param $param
     * @return int
     */
    public function callback($param) {
        Log::pay(__METHOD__ . ', ' . __LINE__ . ', param = ' . json_encode($param));

        if (!isset($param['content']) || !isset($param['Ali'])
            || !isset($param['ServerID'])) {
            Log::pay(__METHOD__ . ' invalid param');
            return conErrorCode::ERR_INVALID_PARAM;
        }

        $ali = $param['Ali'];
        $serverId = $param['ServerID'];
        $bills = $param['content'];

        if (!is_array($bills)) {
            Log::pay(__METHOD__ . ', ' . __LINE__ . ', invalid param, content wrong, '
                . ' type of content = ' . gettype($param['content'])
                . ', content = ' . $param['content']);
        }

        clsPay::callback($serverId, $ali, $bills);
        return conErrorCode::ERR_OK;
    }
}