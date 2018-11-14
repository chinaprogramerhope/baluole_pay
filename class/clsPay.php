<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 11:48
 *
 * todo 自增id为什么不是连续的
 *
 * todo callback中只是加金币, 更新订单状态；  还是需要更新后台和发邮件等
 *
 * todo 自动充值和手动（后台客服）充值
 */

class clsPay {
    const ORDER_STATUS_NEW = 0; // 新订单
    const ORDER_STATUS_PAY = 1; // 已支付订单

    const DISPATCH_COMMAND = 60002; // todo
    const DISPATCH_SERVER_IP = '192.168.1.219';
    const DISPATCH_SERVER_PORT = 10004;

    const enumAddScoreType_TableReward = 1;
    const enumAddScoreType_OnlineReward = 2;
    const enumAddScoreType_RouletteReward = 3;
    const enumAddScoreType_SlotsReward = 4;
    const enumAddScoreType_ZhaJinHuaXiQianReward = 5;
    const enumAddScoreType_UserBuy = 6;
    const enumAddScoreType_BackgroundAdd = 7;
    const enumAddScoreType_BackgroundSub = 8;
    const enumAddScoreType_BuySpeaker = 9;
    const enumAddScoreType_ServiceFee = 10;
    const enumAddScoreType_User_Disconnect = 11;
    const enumAddScoreType_Mission_Reward = 12;

    const enumResultSucc = 0;
    const enumResultFail = 1;

    /**
     * 创建订单
     * @param $serverId
     * @param $account
     * @param $amount
     * @return int
     */
    public static function createOrder($serverId, $account, $amount) {
        // 防止短时间内大量重复发包 todo 为什么只是用account做键
        $redis = clsRedis::getInstance();
        if (null === $redis) {
            Log::error(__METHOD__ . ', ' . __LINE__ . ', redis connect fail');
            return conErrorCode::ERR_REDIS_CONNECT_FAIL;
        }
        $key = conRedisKey::pay_create_order . $account;
        if ($redis->exists($key)) {
            Log::pay(__METHOD__ . ', ' . __LINE__ . ', too frequent!! serverId = '
                . $serverId . ', account = ' . $account . ', amount = ' . $amount);
            return conErrorCode::ERR_PAY_TOO_FREQUENT;
        } else {
            $ttl = 10;
            $redis->setex($key, $ttl, 1);
        }

        // 获取支付保账号
        $moneyLimit = 20 * 10000;
        $aliArr = daoPay::getAli($moneyLimit, 'JJ');
        // 随机选取一个支付宝账号
        $index = array_rand($aliArr, 1);
        $ali = $aliArr[$index]['Ali'];
        $moneyNow = $aliArr[0]['Money']; // todo 为什么是0 不是index; 转为float
        Log::pay(__METHOD__ . ', 获得支付宝账号: ' . $ali . ', 余额: ' . $moneyNow);

        // 获取支付url
        $payUrlArr = daoPay::getPayUrl($ali, $amount);
        $payUrl = $payUrlArr[0]['PayUrl'];
//        header('Location: ' . $payUrlArr); // todo 跳转, 打开支付宝
        Log::pay(__METHOD__ . ', payUrl: ' . $payUrl);

        // 生成订单id
        $orderId = self::getOrderIdByPrefix('JJ');

        // 生成订单
        $timeNow = date('Y-m-d H:i:s');
        $userId = 102453; // todo 测试时userId和gameId赋默认值
        $gameId = 0;
        return daoPay::insertOrder($orderId, $userId, $gameId, $account, $amount, $serverId, $ali,
            $timeNow, self::ORDER_STATUS_NEW);
    }

