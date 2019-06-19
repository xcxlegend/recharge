# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.7.16)
# Database: recharge
# Generation Time: 2019-06-19 13:02:22 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table pay_admin
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_admin`;

CREATE TABLE `pay_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` varchar(50) NOT NULL COMMENT '后台用户名',
  `password` varchar(32) NOT NULL COMMENT '后台用户密码',
  `groupid` tinyint(1) unsigned DEFAULT '0' COMMENT '用户组',
  `createtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `google_secret_key` varchar(128) NOT NULL DEFAULT '' COMMENT '谷歌令牌密钥',
  `mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '手机号码',
  `session_random` varchar(50) NOT NULL DEFAULT '' COMMENT 'session随机字符串',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `pay_admin` WRITE;
/*!40000 ALTER TABLE `pay_admin` DISABLE KEYS */;

INSERT INTO `pay_admin` (`id`, `username`, `password`, `groupid`, `createtime`, `google_secret_key`, `mobile`, `session_random`)
VALUES
	(11,'admin','469d9116d5310318d1fb31f02ec9e23c',1,1553768137,'','','WCG3kWKtaSmLjVYbCyKAEvW6nFd5LGnx');

/*!40000 ALTER TABLE `pay_admin` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_apimoney
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_apimoney`;

CREATE TABLE `pay_apimoney` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `payapiid` int(11) DEFAULT NULL,
  `money` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `freezemoney` decimal(15,3) NOT NULL DEFAULT '0.000' COMMENT '冻结金额',
  `status` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `pay_apimoney` WRITE;
/*!40000 ALTER TABLE `pay_apimoney` DISABLE KEYS */;

INSERT INTO `pay_apimoney` (`id`, `userid`, `payapiid`, `money`, `freezemoney`, `status`)
VALUES
	(10,6,207,18000.0000,0.000,1);

/*!40000 ALTER TABLE `pay_apimoney` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_auth_error_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_auth_error_log`;

CREATE TABLE `pay_auth_error_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `auth_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：商户登录 1：后台登录 2：商户短信验证 3：后台短信验证 4：谷歌令牌验证 5：支付密码验证 ',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `ctime` int(11) NOT NULL DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pay_auth_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_auth_group`;

CREATE TABLE `pay_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `is_manager` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1需要验证权限 0 不需要验证权限',
  `rules` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `pay_auth_group` WRITE;
/*!40000 ALTER TABLE `pay_auth_group` DISABLE KEYS */;

INSERT INTO `pay_auth_group` (`id`, `title`, `status`, `is_manager`, `rules`)
VALUES
	(1,'超级管理员',1,0,'1,133,2,3,51,4,57,5,55,56,58,59,6,44,52,53,48,70,54,126,7,8,60,61,62,9,63,64,65,66,10,67,68,69,11,12,79,80,81,82,83,84,85,86,87,88,89,90,91,93,94,95,96,97,98,99,100,101,120,13,14,15,92,16,73,76,77,78,17,46,121,18,19,71,75,20,72,74,22,21,23,114,115,24,25,26,125,127,130,27,28,108,129,29,102,30,103,106,107,119,104,105,109,110,111,128,31,32,33,34,35,36,37,38,39,113,40,112,41,42,45,47,116,122,117,123,118,124'),
	(2,'运营管理员',1,0,'1,133,3,51,4,57,5,55,56,59,6,44,52,70,54,7,60,61,62,63,65,66,67,68,69,12,79,80,81,82,83,84,85,86,87,93,94,98,99,13,14,15,92,73,76,77,78,46,18,19,71,22,23,24,33,34,35,36,37,38,39,40,41,42,45,47'),
	(3,'财务管理员',1,1,'1,133,5,6,70,65,66,67,68,69,13,73,76,77,71,75,72,74,23,24,25,26'),
	(4,'普通商户',1,1,'22,24'),
	(5,'普通代理商',1,1,'114,115');

/*!40000 ALTER TABLE `pay_auth_group` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_auth_group_access
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_auth_group_access`;

CREATE TABLE `pay_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `pay_auth_group_access` WRITE;
/*!40000 ALTER TABLE `pay_auth_group_access` DISABLE KEYS */;

INSERT INTO `pay_auth_group_access` (`uid`, `group_id`)
VALUES
	(1,1),
	(3,1),
	(9,1),
	(10,1),
	(10,2),
	(11,1);

/*!40000 ALTER TABLE `pay_auth_group_access` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_auth_rule
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_auth_rule`;

CREATE TABLE `pay_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `icon` varchar(100) DEFAULT '' COMMENT '图标',
  `menu_name` varchar(100) NOT NULL DEFAULT '' COMMENT '规则唯一标识Controller/action',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `pid` tinyint(5) NOT NULL DEFAULT '0' COMMENT '菜单ID ',
  `is_menu` tinyint(1) unsigned DEFAULT '0' COMMENT '1:是主菜单 0否',
  `is_race_menu` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1:是 0:不是',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `condition` char(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

LOCK TABLES `pay_auth_rule` WRITE;
/*!40000 ALTER TABLE `pay_auth_rule` DISABLE KEYS */;

