/*
 Navicat Premium Data Transfer

 Source Server         : 127.0.0.1本地开发
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : 127.0.0.1:3306
 Source Schema         : sunphp

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 10/04/2023 16:34:17
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sun_core_account
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_account`;
CREATE TABLE `sun_core_account`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL COMMENT '平台类型',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台名称',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台头像',
  `appid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'appid',
  `secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'secret',
  `level` tinyint(1) DEFAULT NULL COMMENT '公众号级别',
  `end_time` datetime(0) NULL DEFAULT NULL COMMENT '到期时间',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注信息',
  `is_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0正常1回收站2删除',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sun_core_app
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_app`;
CREATE TABLE `sun_core_app`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dir` varchar(50) NOT NULL DEFAULT 'app' COMMENT '模块安装目录' ,
  `identity` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用唯一标识',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '应用名称',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '应用图标',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '自定义图标',
  `version` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '应用版本',
  `new_version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '扫描本地新版本',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '开发者名称',
  `admin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '管理后台地址',
  `cover` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '入口地址',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1框架应用2微擎应用',
  `is_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0正常1回收站2未安装',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `index_i` (`identity`) USING BTREE COMMENT '唯一标识'
) ENGINE = MyISAM AUTO_INCREMENT = 27 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sun_core_bindapp
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_bindapp`;
CREATE TABLE `sun_core_bindapp`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acid` int(11) NOT NULL COMMENT 'account平台id',
  `sid` int(11) NOT NULL COMMENT 'supports应用分支id',
  `order` int(11) NOT NULL DEFAULT 1 COMMENT '排序大靠前',
  `end_time` datetime(0) NULL DEFAULT NULL COMMENT '绑定app到期时间',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `index_aa`(`acid`, `sid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 22 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for sun_core_create
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_create`;
CREATE TABLE `sun_core_create`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `wx_gzh` int(11) NOT NULL DEFAULT 0 COMMENT '微信公众号',
  `wx_xcx` int(11) NOT NULL DEFAULT 0 COMMENT '微信小程序',
  `zjtd_xcx` int(11) NOT NULL DEFAULT 0 COMMENT '字节跳动小程序',
  `pc` int(11) NOT NULL DEFAULT 0 COMMENT 'PC网站',
  `app` int(11) NOT NULL DEFAULT 0 COMMENT 'APP应用',
  `zfb_xcx` int(11) NOT NULL DEFAULT 0 COMMENT '支付宝小程序',
  `bd_xcx` int(11) NOT NULL DEFAULT 0 COMMENT '百度小程序',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `index_u`(`uid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for sun_core_email
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_email`;
CREATE TABLE `sun_core_email`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acid` int(11) NOT NULL COMMENT '绑定的平台id',
  `email_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '发件人名称',
  `email_sender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '发件人邮箱',
  `email_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'smtp授权码',
  `email_smtp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'smtp服务器',
  `email_sign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '邮件末尾签名',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `index_acid`(`acid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sun_core_menu
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_menu`;
CREATE TABLE `sun_core_menu`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL COMMENT 'app应用id',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '菜单地址',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '菜单标题',
  `do` varchar(255) NULL ,
  `state` varchar(255) NULL ,
  `direct` varchar(255) NULL ,
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for sun_core_order
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_order`;
CREATE TABLE `sun_core_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) NOT NULL COMMENT '系统订单out_trade_no',
  `tid` varchar(255) NOT NULL COMMENT '模块订单号',
  `money` decimal(10,2) NOT NULL COMMENT '订单金额',
  `title` varchar(255) NOT NULL COMMENT '订单标题',
  `module` varchar(255) NOT NULL COMMENT '模块标识',
  `acid` int(11) NOT NULL COMMENT '平台id',
  `pay_acid` int(11) NOT NULL COMMENT '实际使用支付配置平台id',
  `pay_method` varchar(50) NOT NULL COMMENT '支付方式wechat/alipay/unipay',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0待回调1支付回调成功',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_o` (`order_id`) USING HASH
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


-- ----------------------------
-- Table structure for sun_core_pay
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_pay`;
CREATE TABLE `sun_core_pay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acid` int(11) NOT NULL COMMENT '绑定的平台id',
  `wx_mchid` varchar(50) DEFAULT NULL COMMENT '微信商户号',
  `wx_apikey` varchar(255) DEFAULT NULL COMMENT '微信支付api秘钥V3',
  `wx_cert` varchar(255) DEFAULT NULL COMMENT '微信商户私钥证书',
  `wx_public_cert` varchar(255) DEFAULT NULL COMMENT '微信商户公钥证书',
  `wechat_public_cert` varchar(255) DEFAULT NULL COMMENT '微信平台公钥证书',
  `wx_switch` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1启用0关闭',
  `ali_switch` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1启用0关闭',
  `ali_appid` varchar(50) DEFAULT NULL COMMENT '支付宝应用id',
  `ali_appkey` text COMMENT '支付宝应用私钥',
  `ali_app_cert` varchar(255) DEFAULT NULL COMMENT '支付宝应用私钥证书',
  `ali_public_cert` varchar(255) DEFAULT NULL COMMENT '支付宝公钥证书',
  `ali_root_cert` varchar(255) DEFAULT NULL COMMENT '支付宝根证书',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_acid` (`acid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for sun_core_sms
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_sms`;
CREATE TABLE `sun_core_sms`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acid` int(11) NOT NULL COMMENT '绑定的平台id',
  `type` tinyint(1) NULL DEFAULT NULL COMMENT '短信类型，默认关闭',
  `ali_sms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '阿里云短信',
  `tencent_sms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '腾讯云短信',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `index_acid`(`acid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sun_core_storage
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_storage`;
CREATE TABLE `sun_core_storage`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acid` int(11) NOT NULL COMMENT '绑定的平台id',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '存储类型，默认本地',
  `ali_oss` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '阿里云',
  `tencent_cos` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '腾讯云',
  `qiniu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '七牛云',
  `suffix` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '后缀名称限制',
  `img_size` int(11) NOT NULL DEFAULT 5000 COMMENT '图片大小限制',
  `video_size` int(11) NOT NULL DEFAULT 5000 COMMENT '音视频大小限制',
  `file_size` int(11) NOT NULL DEFAULT 5000 COMMENT '文件大小限制',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `index_acid`(`acid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sun_core_supports
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_supports`;
CREATE TABLE `sun_core_supports`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL COMMENT '应用id',
  `type` tinyint(1) NOT NULL COMMENT '支持的平台类型',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `index_at`(`app_id`, `type`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 123 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for sun_core_system
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_system`;
CREATE TABLE `sun_core_system`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sys_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '系统名称',
  `sys_logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '系统logo',
  `sys_version` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '系统版本',
  `sys_domain` varchar(255) DEFAULT NULL COMMENT '安装域名',
  `sys_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1普通2会员',
  `sys_mall` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1显示市场2隐藏',
  `sys_upgrade` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1显示升级2隐藏',
  `activate_key` varchar(255) DEFAULT NULL COMMENT '高级版激活秘钥',
  `sys_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '系统密钥',
  `sys_sign` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '系统签名',
  `register` tinyint(1) NOT NULL DEFAULT 0 COMMENT '能否注册，默认0禁止',
  `check` tinyint(1) NOT NULL DEFAULT 1 COMMENT '注册审核，默认1审核',
  `bind_phone` tinyint(1) NOT NULL DEFAULT 0 COMMENT '默认0不绑定，1绑定',
  `record_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备案号',
  `record_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备案公司名称',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sun_core_useaccount
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_useaccount`;
CREATE TABLE `sun_core_useaccount`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `acid` int(11) NOT NULL COMMENT '平台id',
  `role` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1操作员2平台所有者',
  `order` int(11) NOT NULL DEFAULT 1 COMMENT '排序大靠前',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `index_ua`(`uid`, `acid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 48 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for sun_core_useapp
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_useapp`;
CREATE TABLE `sun_core_useapp`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `sid` int(11) NOT NULL COMMENT 'app支持类型id',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `index_us`(`uid`, `sid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 118 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for sun_core_user
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_user`;
CREATE TABLE `sun_core_user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '临时访问id',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1用户2管理员',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '昵称',
  `pwd` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '头像',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '手机号',
  `wechat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '微信号',
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '邮箱',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '登录ip',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户备注信息',
  `is_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0正常1回收站2待审核',
  `login_time` datetime(0) NULL DEFAULT NULL COMMENT '上次登录时间',
  `end_time` datetime(0) NULL DEFAULT NULL COMMENT '到期时间',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `index_s`(`session_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 20 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sun_core_usermenu
-- ----------------------------
DROP TABLE IF EXISTS `sun_core_usermenu`;
CREATE TABLE `sun_core_usermenu`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uaid` int(11) NOT NULL COMMENT '使用平台表id',
  `menu_id` int(11) NOT NULL COMMENT '应用菜单id',
  `can_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1可用0禁止',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `index_um`(`uaid`, `menu_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

SET FOREIGN_KEY_CHECKS = 1;
