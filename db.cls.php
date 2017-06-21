<?php

/* * ************************************************
  #	Version 1.2		PHP MySQL JavaScript
  #	Copyright (c) 2009 http://www.fangbian123.com
  #	Author: Li Zhixiao <English Name: Hawking E-mail:578731186@qq.com QQ:578731186>
  #	Date: 2009/10/10
  #	Modified by Li Zhixiao	2014/03/14
 * ************************************************ */

/**
 * 功能：数据库的基础操作类
 * */
class DB {

    private $conn = "";         //定义数据库连接变量

    /**
     * 功能：初始化构造函数，连接数据库
     */

    public function __construct() {
        try {           //捕获连接错误并显示错误文件
            $conn = mysql_connect(SERVERNAME, USERNAME, PASSWORD);
            @mysql_query("SET NAMES 'utf8'");
//			@mysql_query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
        } catch (Exception $e) {
            $msg = $e;
            include(ERRFILE);
        }
        try {           //捕获数据库选择错误并显示错误文件
            mysql_select_db(DBNAME, $conn);
        } catch (Exception $e) {
            $msg = $e;
            include(ERRFILE);
        }
        $this->conn = $conn;
    }

    /**
     * 功能：获取mysql版本
     * 返回：版本号
     */
    public function GetMySQLVer() {
        return mysql_get_server_info();
    }

    /**
     * 功能：获取mysql当前数据库大小
     * 返回：版本号
     */
    public function GetMySQLMount() {
        $mount = 0;
        if ($res = mysql_query('SHOW TABLE STATUS FROM ' . DBNAME)) {
            while ($row = mysql_fetch_array($res)) {
                $mount += $row['Data_length'];
                $mount += $row['Index_length'];
                $mount += $row['Data_free'];
//				mysql_free_result($res);
            }
        }
        return $mount . ' kB ';
    }

    /**
     * 功能：数据库查询函数
     * 参数：$sql SQL语句
     * 返回：二唯数组或false
     */
    public function Select($sql = "") {
        if (empty($sql))
            return false;     //如果SQL语句为空则返回FALSE
        if (empty($this->conn))
            return false;   //如果连接为空则返回FALSE
        try {           //捕获数据库选择错误并显示错误文件
            $results = mysql_query($sql, $this->conn);
        } catch (Exception $e) {
            $msg = $e;
            include(ERRFILE);
        }
        if ((!$results) or ( empty($results))) {   //如果查询结果为空则释放结果并返回FALSE
            @mysql_free_result($results);
            return false;
        }

        $count = 0;
        $data = array();

        while ($row = @mysql_fetch_array($results)) {  //把查询结果重组成一个二维数组
            $data[$count] = $row;
            $count++;
        }

        @mysql_free_result($results);

        return $data;
    }

    /**
     * 功能：数据插入函数
     * 参数：$sql SQL语句
     * 返回：0或新插入数据的ID
     */
    public function Insert($sql = "") {
        if (empty($sql))
            return 0;      //如果SQL语句为空则返回FALSE
        if (empty($this->conn))
            return 0;    //如果连接为空则返回FALSE
        try {           //捕获数据库选择错误并显示错误文件
            $results = mysql_query($sql, $this->conn);
        } catch (Exception $e) {
            $msg = $e;
            include(ERRFILE);
        }
        if (!$results)          //如果插入失败返回0，否则返回当前插入数据id
            return 0;
        else
            return @mysql_insert_id($this->conn);
    }

    /**
     * 功能：数据更新函数
     * 参数：$sql SQL语句
     * 返回：TRUE OR FALSE
     */
    public function Update($sql = "") {
        if (empty($sql))
            return false;     //如果SQL语句为空则返回FALSE
        if (empty($this->conn))
            return false;   //如果连接为空则返回FALSE
        try {           //捕获数据库选择错误并显示错误文件
            $result = mysql_query($sql, $this->conn);
        } catch (Exception $e) {
            $msg = $e;
            include(ERRFILE);
        }
        return $result;
    }