INSERT INTO `pay_auth_rule` (`id`, `icon`, `menu_name`, `title`, `pid`, `is_menu`, `is_race_menu`, `type`, `status`, `condition`)
VALUES
	(1,'fa fa-home','Index/index','管理首页',0,1,0,1,1,''),
	(2,'fa fa-cogs','System/#','系统设置',0,1,0,1,1,''),
	(3,'fa fa-cogs','System/base','基本设置',2,1,0,1,1,''),
	(4,'fa fa-envelope-o','System/email','邮件设置',2,1,0,1,1,''),
	(5,'fa fa-send','System/smssz','短信设置',2,1,0,1,1,''),
	(6,'fa fa-pencil-square-o','System/planning','计划任务',2,1,0,1,1,''),
	(7,'fa fa-user-circle','Admin/#','管理员管理',0,1,0,1,1,''),
	(8,'fa fa-vcard ','Admin/index','管理员信息',7,1,0,1,1,''),
	(9,'fa fa-life-ring','Auth/index','角色配置',7,1,0,1,1,''),
	(10,'fa fa-universal-access','Menu/index','权限配置',7,1,0,1,1,''),
	(11,'fa fa-users','User/#','用户管理',0,1,0,1,1,''),
	(12,'fa fa-user','User/index?status=1&authorized=1','已认证用户',11,1,0,1,1,''),
	(13,'fa fa-user-o','User/index?status=1&authorized=2','待认证用户',11,1,0,1,1,''),
	(14,'fa fa-user-plus','User/index?status=1&authorized=0','未认证用户',11,1,0,1,1,''),
	(15,'fa fa-user-times','User/index?status=0','冻结用户',11,1,0,1,1,''),
	(16,'fa fa-gift','User/invitecode','邀请码',11,1,0,1,1,''),
	(17,'fa fa-address-book','User/loginrecord','登录记录',11,1,0,1,1,''),
	(18,'fa fa-user-circle','Agent/#','代理管理',0,1,0,1,1,''),
	(19,'fa fa-signing','User/agentList','代理列表',18,1,0,1,1,''),
	(20,'fa fa-signing','Order/changeRecord?bank=9','佣金记录',18,1,0,1,1,''),
	(21,'fa fa-sellsy','Order/dfApiOrderList','代付Api订单',22,1,0,1,1,''),
	(22,'fa fa-reorder','User/#','订单管理',0,1,0,1,1,''),
	(23,'fa fa-indent','Order/changeRecord','流水记录',22,1,0,1,1,''),
	(24,'fa fa-thumbs-up','Order/index?status=1or2','成功订单',22,1,0,1,1,''),
	(25,'fa fa-thumbs-down','Order/index?status=0','未支付订单',22,1,0,1,1,''),
	(26,'fa fa-hand-o-right','Order/index?status=1','通知异常订单',22,1,0,1,1,''),
	(27,'fa fa-user-secret','Withdrawal','提款管理',0,1,0,1,1,''),
	(28,'fa fa-wrench','Withdrawal/setting','提款设置',27,1,0,1,1,''),
	(29,'fa fa-asl-interpreting','Withdrawal/index','手动结算',27,1,0,1,1,''),
	(30,'fa fa-window-restore','Withdrawal/payment','代付结算',27,1,0,1,1,''),
	(31,'fa fa-bank','Channel/#','通道管理',0,1,0,1,1,''),
	(32,'fa fa-product-hunt','Channel/index','入金渠道设置',31,1,0,1,1,''),
	(33,'fa fa-sitemap','Channel/product','支付产品设置',31,1,0,1,1,''),
	(34,'fa fa-sliders','PayForAnother/index','代付渠道设置',31,1,0,1,1,''),
	(35,'fa fa-book','Content/#','文章管理',0,1,0,1,1,''),
	(36,'fa fa-tags','Content/category','栏目列表',35,1,0,1,1,''),
	(37,'fa fa-list-alt','Content/article','文章列表',35,1,0,1,1,''),
	(38,'fa fa-line-chart','Statistics/#','财务分析',0,1,0,1,1,''),
	(39,'fa fa-bar-chart-o','Statistics/index','交易统计',38,1,0,1,1,''),
	(40,'fa fa-area-chart','Statistics/userFinance','商户交易统计',38,1,0,1,1,''),
	(41,'fa fa-industry','Statistics/userFinance?groupid=agent','代理商交易统计',38,1,0,1,1,''),
	(42,'fa fa-pie-chart','Statistics/channelFinance','接口交易统计',38,1,0,1,1,''),
	(43,'fa fa-cubes','Template/index','模板设置',2,1,0,1,0,''),
	(44,'fa fa-qq','System/mobile','手机设置',2,1,0,1,1,''),
	(45,'fa fa-signal','Statistics/chargeRank','充值排行榜',38,1,0,1,1,''),
	(46,'fa fa-first-order','Deposit/index','投诉保证金设置',11,1,0,1,1,''),
	(47,'fa fa-asterisk','Statistics/complaintsDeposit','投诉保证金统计',38,1,0,1,1,''),
	(48,'fa fa-magnet','System/clearData','数据清理',2,1,0,1,1,''),
	(51,'','System/SaveBase','保存设置',3,0,0,1,1,''),
	(52,'','System/BindMobileShow','绑定手机号码',44,0,0,1,1,''),
	(53,'','System/editMobileShow','手机修改',44,0,0,1,1,''),
	(54,'fa fa-wrench','System/editPassword','修改密码',2,1,0,1,1,''),
	(55,'','System/editSmstemplate','短信模板',5,0,0,1,1,''),
	(56,'','System/saveSmstemplate','保存短信模板',5,0,0,1,1,''),
	(57,'','System/saveEmail','邮件保存',4,0,0,1,1,''),
	(58,'','System/testMobile','测试短信',5,0,0,1,1,''),
	(59,'','System/deleteAdmin','删除短信模板',5,0,0,1,1,''),
	(60,'','Admin/addAdmin','管理员添加',8,0,0,1,1,''),
	(61,'','Admin/editAdmin','管理员修改',8,0,0,1,1,''),
	(62,'','Admin/deleteAdmin','管理员删除',8,0,0,1,1,''),
	(63,'','Auth/addGroup','添加角色',9,0,0,1,1,''),
	(64,'','Auth/editGroup','修改角色',9,0,0,1,1,''),
	(65,'','Auth/giveRole','选择角色',9,0,0,1,1,''),
	(66,'','Auth/ruleGroup','分配权限',9,0,0,1,1,''),
	(67,'','Menu/addMenu','添加菜单',10,0,0,1,1,''),
	(68,'','Menu/editMenu','修改菜单',10,0,0,1,1,''),
	(69,'','Menu/delMenu','删除菜单',10,0,0,1,1,''),
	(70,'','System/clearDataSend','数据清理提交',48,0,0,1,1,''),
	(71,'','User/addAgentCate','代理级别',19,0,0,1,1,''),
	(72,'','User/saveAgentCate','保存代理级别',18,0,0,1,1,''),
	(73,'','User/addInvitecode','添加激活码',16,0,0,1,1,''),
	(74,'','User/EditAgentCate','修改代理分类',18,0,0,1,1,''),
	(75,'','User/deleteAgentCate','删除代理分类',19,0,0,1,1,''),
	(76,'','User/setInvite','邀请码设置',16,0,0,1,1,''),
	(77,'','User/addInvite','创建邀请码',16,0,0,1,1,''),
	(78,'','User/delInvitecode','删除邀请码',16,0,0,1,1,''),
	(79,'','User/editUser','用户编辑',12,0,0,1,1,''),
	(80,'','User/changeuser','修改状态',12,0,0,1,1,''),
	(81,'','User/authorize','用户认证',12,0,0,1,1,''),
	(82,'','User/usermoney','用户资金管理',12,0,0,1,1,''),
	(83,'','User/userWithdrawal','用户提现设置',12,0,0,1,1,''),
	(84,'','User/userRateEdit','用户费率设置',12,0,0,1,1,''),
	(85,'','User/editPassword','用户密码修改',12,0,0,1,1,''),
	(86,'','User/editStatus','用户状态修改',12,0,0,1,1,''),
	(87,'','User/delUser','用户删除',12,0,0,1,1,''),
	(88,'','User/thawingFunds','T1解冻任务管理',12,0,0,1,1,''),
	(89,'','User/exportuser','导出用户',12,0,0,1,1,''),
	(90,'','User/editAuthoize','修改用户认证',12,0,0,1,1,''),
	(91,'','User/getRandstr','切换商户密钥',12,0,0,1,1,''),
	(92,'','User/suoding','用户锁定',15,0,0,1,1,''),
	(93,'','User/editbankcard','银行卡管理',12,0,0,1,1,''),
	(94,'','User/saveUser','添加用户',12,0,0,1,1,''),
	(95,'','User/saveUserProduct','保存用户产品',12,0,0,1,1,''),
	(96,'','User/saveUserRate','保存用户费率',12,0,0,1,1,''),
	(97,'','User/edittongdao','编辑通道',12,0,0,1,1,''),
	(98,'','User/frozenMoney','用户资金冻结',12,0,0,1,1,''),
	(99,'','User/unfrozenHandles','T1资金解冻',12,0,0,1,1,''),
	(100,'','User/frozenOrder','冻结订单列表',12,0,0,1,1,''),
	(101,'','User/frozenHandles','T1订单解冻展示',12,0,0,1,1,''),
	(102,'','Withdrawal/editStatus','操作状态',29,0,0,1,1,''),
	(103,'','Withdrawal/editwtStatus','操作订单状态',30,0,0,1,1,''),
	(104,'','Withdrawal/exportorder','导出数据',27,0,0,1,1,''),
	(105,'','Withdrawal/editwtAllStatus','批量修改提款状态',27,0,0,1,1,''),
	(106,'','Withdrawal/exportweituo','导出委托提现',30,0,0,1,1,''),
	(107,'','Payment/index','提交上游',30,0,0,1,1,''),
	(108,'','Withdrawal/saveWithdrawal','保存设置',28,0,0,1,1,''),
	(109,'','Withdrawal/AddHoliday','添加假日',27,0,0,1,1,''),
	(110,'','Withdrawal/settimeEdit','编辑提款时间',27,0,0,1,1,''),
	(111,'','Withdrawal/delHoliday','删除节假日',27,0,0,1,1,''),
	(112,'','Statistics/exportorder','订单数据导出',40,0,0,1,1,''),
	(113,'','Statistics/details','查看详情',39,0,0,1,1,''),
	(114,'','Order/exportorder','订单导出',23,0,0,1,1,''),
	(115,'','Order/exceldownload','记录导出',23,0,0,1,1,''),
	(116,'fa fa-area-chart','Statistics/platformReport','平台报表',38,1,0,1,1,''),
	(117,'fa fa-area-chart','Statistics/merchantReport','商户报表',38,1,0,1,1,''),
	(118,'fa fa-area-chart','Statistics/agentReport','代理报表',38,1,0,1,1,''),
	(119,'','Withdrawal/submitDf','代付提交',30,0,0,1,1,''),
	(120,'','User/editUserProduct','分配用户通道',12,0,0,1,1,''),
	(121,'fa fa-wrench','Transaction/index','风控设置',11,1,0,1,1,''),
	(122,'','Statistics/exportPlatform','导出平台报表',116,0,0,1,1,''),
	(123,'','Statistics/exportMerchant','导出商户报表',117,0,0,1,1,''),
	(124,'','Statistics/exportAgent','导出代理报表',118,0,0,1,1,''),
	(125,'','Order/show','查看订单',22,0,0,1,1,''),
	(126,'fa fa-cog','Withdrawal/checkNotice','提现申请声音提示',2,0,0,1,1,''),
	(127,'fa fa-smile-o','Order/index','全部订单',22,1,0,1,1,''),
	(128,'','Withdrawal/rejectAllDf','批量驳回代付',27,0,0,1,1,''),
	(129,'','User/saveWithdrawal','保存用户提款设置',28,0,0,1,1,''),
	(130,'fa fa-snowflake-o','Order/frozenOrder','冻结订单',22,1,0,1,1,''),
	(133,'fa fa-home','Index/main','管理首页',1,1,0,1,1,'');

