<?php
/**
 * Created by PhpStorm.
 * User: hjl
 * Date: 18-10-9
 * Time: 下午4:52
 */

class conConstant {
    // mysql连接配置
    const mysql_dsn = 'mysql:dbname=payment;host=192.168.1.119';
    const mysql_user = 'RoamGame';
    const mysql_password = 'Xmpx3hTpYujflCgbRkJV1';

    // redis连接配置
    const redis_ip = '127.0.0.1';
    const redis_port = 6379;

    const default_redis_expire_time = 604800; // redis键默认过期时间: 7天
}