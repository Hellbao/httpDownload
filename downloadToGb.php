<?php

include 'config.ini.php';
include 'db.cls.php';
//include 'file.cls.php';
//本地拷贝下载

$db = new DB();
//$file = new FILE();

$fromPath = 'E:/qs_resources/common/';
$toPath = 'E:/' . iconv("UTF-8", "gb2312", "科普中国") . '/';

$oldSql = "select c.id,c.title,c.cat_id,c.thumbnail,a.file_path,ca.cat_name FROM contentinfo c left join attachment a on c.id=a.content_id left join contentcat ca on ca.id=c.cat_id WHERE c.category like '.154.%' and c.add_time<='2017-06-15 00:00:00' ORDER BY c.id desc";
$newSql = "select c.id,c.title,c.cat_id,c.thumbnail,a.file_path,ca.cat_name FROM contentinfo c left join attachment a on c.id=a.content_id left join contentcat ca on ca.id=c.cat_id WHERE c.category like '.154.%' and c.add_time>'2017-06-15 00:00:00' ORDER BY c.id desc";

$Res = $db->Select($oldSql);
$Dir = 'old';
//var_dump($oldRes[0]);
$sum = count($Res);

$dx = "------------------------------------------------------------------------------------------------------------";
$num = 0;


for ($i = 0; $i < $sum; $i++) {
    echo iconv("UTF-8", "gb2312", "\r\r\n共计" . $sum . "条     剩余" . ($sum - $num) . "条\r\r\n" . $dx);
    $extImg = explode('.', $Res[$i]['thumbnail']);
    $extImg = $extImg[count($extImg) - 1];

    $extVideo = explode('.', $Res[$i]['file_path']);
    $extVideo = $extVideo[count($extVideo) - 1];

    $fileImgFrom = $fromPath . $Res[$i]['thumbnail'];
    $fileVideoFrom = $fromPath . $Res[$i]['file_path'];

    $fileDir = $toPath . $Dir . '/' . iconv("UTF-8", "gb2312", $Res[$i]['cat_name']);

    $fileImgTo = $toPath . $Dir . '/' . iconv("UTF-8", "gb2312", $Res[$i]['cat_name']) . '/' . iconv("UTF-8", "gb2312", $Res[$i]['title']) . '.' . $extImg;
    $fileVideoTo = $toPath . $Dir . '/' . iconv("UTF-8", "gb2312", $Res[$i]['cat_name']) . '/' . iconv("UTF-8", "gb2312", $Res[$i]['title']) . '.' . $extVideo;


    if (!is_dir($fileDir)) {
        mkdir($fileDir, 0777, true);
    }
    if (!file_exists($fileImgTo)) {
        if (file_exists($fileImgFrom)) {
            $imgFlag = copy($fileImgFrom, $fileImgTo);
            if ($imgFlag) {
                echo iconv("UTF-8", "gb2312", "\r\r\nimg---拷贝成功\r\r\n" . $dx);
            } else {
                echo iconv("UTF-8", "gb2312", "\r\r\nimg---拷贝失败\r\r\n" . $dx);
            }
        } else {
            echo iconv("UTF-8", "gb2312", "\r\r\nimg---源文件不存在\r\r\n" . $dx);
        }
    } else {
        echo iconv("UTF-8", "gb2312", "\r\r\nimg---exists\r\r\n" . $dx);
    }

    if (!file_exists($fileVideoTo)) {

        if (file_exists($fileVideoFrom)) {
            $videoFlag = copy($fileVideoFrom, $fileVideoTo);
            if ($videoFlag) {
                echo iconv("UTF-8", "gb2312", "\r\r\nvideo---拷贝成功\r\r\n" . $dx);
            } else {
                echo iconv("UTF-8", "gb2312", "\r\r\nvideo---拷贝失败\r\r\n" . $dx);
            }
        } else {
            echo iconv("UTF-8", "gb2312", "\r\r\nvideo---源文件不存在\r\r\n" . $dx);
        }
    } else {
        echo iconv("UTF-8", "gb2312", "\r\r\nvideo---exists\r\r\n" . $dx);
    }
    $num++;
}
$db->Close();
sleep(10);
?>