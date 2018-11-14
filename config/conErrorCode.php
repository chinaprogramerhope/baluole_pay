<?php
/**
 * Created by PhpStorm.
 * User: hjl
 * Date: 18-10-7
 * Time: 下午4:36
 */
class conErrorCode {
    const ERR_OK = 0; // 接口请求成功
    const ERR_SERVER = 1; // 服务端错误
    const ERR_CLIENT = 2; // 客户端错误

    const ERR_INVALID_PARAM = 3; // 参数错误

    // mysql
    const ERR_MYSQL_CONNECT_FAIL = 100; // mysql连接失败
    const ERR_MYSQL_EXCEPTION = 101; // mysql异常

    // redis
    const ERR_REDIS_CONNECT_FAIL = 200; // redis连接失败

    // pay
    const ERR_PAY_FAIL = 300; // 支付失败
    const ERR_PAY_TOO_FREQUENT = 301; // 支付(创建订单)太频繁

}