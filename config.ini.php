<?php
/**************************************************
#	Version 1.2		PHP MySQL JavaScript
#	Copyright (c) 2009 http://www.fangbian123.com
#	Author: Li Zhixiao <English Name: Hawking E-mail:578731186@qq.com QQ:578731186>
#	Date: 2009/10/10
**************************************************/
header("Content-Type:text/html;charset=utf-8");
ini_set('date.timezone','Asia/Shanghai');					//设置默认时间为北京时间
set_magic_quotes_runtime(0);
//session_start();
set_time_limit(0); 

define("LIMIT_SIZE",2*1024*1024);		//文件上传大小限制

define("DOWNLOAD_PATH","E:\qs_resources\common\\");
define("XML_PATH","http://vms.kepuchina.cn/v2/spk.xml");	
//define("DOWNLOAD_PATH","E:\common\\");
define("USERNAME","root");									//数据库连接用户名
//define("PASSWORD","123");									//数据库连接密码
define("PASSWORD","admin");	
define("SERVERNAME","localhost");							//数据库服务器的名称
define("DBNAME","qs_source");                        //数据库名称



?>