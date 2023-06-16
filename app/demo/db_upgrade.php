<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-20 11:44:58
 * @LastEditors: light
 * @LastEditTime: 2023-04-13 10:29:02
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
defined('SUN_IN') or exit('Sunphp Access Denied');

/* 表格升级示例 */

/* 判断表中字段是否存在 */
if (!pdo_fieldexists('sun_demo_test', 'type')) {
  pdo_run("ALTER TABLE `sun_demo_test`
  ADD COLUMN `type` varchar(50) DEFAULT NULL AFTER `wechat`,
  ADD COLUMN `edition` varchar(255) DEFAULT NULL AFTER `type`;
  ");
}

/* 判断表格是否存在 */
if(!pdo_tableexists('sun_demo_new')){
  pdo_run("CREATE TABLE `sun_demo_new` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `des` varchar(255) DEFAULT NULL COMMENT '描述',
    PRIMARY KEY (`id`)
  )
  ");
}