/*!40000 ALTER TABLE `pay_auth_rule` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_channel
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_channel`;

CREATE TABLE `pay_channel` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '供应商通道ID',
  `code` varchar(200) DEFAULT NULL COMMENT '供应商通道英文编码',
  `title` varchar(200) DEFAULT NULL COMMENT '供应商通道名称',
  `mch_id` varchar(100) DEFAULT NULL COMMENT '商户号',
  `signkey` varchar(500) DEFAULT NULL COMMENT '签文密钥',
  `appid` varchar(100) DEFAULT NULL COMMENT '应用APPID',
  `appsecret` varchar(100) DEFAULT NULL COMMENT '安全密钥',
  `gateway` varchar(300) DEFAULT NULL COMMENT '网关地址',
  `pagereturn` varchar(255) DEFAULT NULL COMMENT '页面跳转网址',
  `serverreturn` varchar(255) DEFAULT NULL COMMENT '服务器通知网址',
  `defaultrate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '下家费率',
  `fengding` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '封顶手续费',
  `rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '银行费率',
  `updatetime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上次更改时间',
  `unlockdomain` varchar(100) NOT NULL COMMENT '防封域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1开启 0关闭',
  `paytype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '渠道类型: 1 微信扫码 2 微信H5 3 支付宝扫码 4 支付宝H5 5网银跳转 6网银直连 7百度钱包 8 QQ钱包 9 京东钱包',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `paying_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '当天交易金额',
  `all_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '当天上游可交易量',
  `last_paying_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后交易时间',
  `min_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔最小交易额',
  `max_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔最大交易额',
  `control_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '风控状态:0否1是',
  `offline_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '通道上线状态:0已下线，1上线',
  `t0defaultrate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'T0运营费率',
  `t0fengding` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'T0封顶手续费',
  `t0rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'T0成本费率',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='供应商列表';

LOCK TABLES `pay_channel` WRITE;
/*!40000 ALTER TABLE `pay_channel` DISABLE KEYS */;

INSERT INTO `pay_channel` (`id`, `code`, `title`, `mch_id`, `signkey`, `appid`, `appsecret`, `gateway`, `pagereturn`, `serverreturn`, `defaultrate`, `fengding`, `rate`, `updatetime`, `unlockdomain`, `status`, `paytype`, `start_time`, `end_time`, `paying_money`, `all_money`, `last_paying_time`, `min_money`, `max_money`, `control_status`, `offline_status`, `t0defaultrate`, `t0fengding`, `t0rate`)
VALUES
	(1,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.0000,0.0000,0.0000,0,'',0,0,0,0,0.00,0.00,0,0.00,0.00,0,1,0.0000,0.0000,0.0000);

