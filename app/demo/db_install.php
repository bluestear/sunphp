<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-20 11:44:40
 * @LastEditors: light
 * @LastEditTime: 2023-04-13 10:31:05
 * @Description: SonLight Tech版权所有
 */

 declare(strict_types=1);
defined('SUN_IN') or exit('Sunphp Access Denied');

/* 安装应用时，安装数据表格示例 */

pdo_run("

drop table if exists sun_demo_test;
CREATE TABLE `sun_demo_test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wechat` varchar(255) DEFAULT NULL COMMENT '微信',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

");