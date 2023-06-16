<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-20 11:44:40
 * @LastEditors: light
 * @LastEditTime: 2023-03-20 16:15:45
 * @Description: SonLight Tech版权所有
 */

 declare(strict_types=1);
defined('SUN_IN') or exit('Sunphp Access Denied');

/* 用户卸载应用时候，表格卸载示例 */

pdo_run("

DROP TABLE IF EXISTS `sun_demo_test`;
DROP TABLE IF EXISTS `sun_demo_new`;

");