/*!40000 ALTER TABLE `pay_channel` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_channel_account
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_channel_account`;

CREATE TABLE `pay_channel_account` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '供应商通道账号ID',
  `channel_id` smallint(6) unsigned NOT NULL COMMENT '通道id',
  `mch_id` varchar(100) DEFAULT NULL COMMENT '商户号',
  `signkey` varchar(500) DEFAULT NULL COMMENT '签文密钥',
  `appid` varchar(100) DEFAULT NULL COMMENT '应用APPID',
  `appsecret` varchar(2500) DEFAULT NULL COMMENT '安全密钥',
  `defaultrate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '下家费率',
  `fengding` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '封顶手续费',
  `rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '银行费率',
  `updatetime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上次更改时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1开启 0关闭',
  `title` varchar(100) DEFAULT NULL COMMENT '账户标题',
  `weight` tinyint(2) DEFAULT NULL COMMENT '轮询权重',
  `custom_rate` tinyint(1) DEFAULT NULL COMMENT '是否自定义费率',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始交易时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `last_paying_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一笔交易时间',
  `paying_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '当天交易金额',
  `all_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单日可交易金额',
  `max_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔交易最大金额',
  `min_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔交易最小金额',
  `offline_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '上线状态-1上线,0下线',
  `control_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '风控状态-0不风控,1风控中',
  `is_defined` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否自定义:1-是,0-否',
  `unit_frist_paying_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间第一笔交易时间',
  `unit_paying_number` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '单时间交易笔数',
  `unit_paying_amount` decimal(11,0) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间交易金额',
  `unit_interval` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间数值',
  `time_unit` char(1) NOT NULL DEFAULT 's' COMMENT '限制时间单位',
  `unit_number` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间次数',
  `unit_all_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单位时间金额',
  `t0defaultrate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'T0运营费率',
  `t0fengding` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'T0封顶手续费',
  `t0rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'T0成本费率',
  `unlockdomain` varchar(255) NOT NULL COMMENT '防封域名',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='供应商账号列表';



# Dump of table pay_email
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_email`;

CREATE TABLE `pay_email` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `smtp_host` varchar(300) DEFAULT NULL,
  `smtp_port` varchar(300) DEFAULT NULL,
  `smtp_user` varchar(300) DEFAULT NULL,
  `smtp_pass` varchar(300) DEFAULT NULL,
  `smtp_email` varchar(300) DEFAULT NULL,
  `smtp_name` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table pay_loginrecord
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_loginrecord`;

CREATE TABLE `pay_loginrecord` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `logindatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `loginip` varchar(100) NOT NULL,
  `loginaddress` varchar(300) DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型：0：前台用户 1：后台用户',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `pay_loginrecord` WRITE;
/*!40000 ALTER TABLE `pay_loginrecord` DISABLE KEYS */;

INSERT INTO `pay_loginrecord` (`id`, `userid`, `logindatetime`, `loginip`, `loginaddress`, `type`)
VALUES
	(1,11,'2019-06-13 22:29:38','127.0.0.1','本机地址',1);

/*!40000 ALTER TABLE `pay_loginrecord` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_member
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_member`;

CREATE TABLE `pay_member` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(32) NOT NULL COMMENT '密码',
  `groupid` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用户组',
  `salt` varchar(10) NOT NULL COMMENT '密码随机字符',
  `parentid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '代理ID',
  `agent_cate` int(11) NOT NULL DEFAULT '0' COMMENT '代理级别',
  `balance` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '可用余额',
  `blockedbalance` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '冻结可用余额',
  `email` varchar(100) NOT NULL,
  `activate` varchar(200) NOT NULL,
  `regdatetime` int(11) unsigned NOT NULL DEFAULT '0',
  `activatedatetime` int(11) unsigned NOT NULL DEFAULT '0',
  `realname` varchar(50) DEFAULT NULL COMMENT '姓名',
  `sex` tinyint(1) NOT NULL DEFAULT '1' COMMENT '性别',
  `birthday` int(11) NOT NULL DEFAULT '0',
  `sfznumber` varchar(20) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL COMMENT '联系电话',
  `qq` varchar(15) DEFAULT NULL COMMENT 'QQ',
  `address` varchar(200) DEFAULT NULL COMMENT '联系地址',
  `paypassword` varchar(32) DEFAULT NULL COMMENT '支付密码',
  `authorized` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 已认证 0 未认证 2 待审核',
  `apidomain` varchar(500) DEFAULT NULL COMMENT '授权访问域名',
  `apikey` varchar(32) NOT NULL COMMENT 'APIKEY',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `receiver` varchar(255) DEFAULT NULL COMMENT '台卡显示的收款人信息',
  `unit_paying_number` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间已交易次数',
  `unit_paying_amount` decimal(11,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '单位时间已交易金额',
  `unit_frist_paying_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间已交易的第一笔时间',
  `last_paying_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当天最后一笔已交易时间',
  `paying_money` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '当天已交易金额',
  `login_ip` varchar(255) NOT NULL DEFAULT ' ' COMMENT '登录IP',
  `last_error_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录错误时间',
  `login_error_num` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '错误登录次数',
  `google_auth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启谷歌身份验证登录',
  `df_api` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启代付API',
  `open_charge` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启充值功能',
  `df_domain` text NOT NULL COMMENT '代付域名报备',
  `df_auto_check` tinyint(1) NOT NULL DEFAULT '0' COMMENT '代付API自动审核',
  `google_secret_key` varchar(255) NOT NULL DEFAULT '' COMMENT '谷歌密钥',
  `df_ip` text NOT NULL COMMENT '代付域名报备IP',
  `session_random` varchar(50) NOT NULL DEFAULT '' COMMENT 'session随机字符串',
  `df_charge_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '代付API扣除手续费方式，0：从到账金额里扣，1：从商户余额里扣',
  `last_login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `pay_member` WRITE;
/*!40000 ALTER TABLE `pay_member` DISABLE KEYS */;

INSERT INTO `pay_member` (`id`, `username`, `password`, `groupid`, `salt`, `parentid`, `agent_cate`, `balance`, `blockedbalance`, `email`, `activate`, `regdatetime`, `activatedatetime`, `realname`, `sex`, `birthday`, `sfznumber`, `mobile`, `qq`, `address`, `paypassword`, `authorized`, `apidomain`, `apikey`, `status`, `receiver`, `unit_paying_number`, `unit_paying_amount`, `unit_frist_paying_time`, `last_paying_time`, `paying_money`, `login_ip`, `last_error_time`, `login_error_num`, `google_auth`, `df_api`, `open_charge`, `df_domain`, `df_auto_check`, `google_secret_key`, `df_ip`, `session_random`, `df_charge_type`, `last_login_time`)
VALUES
	(62,'demo','777b496b63c385aa62c6fff2816b57b2',4,'2706',1,4,679.4200,0.0000,'abc@qq.com','54079f7c82c431696a841e6d6810e6b9',1546843466,2019,'demo',0,-28800,'123123','','','123123','96e79218965eb72c92a549dd5a330112',1,NULL,'lvjip0x4sqeni4h69pzbpgorp3u2ea3w',1,NULL,65,995.5460,0,1559658573,995.5460,'',0,0,0,1,0,'',1,'','','HOwDrkguW9V342ubBLXHUVIzrh0pKLkP',0,1559398781);

