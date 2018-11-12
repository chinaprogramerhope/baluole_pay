<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 12:14
 */

class daoPay {
    /**
     * 获取支付地址
     * @param $moneyLimit
     * @param $belong
     * @return array|int
     */
    public static function getAli($moneyLimit, $belong) {
        $pdo = clsMysql::getInstance();
        if (null === $pdo) {
            Log::error(__METHOD__ . ', ' . __LINE__ . ', mysql connect fail');
            return conErrorCode::ERR_MYSQL_CONNECT_FAIL;
        }

        try {
            $sql = 'select Ali, Money from KK_Ali where Money < :moneyLimit and Nulity = :nulity';
            $sql .= ' and Belong = :belong';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':moneyLimit' => $moneyLimit,
                ':nulity' => 0,
                ':belong' => $belong // todo
            ]);
            $rows = $stmt->fetchAll(); // todo 1条还是多条
            return $rows;
        } catch (PDOException $e) {
            Log::error(__METHOD__ . ', ' . __LINE__ . ', mysql exception: ' . $e->getMessage());
            return conErrorCode::ERR_MYSQL_EXCEPTION;
        }
    }

    /**
     * 获取支付url
     * @param $aliAddress
     * @param $money
     * @return array|int
     */
    public static function getPayUrl($aliAddress, $money) {
        $pdo = clsMysql::getInstance();
        if (null === $pdo) {
            Log::error(__METHOD__ . ', ' . __LINE__ . ', mysql connect fail');
            return conErrorCode::ERR_MYSQL_CONNECT_FAIL;
        }

        try {
            $sql = 'select PayUrl from KK_QRCode where Ali = :Ali and Money = :Money';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':Ali' => $aliAddress,
                ':Money' => $money,
            ]);
            $rows = $stmt->fetchAll(); // todo 1条还是多条
            return $rows;
        } catch (PDOException $e) {
            Log::error(__METHOD__ . ', ' . __LINE__ . ', mysql exception: ' . $e->getMessage());
            return conErrorCode::ERR_MYSQL_EXCEPTION;
        }
    }

    /**
     * 创建订单
     * @param $orderId
     * @param $userId
     * @param $gameId
     * @param $account
     * @param $money
     * @param $serverId
     * @param $aliPay
     * @param $applyDate
     * @param $orderStatus
     * @return int
     */
    public static function insertOrder($orderId, $userId, $gameId, $account, $money,
                                       $serverId, $aliPay, $applyDate, $orderStatus) {
        $pdo = clsMysql::getInstance();
        if (null === $pdo) {
            Log::error(__METHOD__ . ', ' . __LINE__ . ', mysql connect fail');
            return conErrorCode::ERR_MYSQL_CONNECT_FAIL;
        }

        try {
            $sql = 'insert into jj_payorder(OrderID, UserID, GameID, Account, decMoney, ServerID, AliPay, ApplyDate, OrderStatus)';
            $sql .= ' values (:OrderID, :UserID, :GameID, :Account, :decMoney, :ServerID, :AliPay, :ApplyDate, :OrderStatus)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':OrderID' => $orderId,
                ':UserID' => $userId,
                ':GameID' => $gameId,
                ':Account' => $account,
                ':decMoney' => $money,
                ':ServerID' => $serverId,
                ':AliPay' => $aliPay,
                ':ApplyDate' => $applyDate,
                ':OrderStatus' => $orderStatus,
            ]);
            $stmt->execute();
            return conErrorCode::ERR_OK;
        } catch (PDOException $e) {
            Log::error(__METHOD__ . ', ' . __LINE__ . ', mysql exception: ' . $e->getMessage());
            return conErrorCode::ERR_MYSQL_EXCEPTION;
        }
    }

    /**
     * 获取订单
     * @param $ali
     * @param $orderStatus
     * @param $payTime
     * @param $money
     * @param $activeTime
     * @return array|int
     */
    public static function getOrder($ali, $orderStatus, $payTime, $money, $activeTime) {
        $pdo = clsMysql::getInstance();
        if (null === $pdo) {
            Log::error(__METHOD__ . ', ' . __LINE__ . ', mysql connect fail');
            return conErrorCode::ERR_MYSQL_CONNECT_FAIL;
        }

        try { // todo limit 1
            $sql = 'select Account, OrderStatus, ApplyDate, OrderID, UserID, ServerID from';
            $sql .= ' jj_payorder where AliPay = :AliPay and OrderStatus = :OrderStatus and decMoney = :decMoney';
            $sql .= ' and timestampdiff(second, ApplyDate, :PayDate) < :activeTime';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':AliPay' => $ali,
                ':OrderStatus' => $orderStatus,
                ':decMoney' => $money,
                ':PayDate' => $payTime,
                ':activeTime' => $activeTime
            ]);
            $rows = $stmt->fetchAll();
            // todo 格式化返回值
            return $rows;
        } catch (PDOException $e) {
            Log::error(__METHOD__ . ', ' . __LINE__ . ', mysql exception: ' . $e->getMessage());
            return conErrorCode::ERR_MYSQL_EXCEPTION;
        }
    }
}