    /**
     * 回调
     * @param $serverId
     * @param $ali
     * @param $bills
     */
    public static function callback($serverId, $ali, $bills) {
        Log::pay(__METHOD__ . ', ' . __LINE__ . ', bills数量 = ' . count($bills));

        foreach ($bills as $k => $bill) {
            $payTime = $bill['time'];
            $money = $bill['money'];

            Log::pay(__METHOD__ . ', ' . __LINE__ . ', 编号 = ' . $k
                . ', bill = ' . json_encode($bill));

            $activeTime = 480; // 新订单有效时间480妙
            $orders = daoPay::getOrder($ali, self::ORDER_STATUS_NEW, $money); // notice 服务端认为实际上不会有多条
            if (empty($orders)) {
                Log::pay(__METHOD__ . ', ' . __LINE__ . ', 没有数据, 编号 = ' . $k
                    . ', bill = ' . json_encode($bill));
                continue;
            }

            // 获取订单参数
            Log::pay(__METHOD__ . ', ' . __LINE__ . ', 存在订单数据, 开始获取参数. k = ' . $k
                . ', bill = ' . json_encode($bill));

            // test todo 测试时取最新的一条
            $order = end($orders);
//            $order = $orders[0];
            $applyTime = $order['ApplyDate'];
            $orderId = $order['OrderID'];
            $userId = $order['UserID'];
            $account = $order['Account'];
            $serverId = $order['ServerID'];

            Log::pay(__METHOD__ . ', ' . __LINE__ . ', 有数据, 编号: ' . $k
                . ', order = ' . json_encode($order));

            // 检测支付时间
            $applyTs = strtotime($applyTime);
            if ($payTime - $applyTs > $activeTime) {
                Log::pay(__METHOD__ . ', ' . __LINE__ . ', pay too late, applyTime = '
                    . $applyTime . ', payTime = ' . date('Y-m-d H:i:s', $payTime));
                continue;
            }

            // 加金币
            $gold = ceil($money); // todo 测试期间1：1
            if (!self::addGold($userId, $gold)) {
                Log::pay(__METHOD__ . ', ' . __LINE__ . ', scoreOperation fail, userId = '
                    . $userId . ', gold = ' . $gold);
                continue;
            }

            Log::pay(__METHOD__ . ', ' . __LINE__ . ', addGold success!! orderId = ' . $orderId
                . ', userId = ' . $userId . ', gold = ' . $gold);

            // 更新订单状态
            $payTime = date('Y-m-d H:i:s', $payTime);
            if (daoPay::updateOrder($orderId, self::ORDER_STATUS_PAY, $payTime) !== true) {
                Log::pay(__METHOD__ . ', ' . __LINE__ . ', updateOrder fail, order = ' . json_encode($order));
                continue;
            }

            Log::pay(__METHOD__ . ', ' . __LINE__ . ', updateOrder success!! orderId = '
                . $orderId . ', orderStatus = ' . self::ORDER_STATUS_PAY . ', payTime = ' . $payTime);
        }
    }