/*!40000 ALTER TABLE `pay_member` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_member_agent_cate
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_member_agent_cate`;

CREATE TABLE `pay_member_agent_cate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cate_name` varchar(50) DEFAULT NULL COMMENT '等级名',
  `desc` varchar(255) DEFAULT NULL COMMENT '等级描述',
  `ctime` int(11) DEFAULT '0' COMMENT '添加时间',
  `sort` int(11) DEFAULT '99' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `pay_member_agent_cate` WRITE;
/*!40000 ALTER TABLE `pay_member_agent_cate` DISABLE KEYS */;

INSERT INTO `pay_member_agent_cate` (`id`, `cate_name`, `desc`, `ctime`, `sort`)
VALUES
	(4,'普通商户','',1522638122,99),
	(5,'普通代理商户','',1522638122,99),
	(6,'中级代理商户','',1522638122,99),
	(7,'高级代理商户','',1522638122,99);

/*!40000 ALTER TABLE `pay_member_agent_cate` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_moneychange
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_moneychange`;

CREATE TABLE `pay_moneychange` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
  `ymoney` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '原金额',
  `money` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '变动金额',
  `gmoney` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '变动后金额',
  `datetime` datetime DEFAULT NULL COMMENT '修改时间',
  `transid` varchar(50) DEFAULT NULL COMMENT '交易流水号',
  `tongdao` smallint(6) unsigned DEFAULT '0' COMMENT '支付通道ID',
  `lx` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '类型',
  `tcuserid` int(11) DEFAULT NULL,
  `tcdengji` int(11) DEFAULT NULL,
  `orderid` varchar(50) DEFAULT NULL COMMENT '订单号',
  `contentstr` varchar(255) DEFAULT NULL COMMENT '备注',
  `t` int(4) NOT NULL DEFAULT '0' COMMENT '结算方式',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pay_order
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_order`;

CREATE TABLE `pay_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pay_memberid` varchar(100) NOT NULL COMMENT '商户编号',
  `pay_orderid` varchar(100) NOT NULL COMMENT '系统订单号',
  `pay_amount` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `pay_actualamount` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `pay_applydate` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单创建日期',
  `pay_successdate` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单支付成功时间',
  `pay_code` varchar(100) DEFAULT NULL COMMENT '支付编码',
  `pay_notifyurl` varchar(500) NOT NULL COMMENT '商家异步通知地址',
  `pay_callbackurl` varchar(500) NOT NULL COMMENT '商家页面通知地址',
  `pay_bankname` varchar(300) DEFAULT NULL,
  `pay_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订单状态: 0 未支付 1 已支付未返回 2 已支付已返回',
  `pay_productname` varchar(300) DEFAULT NULL COMMENT '商品名称',
  `pay_zh_tongdao` varchar(50) DEFAULT NULL,
  `out_trade_id` varchar(50) NOT NULL COMMENT '商户订单号',
  `num` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '已补发次数',
  `memberid` varchar(100) DEFAULT NULL COMMENT '支付渠道商家号',
  `account` varchar(100) DEFAULT NULL COMMENT '渠道账号',
  `isdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '伪删除订单 1 删除 0 未删',
  `attach` text CHARACTER SET utf8mb4 COMMENT '商家附加字段,原样返回',
  `pay_url` varchar(255) DEFAULT NULL COMMENT '支付地址',
  `pay_channel_account` varchar(255) DEFAULT NULL COMMENT '通道账户',
  `cost` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '成本',
  `cost_rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '成本费率',
  `account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '子账号id',
  `channel_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '渠道id',
  `last_reissue_time` int(11) NOT NULL DEFAULT '11' COMMENT '最后补发时间',
  `pool_phone_id` int(11) DEFAULT NULL COMMENT '号码池ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `IND_ORD` (`pay_orderid`),
  KEY `account_id` (`account_id`),
  KEY `channel_id` (`channel_id`),
  KEY `m_out_id` (`pay_memberid`,`out_trade_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table pay_order_notify
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_order_notify`;

CREATE TABLE `pay_order_notify` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `body` text NOT NULL COMMENT '回调内容',
  `notify_url` varchar(255) NOT NULL DEFAULT '' COMMENT '回调地址',
  `times` int(11) NOT NULL DEFAULT '0' COMMENT '回调次数',
  `last` int(11) NOT NULL COMMENT '最后回调时间',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单类型 0=充值订单 1=号码商',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=等待回调 1=正在回调 2=回调完成 准备删除',
  PRIMARY KEY (`id`),
  KEY `status_last` (`status`,`last`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



# Dump of table pay_pay_for_another
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_pay_for_another`;

CREATE TABLE `pay_pay_for_another` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `code` varchar(64) NOT NULL COMMENT '代付代码',
  `title` varchar(64) NOT NULL COMMENT '代付名称',
  `mch_id` varchar(255) NOT NULL DEFAULT ' ' COMMENT '商户号',
  `appid` varchar(100) NOT NULL DEFAULT ' ' COMMENT '应用APPID',
  `appsecret` varchar(100) NOT NULL DEFAULT ' ' COMMENT '应用密钥',
  `signkey` varchar(500) NOT NULL DEFAULT ' ' COMMENT '加密的秘钥',
  `public_key` varchar(1000) NOT NULL DEFAULT '  ' COMMENT '加密的公钥',
  `private_key` varchar(1000) NOT NULL DEFAULT '  ' COMMENT '加密的私钥',
  `exec_gateway` varchar(255) NOT NULL DEFAULT ' ' COMMENT '请求代付的地址',
  `query_gateway` varchar(255) NOT NULL DEFAULT ' ' COMMENT '查询代付的地址',
  `serverreturn` varchar(255) NOT NULL DEFAULT ' ' COMMENT '服务器通知网址',
  `unlockdomain` varchar(255) NOT NULL DEFAULT ' ' COMMENT '防封域名',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更改时间',
  `status` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1开启 0关闭',
  `is_default` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认：1是，0否',
  `cost_rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '成本费率',
  `rate_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '费率类型：按单笔收费0，按比例收费：1',
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `updatetime` (`updatetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代付通道表';



# Dump of table pay_paylog
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_paylog`;

CREATE TABLE `pay_paylog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `out_trade_no` varchar(50) NOT NULL,
  `result_code` varchar(50) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `fromuser` varchar(50) NOT NULL,
  `time_end` int(11) unsigned NOT NULL DEFAULT '0',
  `total_fee` smallint(6) unsigned NOT NULL DEFAULT '0',
  `payname` varchar(50) NOT NULL,
  `bank_type` varchar(20) DEFAULT NULL,
  `trade_type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IND_TRD` (`transaction_id`),
  UNIQUE KEY `IND_ORD` (`out_trade_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pay_pool_phones
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_pool_phones`;

CREATE TABLE `pay_pool_phones` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '号码商ID 使用member表',
  `phone` char(15) NOT NULL DEFAULT '',
  `money` int(11) NOT NULL DEFAULT '0' COMMENT '充值金额 分',
  `notify_url` varchar(255) DEFAULT NULL COMMENT '商户回调地址',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT '时间戳',
  `channel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '运营商标识 1=移动 2=联调 3=电信',
  `out_trade_id` varchar(255) NOT NULL DEFAULT '' COMMENT '商户订单号',
  `order_id` varchar(255) NOT NULL DEFAULT '' COMMENT '平台订单号',
  `lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否锁定 表示匹配支付',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pid_out_trade_id` (`pid`,`out_trade_id`),
  KEY `pool_id` (`pid`),
  KEY `lock_time` (`lock`,`time`),
  KEY `lock_money` (`lock`,`money`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

LOCK TABLES `pay_pool_phones` WRITE;
/*!40000 ALTER TABLE `pay_pool_phones` DISABLE KEYS */;