    /**
     * 功能：数据删除函数
     * 参数：$sql SQL语句
     * 返回：TRUE OR FALSE
     */
    public function Delete($sql = "") {
        if (empty($sql))
            return false;     //如果SQL语句为空则返回FALSE
        if (empty($this->conn))
            return false;   //如果连接为空则返回FALSE
        try {
            $result = mysql_query($sql, $this->conn);
        } catch (Exception $e) {
            $msg = $e;
            include(ERRFILE);
        }
        return $result;
    }

    /**
     * 功能：定义事务
     */
    public function BeginTransaction() {
        mysql_query("SET  AUTOCOMMIT=0");    //设置为不自动提交，因为MYSQL默认立即执行
        mysql_query("BEGIN");       //开始事务定义
    }

    /**
     * 功能：回滚
     */
    public function Rollback() {
        mysql_query("ROOLBACK");
    }

    /**
     * 功能：提交执行
     */
    public function Commit() {
        mysql_query("COMMIT");
    }

    /**
     * 功能：提取指定表的指定id的记录
     * 参数：$id 表id,$name 表名称
     * 返回：数组
     */
    public function GetInfo($id, $name) {
        $sql = "SELECT * FROM $name WHERE id=$id";
        $r = $this->select($sql);
        return $r[0];
    }

    /**
     * 功能：向指定表中插入数据
     * 参数：$name 表名称,$data 数组(格式：$data['字段名'] = 值)
     * 返回：插入记录id
     */
    public function InsertData($name, $data) {
        $field = implode(',', array_keys($data));   //定义sql语句的字段部分
        $i = 0;
        foreach ($data as $key => $val) {
            $value .= "'" . $val . "'";
            if ($i < count($data) - 1)       //判断是否到数组的最后一个值
                $value .= ",";
            $i++;
        }
        $sql = "INSERT INTO $name ($field) VALUES ($value)";
        return $this->insert($sql);
    }

    /**
     * 功能：更新指定表指定id的调查表记录
     * 参数：$name 表名称,$id 表id,$data 数组(格式：$data['字段名'] = 值)
     * 返回：TRUE OR FALSE
     */
    public function UpdateData($name, $id, $data) {
        $col = array();
        foreach ($data as $key => $value) {
            $col[] = $key . "='" . $value . "'";
        }
        $sql = "UPDATE $name SET " . implode(',', $col) . " WHERE id = $id";
        return $this->update($sql);
    }

    /**
     * 功能：删除指定id的表记录
     * 参数：$id表id,$name 表名称
     * 返回：TRUE OR FALSE
     */
    public function DelData($id, $name) {
        $sql = "DELETE FROM $name WHERE id = $id";
        return $this->delete($sql);
    }

    /**
     * 功能：删除多个id的表记录
     * 参数：$idStrArr id数组,$name 表名称
     * 返回：TRUE OR FALSE
     */
    public function DelArrIdData($idStrArr, $name) {
        $sql = "DELETE FROM $name WHERE id IN ($idStrArr)";
        return $this->delete($sql);
    }

    /**
     * 功能：虚拟删除多个id的表记录
     * 参数：$idStrArr id数组,$name 表名称，$columnSet 需要设置的属性
     * 返回：TRUE OR FALSE
     */
    public function VirtualDelArrIdInfo($idStrArr, $name, $columnSet) {
        $sql = "UPDATE $name SET $columnSet=1 WHERE id IN ($idStrArr)";
        return $this->update($sql);
    }

    /**
     * 功能：关闭数据库连接
     * 返回：无
     */
    public function Close() {
        mysql_close($this->conn);
    }