    /**
     * 构造订单号(形如：xx_201811120553417833496026)
     * @param $prefix
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

    private static function orderSuccess($order, $thirdOrderNo = '') {
        if (empty($order)) {
            return false;
        }

        $orderId = $order['OrderID'];
        $orderStatus = intval($order['OrderStatus']);
        if ($orderStatus !== 0) {
            if ($orderStatus === 1) {
                Log::pay(__METHOD__ . ', ' . __LINE__ . ', order state success, 
                    orderId = ' . $orderId . ', order = ' . json_encode($order));
                return true;
            } else {
                Log::pay(__METHOD__ . ', ' . __FILE__ . ', order state error, orderId = '
                    . $orderId . ', order = ' . json_encode($order));
                return false;
            }
        }

        // 加金币
        $gold = intval($order['decMoney'] * 100); // todo 钱换算金币规则
        $userId = $order['UserID'];
        if (!self::addGold($userId, $gold, $order['game_code'])) { // todo smc_order没有game_code字段
            Log::pay(__METHOD__ . ', ' . __LINE__ . ', scoreOperation fail, userId = '
                . $userId . ', gold = ' . $gold);
            return false;
        }

        Log::pay(__METHOD__ . ', ' . __LINE__ . ', scoreOperation success!! userId = '
            . $userId . ', gold = ' . $gold);

//        // 更新后台订单表
//        $status = 1;
//        $paySuccessTime = time();
//        if (!self::updateSmcOrder($status, $paySuccessTime)) {
//            Log::pay(__METHOD__ . ', ' . __LINE__ . ', updateSmcOrder fail, status = '
//                . $status . ', paySuccessTime = ' . $paySuccessTime);
//            return false;
//        }
//
//        // 更新支付邮件表
//        $userDbIndex = self::getUserDBPos($userId);
//        if (!empty($userDbIndex)) {
//            self::insertSmcPayMail();
//        } else {
//            Log::pay(__METHOD__ . ', ' . __LINE__ . ', userDbIndex empty, userId = ' . $userId);
//        }
//
//        // 更新总支付金额 todo 原代码是更新redis  合适吗

        return true;
    }

    public static function getUserDBPos($userId) {
        $tmp = $userId & 0x00000000000000FF;
        $dbx = ($tmp & 0xF0) >> 4;
        $server = 'eus' . $dbx;
        $posx = $tmp & 0x0F;
        $db = '';
        $db->select('dbindex,tableindex');
        $db->from('CASINOUSER2ACCOUNT_' . $posx);
        $db->where('userid', $userId);
        $db->limit(1);
        $query = $db->get();
        $db->close();
        $userDbIndex = $query->row_array();
        if (empty ($userDbIndex)) {
            return false;
        }
        return $userDbIndex;
    }

    public static function insertSmcPayMail() {
        $db1 = " ( 'eus' . $user_db_index ['dbindex'], true )";
        $sql = "UPDATE CASINOUSER_" . $user_db_index ['tableindex'] . " SET totalBuy = totalBuy + '" . $gold . "' WHERE id = '" . $order ['user_id'] . "'";
        $db1->query($sql);
        $sqlTmp = "SELECT user_email,`password` from CASINOUSER_" . $user_db_index ['tableindex'] . " where id=" . $order ['user_id'];
        $queryTmp = $db1->query($sqlTmp);
        $rowTmp = $queryTmp->row_array();
        $db1->close();

        if ($rowTmp) {
            $dataTmp = array();
            $dataTmp['add_time'] = time();
            $dataTmp['user_id'] = $order ['user_id'];
            $dataTmp['user_email'] = $rowTmp['user_email'];
            $dataTmp['password'] = $rowTmp['password'];
            $dataTmp['money'] = $gold;
            $flagTmp = $db->insert('smc_pay_uemail', $dataTmp);
            Log::pay(__METHOD__ . ', ' . __LINE__ . ", flagTmp=$flagTmp,dataTmp=" . json_encode($dataTmp));
        }
    }

    public static function addGold($userId, $gold, $gameCode = '999990') {
        $gameCode = '999990';
        $scoreoper = new GameServerMiddleLayerServerScoreOperation();
        $scoreoper->set_userid($userId);
        $scoreoper->set_score($gold);
        $scoreoper->set_gameCode($gameCode);
        $scoreoper->set_addtype(self::enumAddScoreType_UserBuy); // todo

        $buf = $scoreoper->SerializeToString();

        // test
        Log::pay(__METHOD__ . ', ' . __LINE__ . ', ok13');

        $ret = self::requestMidlayerRes($buf, self::DISPATCH_COMMAND, self::DISPATCH_SERVER_IP, self::DISPATCH_SERVER_PORT);

        // test
        Log::pay(__METHOD__ . ', ' . __LINE__ . ', ok14');

        $rsp = new GameServerMiddleLayerServerScoreOperationRsp();
        $rsp->ParseFromString($ret);

        // test
        Log::pay(__METHOD__ . ', ' . __LINE__ . ', ok15');

        $r = $rsp->returncode() === self::enumResultSucc ? true : false;
        Log::pay(__METHOD__ . ', ' . __LINE__ . ', ' . date('Y-m-d H:i:s')
            . '--' . $userId . '--' . $gold . ': returncode:' . $rsp->returncode() . "\n");
        return $r;
    }

    /**
     * 请求中间层并解析响应
     * @param $buf
     * @param $command - 命令号
     * @param $host
     * @param $port
     * @return bool|null
     */
    public static function requestMidlayerRes($buf, $command, $host, $port) {
        //require_once(APPPATH . "third_party/proto/pb_proto_packet.php");
//        $this->_require('pb_proto_pbclientgameserver');
        // test
        Log::pay(__METHOD__ . ', ' . __LINE__ . ', buf = ' . $buf . ', json = ' . json_encode($buf));
        $pack = new Packet();
        $pack->set_version(0);
        $pack->set_command($command);
        $pack->set_connectionid("99");
        $pack->set_serialized($buf);
        $buf_pack = $pack->SerializeToString();

        $buf_length = sprintf('%08x', strlen($buf_pack));
        $buf_length = self::ntohl($buf_length);

        $request_stream = pack('H*', $buf_length) . $buf_pack;

        // todo 这是阻塞式socket吧, 因为显示设为非阻塞后连不上了
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die('Could not create socket');

        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 20, 'usec' => 0));
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 20, 'usec' => 0));

        $conn = socket_connect($socket, $host, $port);

        if (!$conn) {
            Log::pay(__METHOD__ . ', ' . __LINE__ . ', socket_connect fail, host = '
                . $host . ', port = ' . $port);
            return false;
        }

        // test
        Log::pay(__METHOD__ . ', ' . __LINE__ . ', req_stream = ' . $request_stream . ', json = ' . json_encode($request_stream));
        socket_write($socket, $request_stream);

        $read_length = socket_read($socket, 4);

        // test
        Log::pay('ok30, read_length = ' . json_encode($read_length));

        if (strlen($read_length) <= 0) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            Log::pay(__METHOD__ . ', ' . __LINE__ . ', errcode = ' . $errorcode
                . ', errmsg = ' . $errormsg);
            return false;
        }

        $read_length = unpack('H*', $read_length);
        $read_length = $read_length[1];
        $buf_length = base_convert(self::ntohl($read_length), 16, 10);
        $response_stream = socket_read($socket, $buf_length);

        $response_pack = new Packet();
        $response_pack->ParseFromString($response_stream);
        $ret = $response_pack->serialized();
        socket_close($socket);

        // test
        Log::pay('ok31, ret = ' . json_encode($ret));
        return $ret;
    }

    /**
     * 高低位字节序转换
     * @param $n
     * @return string
     */
    private static function ntohl($n) {
        $ret = substr($n, 6, 2) . substr($n, 4, 2) . substr($n, 2, 2) . substr($n, 0, 2);
        return $ret;
    }
}