INSERT INTO `pay_pool_phones` (`id`, `pid`, `phone`, `money`, `notify_url`, `time`, `channel`, `out_trade_id`, `order_id`, `lock`)
VALUES
	(3,1,'18081159865',1000,'http://127.0.0.1:8080/v1/test/notify',1560856652,3,'123456','P2019061819173230',0),
	(4,1,'18081159866',1000,'http://127.0.0.1:8080/v1/test/notify',1560931628,3,'12345677777','P2019061916070836',0);

/*!40000 ALTER TABLE `pay_pool_phones` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_pool_provider
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_pool_provider`;

CREATE TABLE `pay_pool_provider` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(50) DEFAULT '' COMMENT '名称',
  `appkey` char(32) NOT NULL DEFAULT '' COMMENT 'key',
  `appsecret` char(32) NOT NULL DEFAULT '' COMMENT 'secret',
  `money` int(11) NOT NULL DEFAULT '0' COMMENT '金额结算++',
  PRIMARY KEY (`id`),
  KEY `akey` (`appkey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

LOCK TABLES `pay_pool_provider` WRITE;
/*!40000 ALTER TABLE `pay_pool_provider` DISABLE KEYS */;

INSERT INTO `pay_pool_provider` (`id`, `name`, `appkey`, `appsecret`, `money`)
VALUES
	(1,'demo','a','2',0);

/*!40000 ALTER TABLE `pay_pool_provider` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_pool_rec
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_pool_rec`;

CREATE TABLE `pay_pool_rec` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '数量',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT '批次时间戳',
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



# Dump of table pay_product
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_product`;

CREATE TABLE `pay_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '通道名称',
  `code` varchar(50) NOT NULL COMMENT '通道代码',
  `polling` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '接口模式 0 单独 1 轮询',
  `paytype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '支付类型 1 微信扫码 2 微信H5 3 支付宝扫码 4 支付宝H5 5 网银跳转 6网银直连  7 百度钱包  8 QQ钱包 9 京东钱包',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `isdisplay` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用户端显示 1 显示 0 不显示',
  `channel` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '通道ID',
  `weight` text COMMENT '平台默认通道权重',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `pay_product` WRITE;
/*!40000 ALTER TABLE `pay_product` DISABLE KEYS */;

INSERT INTO `pay_product` (`id`, `name`, `code`, `polling`, `paytype`, `status`, `isdisplay`, `channel`, `weight`)
VALUES
	(1,'微信H5','Wxh5',0,1,1,1,1,''),
	(2,'微信扫码','Wxsan',0,1,1,1,244,''),
	(3,'支付宝扫码','Aliscan',0,1,1,1,1,''),
	(4,'支付宝H5','Alipaywap',0,1,1,1,1,'');

/*!40000 ALTER TABLE `pay_product` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_product_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_product_user`;

CREATE TABLE `pay_product_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT ' ',
  `userid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
  `pid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '商户通道ID',
  `polling` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '接口模式：0 单独 1 轮询',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '通道状态 0 关闭 1 启用',
  `channel` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '指定单独通道ID',
  `weight` varchar(255) DEFAULT NULL COMMENT '通道权重',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table pay_reconciliation
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_reconciliation`;

CREATE TABLE `pay_reconciliation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT '0' COMMENT '用户ID',
  `order_total_count` int(11) DEFAULT '0' COMMENT '总订单数',
  `order_success_count` int(11) DEFAULT '0' COMMENT '成功订单数',
  `order_fail_count` int(11) DEFAULT '0' COMMENT '未支付订单数',
  `order_total_amount` decimal(11,4) DEFAULT '0.0000' COMMENT '订单总额',
  `order_success_amount` decimal(11,4) DEFAULT '0.0000' COMMENT '订单实付总额',
  `date` date DEFAULT NULL COMMENT '日期',
  `ctime` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pay_route
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_route`;

CREATE TABLE `pay_route` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `urlstr` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table pay_sms
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_sms`;

CREATE TABLE `pay_sms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_key` varchar(255) DEFAULT NULL COMMENT 'App Key',
  `app_secret` varchar(255) DEFAULT NULL COMMENT 'App Secret',
  `sign_name` varchar(255) DEFAULT NULL COMMENT '默认签名',
  `is_open` int(11) DEFAULT '0' COMMENT '是否开启，0关闭，1开启',
  `admin_mobile` varchar(255) DEFAULT NULL COMMENT '管理员接收手机',
  `is_receive` int(11) DEFAULT '0' COMMENT '是否开启，0关闭，1开启',
  `sms_channel` varchar(20) NOT NULL DEFAULT 'aliyun' COMMENT '短信通道',
  `smsbao_user` varchar(50) NOT NULL DEFAULT '' COMMENT '短信宝账号',
  `smsbao_pass` varchar(50) NOT NULL DEFAULT '' COMMENT '短信宝密码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `pay_sms` WRITE;
/*!40000 ALTER TABLE `pay_sms` DISABLE KEYS */;

INSERT INTO `pay_sms` (`id`, `app_key`, `app_secret`, `sign_name`, `is_open`, `admin_mobile`, `is_receive`, `sms_channel`, `smsbao_user`, `smsbao_pass`)
VALUES
	(3,'xx','oo','聚合',0,NULL,0,'aliyun','zhanghao','mima');

/*!40000 ALTER TABLE `pay_sms` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_sms_template
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_sms_template`;

CREATE TABLE `pay_sms_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `template_code` varchar(255) DEFAULT NULL COMMENT '模板代码',
  `call_index` varchar(255) DEFAULT NULL COMMENT '调用字符串',
  `template_content` text COMMENT '模板内容',
  `ctime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `pay_sms_template` WRITE;
/*!40000 ALTER TABLE `pay_sms_template` DISABLE KEYS */;