    /**
     * 功能：比较日期
     * 参数：$d1,$d2时间参数
     * 返回：秒值
     */
    public function DateDiff($d1, $d2) {
        if (is_string($d1))
            $d1 = strtotime($d1);
        if (is_string($d2))
            $d2 = strtotime($d2);
        return ($d2 - $d1); //这里返回的是秒值，如果除以3600就是返回小时了，依此类推
    }

    /**
     * 功能：数据库备份
     * 参数：$dbname,$filepath
     */
    function DataBackup($dbname, $filepath) {
        $tables = mysql_list_tables($dbname);
        while ($table = mysql_fetch_row($tables)) {
            $cachetables[$table[0]] = $table[0];
            $tableselected[$table[0]] = 1;
        }

        $table = $cachetables;
        $filehandle = fopen($filepath, "w");
        $result = mysql_query("SHOW tables");
        while ($currow = mysql_fetch_array($result)) {
            if (isset($table[$currow[0]])) {
                $this->SQLDumpTable($currow[0], $filehandle);
                fwrite($filehandle, "\r\n\r\n\r\n");
            }
        }
        fclose($filehandle);
    }

    /**
     * 功能：data dump functions
     * 参数：$table,$fp
     */
    function SQLDumpTable($table, $fp = 0) {
        $tabledump = "DROP TABLE IF EXISTS $table;\r\n";
        $result = mysql_fetch_array(mysql_query("SHOW CREATE TABLE $table"));
        //echo "SHOW CREATE TABLE $table";
        $tabledump .= $result[1] . ";\r\n";

        if ($fp) {
            fwrite($fp, $tabledump);
        } else {
            echo $tabledump;
        }
        // get data
        $rows = mysql_query("SELECT * FROM $table");
        // $numfields=$DB->num_fields($rows);
        $numfields = mysql_num_fields($rows);
        while ($row = mysql_fetch_array($rows)) {
            $tabledump = "INSERT INTO $table VALUES(";

            $fieldcounter = -1;
            $firstfield = 1;
            // get each field's data
            while (++$fieldcounter < $numfields) {
                if (!$firstfield) {
                    $tabledump .= ", ";
                } else {
                    $firstfield = 0;
                }

                if (!isset($row[$fieldcounter])) {
                    $tabledump .= "NULL";
                } else {
                    $tabledump .= "'" . mysql_escape_string($row[$fieldcounter]) . "'";
                }
            }

            $tabledump .= ");\r\n";

            if ($fp) {
                fwrite($fp, $tabledump);
            } else {
                echo $tabledump;
            }
        }
        mysql_free_result($rows);
    }

    /**
     * 功能：数据库还原
     * 参数：$dbname,$filepath
     * 修改建议：file_get_contents，使用正则表达式去掉注释部分
     */
    public function RestoreDB($filepath, $dbname = '') {
        if ($dbname != '') {
            @mysql_query("USE `$dbname`");
        }
        $sql_str = @file_get_contents($filepath);
        $sql_str = preg_replace('/\/\*([\S\s]*?)\*\/(;)?/', '', $sql_str);
        $sql_str = str_replace('USE', ';USE', $sql_str);
        //$sql_str=str_replace("\n",'',$sql_str);
        //$sql_str=str_replace("\r",'',$sql_str);
        $sql_str_Arr = explode(';\r\n', $sql_str);
        foreach ($sql_str_Arr as $key => $val) {
            if (trim($val) != '') {
                @mysql_query(trim($val));
            }
        }
    }

}

/*
  header("Content-Type:text/html;charset=utf-8");

  define("USERNAME","db");									//数据库连接用户名
  define("PASSWORD","qs-db");									//数据库连接密码
  define("SERVERNAME","localhost");							//数据库服务器的名称
  define("DBNAME","qscms");									//数据库名称

  $db=new DB();
  $dbname='qscms';
  $filepath='lstd_sp.sql';
  //$db->DataBackup($dbname,$filepath);
  //$db->RestoreDB($filepath,$dbname);


 */
?>