INSERT INTO `pay_sms_template` (`id`, `title`, `template_code`, `call_index`, `template_content`, `ctime`)
VALUES
	(3,'修改支付密码','SMS_144455941 ','editPayPassword','您正在进行修改支付密码操作，验证码为：${code} ，该验证码 5 分钟内有效，请勿泄露于他人。',1512202260),
	(4,'修改登录密码','SMS_144450598','editPassword','您的验证码为：${code} ，你正在进行修改登录密码操作，该验证码3 分钟内有效，请勿泄露于他人。',1512190115),
	(5,'异地登录','SMS_144455604','loginWarning','检测到您的账号登录异常，如非本人操纵，请及时修改账号密码。',1512202260),
	(6,'申请结算','SMS_144456102','clearing','您的申请结算验证码为：${code}  ，验证码只用于平台结算验证，为了您的资金安全，打死也不能告诉任何人。',1512202260),
	(7,'委托结算','SMS_144450916','entrusted','您的验证码为：${code} ，你正在进行 委托结算 操作，该验证码 5 分钟内有效，请勿泄露于他人。',1512202260),
	(8,'绑定手机','SMS_144455941 ','bindMobile','您的验证码为：${code} ，你正在进行 绑定手机 操作，该验证码 5 分钟内有效，请勿泄露于他人。',1514534290),
	(9,'更新手机','SMS_144450938','editMobile','您的验证码为：${code} ，你正在进行 更新手机 号码操作，该验证码 5 分钟内有效，请勿泄露于他人。',1514535688),
	(10,'更新银行卡 ','SMS_144450919','addBankcardSend','您的验证码为：${code} ，你正在进行 更新银行卡 \r\n 操作，该验证码 5 分钟内有效，请勿泄露于他人。',1514535688),
	(11,'修改个人资料','SMS_144450923','saveProfile','您的验证码为：${code} ，你正在进行 修改个人资料 操作，该验证码 5 分钟内有效，请勿泄露于他人。',151453568),
	(12,'绑定管理员手机号码','SMS_144450927','adminbindMobile','您的验证码为：${code} ，你正在进行 绑定管理员手机号码 操作，该验证码 5 分钟内有效，请勿泄露于他人。',1527670734),
	(13,'修改管理员手机号码','SMS_144455951','admineditMobile','您的验证码为：${code} ，你正在进行 修改管理员手机号码 操作，该验证码 5 分钟内有效，请勿泄露于他人。',1527670734),
	(14,'批量删除订单','SMS_144455956','delOrderSend','您的验证码为：${code} ，你正在进行 批量删除订单  操作，该验证码 5 分钟内有效，请勿泄露于他人。',1527670734),
	(15,'解绑谷歌身份验证器','SMS_119087905','unbindGoogle','您的验证码为：${code} ，你正在进行 解绑谷歌身份验证器 操作，该验证码 5 分钟内有效，请勿泄露于他人。',1527670734),
	(16,'设置订单为已支付','SMS_144455959','setOrderPaidSend','您的验证码为：${code} ，你正在进行 设置订单为已支付 操作，该验证码 5 分钟内有效，请勿泄露于他人。',1527670734),
	(17,'清理数据','SMS_144450934','clearDataSend','您的验证码为：${code} ，你正在进行 清理数据 操作，该验证码 5 分钟内有效，请勿泄露于他人。',1527670734),
	(18,'增加/减少余额（冲正）','SMS_111795375','adjustUserMoneySend','您的验证码为：${code} ，你正在进行 增加/减少余额（冲正） 操作，该验证码 5 分钟内有效，请勿泄露于他人。',1527670734),
	(19,'提交代付','SMS_144450941','submitDfSend','您的提交代付验证码为：${code} ，该验证码 3 分钟内有效，请勿泄露于他人。',1527670734),
	(20,'测试短信','SMS_166779090','test','您的测试短信验证码为：${code} ，您正在进行重要操作，该验证码 5 分钟内有效，请勿泄露于他人。',1527670734),
	(21,'系统配置','SMS_144854336','sysconfigSend','您的系统配置验证码为：${code} ，该验证码 5 分钟内有效，请勿泄露于他人。',1527670734),
	(22,'客户提现提醒','SMS_144455785','tixian','平台有客户申请提现，请及时处理！',1536649511);

/*!40000 ALTER TABLE `pay_sms_template` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_systembank
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_systembank`;

CREATE TABLE `pay_systembank` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bankcode` varchar(100) DEFAULT NULL,
  `bankname` varchar(300) DEFAULT NULL,
  `images` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='结算银行';

LOCK TABLES `pay_systembank` WRITE;
/*!40000 ALTER TABLE `pay_systembank` DISABLE KEYS */;

INSERT INTO `pay_systembank` (`id`, `bankcode`, `bankname`, `images`)
VALUES
	(162,'BOB','北京银行','BOB.gif'),
	(164,'BEA','东亚银行','BEA.gif'),
	(165,'ICBC','中国工商银行','ICBC.gif'),
	(166,'CEB','中国光大银行','CEB.gif'),
	(167,'GDB','广发银行','GDB.gif'),
	(168,'HXB','华夏银行','HXB.gif'),
	(169,'CCB','中国建设银行','CCB.gif'),
	(170,'BCM','交通银行','BCM.gif'),
	(171,'CMSB','中国民生银行','CMSB.gif'),
	(172,'NJCB','南京银行','NJCB.gif'),
	(173,'NBCB','宁波银行','NBCB.gif'),
	(174,'ABC','中国农业银行','5414c87492ad8.gif'),
	(175,'PAB','平安银行','5414c0929a632.gif'),
	(176,'BOS','上海银行','BOS.gif'),
	(177,'SPDB','上海浦东发展银行','SPDB.gif'),
	(178,'SDB','深圳发展银行','SDB.gif'),
	(179,'CIB','兴业银行','CIB.gif'),
	(180,'PSBC','中国邮政储蓄银行','PSBC.gif'),
	(181,'CMBC','招商银行','CMBC.gif'),
	(182,'CZB','浙商银行','CZB.gif'),
	(183,'BOC','中国银行','BOC.gif'),
	(184,'CNCB','中信银行','CNCB.gif'),
	(193,'ALIPAY','支付宝','58b83a5820644.jpg'),
	(194,'WXZF','微信支付','58b83a757a298.jpg');

/*!40000 ALTER TABLE `pay_systembank` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_template
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_template`;

CREATE TABLE `pay_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT ' ' COMMENT '模板名称',
  `theme` varchar(255) NOT NULL DEFAULT ' ' COMMENT '模板代码',
  `is_default` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认模板:1是，0否',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `remarks` varchar(255) NOT NULL DEFAULT ' ' COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='模板表';

LOCK TABLES `pay_template` WRITE;
/*!40000 ALTER TABLE `pay_template` DISABLE KEYS */;

INSERT INTO `pay_template` (`id`, `name`, `theme`, `is_default`, `add_time`, `update_time`, `remarks`)
VALUES
	(1,' 默认模板','default',0,1524299660,1524299660,' 默认模板'),
	(2,'2018新模板','view4',1,1546583665,1546583665,'包含所有页面'),
	(3,'模板二','view2',0,1541007060,1541007060,'默认模板二，有登录页，注册页'),
	(4,'模板三','view3',0,1541007043,1541007043,'雀付优化模板-有登录页，注册页，支持手机浏览'),
	(5,'模板五','view5',0,1541007015,1541007015,'无首页-有登录页，注册页-自适应手机'),
	(6,'九州支付','view6',0,1541007031,1541007031,'九州支付,有登录页，不支持手机访问');

/*!40000 ALTER TABLE `pay_template` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_updatelog
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_updatelog`;

CREATE TABLE `pay_updatelog` (
  `version` varchar(20) NOT NULL,
  `lastupdate` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pay_user_channel_account
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_user_channel_account`;

CREATE TABLE `pay_user_channel_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `account_ids` varchar(255) NOT NULL DEFAULT '' COMMENT '子账号id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启指定账号',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户指定指账号';



# Dump of table pay_user_code
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_user_code`;

CREATE TABLE `pay_user_code` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(11) DEFAULT '0' COMMENT '0找回密码',
  `code` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `status` int(11) DEFAULT '0',
  `ctime` int(11) DEFAULT NULL,
  `uptime` int(11) DEFAULT NULL COMMENT '更新时间',
  `endtime` int(11) DEFAULT NULL COMMENT '有效时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table pay_user_riskcontrol_config
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_user_riskcontrol_config`;

CREATE TABLE `pay_user_riskcontrol_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `min_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔最小金额',
  `max_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔最大金额',
  `unit_all_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单位时间内交易总金额',
  `all_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '当天交易总金额',
  `start_time` tinyint(10) unsigned NOT NULL DEFAULT '0' COMMENT '一天交易开始时间',
  `end_time` tinyint(10) unsigned NOT NULL DEFAULT '0' COMMENT '一天交易结束时间',
  `unit_number` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间内交易的总笔数',
  `is_system` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否平台规则',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态:1开通，0关闭',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `edit_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `time_unit` char(1) NOT NULL DEFAULT 'i' COMMENT '限制的时间单位',
  `unit_interval` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间值',
  `domain` varchar(255) NOT NULL DEFAULT ' ' COMMENT '防封域名',
  `systemxz` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 系统规则 1 用户规则',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='交易配置';



# Dump of table pay_userrate
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_userrate`;

CREATE TABLE `pay_userrate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `payapiid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '通道ID',
  `feilv` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '运营费率',
  `fengding` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '封顶费率',
  `t0feilv` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'T0运营费率',
  `t0fengding` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'T0封顶费率',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商户通道费率';



# Dump of table pay_version
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_version`;

CREATE TABLE `pay_version` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL DEFAULT '0' COMMENT '版本',
  `author` varchar(255) NOT NULL DEFAULT ' ' COMMENT '作者',
  `save_time` varchar(255) NOT NULL DEFAULT '0000-00-00' COMMENT '修改时间,格式YYYY-mm-dd',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据库版本表';

LOCK TABLES `pay_version` WRITE;
/*!40000 ALTER TABLE `pay_version` DISABLE KEYS */;

INSERT INTO `pay_version` (`id`, `version`, `author`, `save_time`)
VALUES
	(1,'5.5','qq0000000','2018-4-8'),
	(2,'5.6','qq0000000','2018/9/02 17:45:33'),
	(3,'5.7.1','qq0000000','2018-4-17');

/*!40000 ALTER TABLE `pay_version` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pay_websiteconfig
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pay_websiteconfig`;

CREATE TABLE `pay_websiteconfig` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `websitename` varchar(300) DEFAULT NULL COMMENT '网站名称',
  `domain` varchar(300) DEFAULT NULL COMMENT '网址',
  `email` varchar(100) DEFAULT NULL,
  `tel` varchar(30) DEFAULT NULL,
  `qq` varchar(30) DEFAULT NULL,
  `directory` varchar(100) DEFAULT NULL COMMENT '后台目录名称',
  `icp` varchar(100) DEFAULT NULL,
  `tongji` varchar(1000) DEFAULT NULL COMMENT '统计',
  `login` varchar(100) DEFAULT NULL COMMENT '登录地址',
  `payingservice` tinyint(1) unsigned DEFAULT '0' COMMENT '商户代付 1 开启 0 关闭',
  `authorized` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '商户认证 1 开启 0 关闭',
  `invitecode` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '邀请码注册',
  `company` varchar(200) DEFAULT NULL COMMENT '公司名称',
  `serverkey` varchar(50) DEFAULT NULL COMMENT '授权服务key',
  `withdraw` tinyint(1) DEFAULT '0' COMMENT '提现通知：0关闭，1开启',
  `login_warning_num` tinyint(3) unsigned NOT NULL DEFAULT '3' COMMENT '前台可以错误登录次数',
  `login_ip` varchar(1000) NOT NULL DEFAULT ' ' COMMENT '登录IP',
  `is_repeat_order` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许重复订单:1是，0否',
  `google_auth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启谷歌身份验证登录',
  `df_api` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启代付API',
  `logo` varchar(255) NOT NULL DEFAULT ' ' COMMENT '公司logo',
  `random_mchno` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启随机商户号',
  `register_need_activate` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户注册是否需激活',
  `admin_alone_login` tinyint(1) NOT NULL DEFAULT '0' COMMENT '管理员是否只允许同时一次登录',
  `max_auth_error_times` int(10) NOT NULL DEFAULT '5' COMMENT '验证错误最大次数',
  `auth_error_ban_time` int(10) NOT NULL DEFAULT '10' COMMENT '验证错误超限冻结时间（分钟）',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `pay_websiteconfig` WRITE;
/*!40000 ALTER TABLE `pay_websiteconfig` DISABLE KEYS */;

INSERT INTO `pay_websiteconfig` (`id`, `websitename`, `domain`, `email`, `tel`, `qq`, `directory`, `icp`, `tongji`, `login`, `payingservice`, `authorized`, `invitecode`, `company`, `serverkey`, `withdraw`, `login_warning_num`, `login_ip`, `is_repeat_order`, `google_auth`, `df_api`, `logo`, `random_mchno`, `register_need_activate`, `admin_alone_login`, `max_auth_error_times`, `auth_error_ban_time`)
VALUES
	(1,'充值平台','xx.com','000000@qq.com','17000000000000','0000000','admin','','','Login',1,1,0,'支付','0d6de302cbc615de3b09463acea87662',0,3,' ',0,0,1,'',0,1,0,10,30);

/*!40000 ALTER TABLE `pay_websiteconfig` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
