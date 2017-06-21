<?php

include 'config.ini.php';
include 'db.cls.php';
include 'file.cls.php';
include 'HttpDownload.cls.php';

//set_time_limit(0);
$xmlUrl = XML_PATH;
$xml = simplexml_load_file($xmlUrl, null, LIBXML_NOCDATA);
$data = json_decode(json_encode($xml), TRUE);

$db = new DB();
$file = new FILE();
$fileDownload = new HttpDownload();
$fail_video = array();

$sumCount = count($data["resources"]["resource"]);
echo iconv("UTF-8", "gb2312", "***********************************************************************\r\r\n\r\r\n本次XML解析出" . $sumCount . "条数据\r\r\n\r\r\n***********************************************************************\r\r\n");


$dx = "------------------------------------------------------------------------------------------------------------";
$nbsp = "   ";
$offset = 1;

foreach ($data["resources"]["resource"] as $resource) {
    $sql = "SELECT resource_id FROM resource_temp";
    $url_res = $db->Select($sql);
    if (count($url_res) == 0) {
        $urlArr = array();
    } else {
        $urlArr = array();
        for ($i = 0; $i < count($url_res); $i++) {
            array_push($urlArr, $url_res[$i]['resource_id']);
        }
    }

    if ((count($url_res) > 0 && !in_array($resource['resource_id'], $urlArr)) || count($url_res) == 0) {
        $time = time();
        echo iconv("UTF-8", "gb2312", "\r\r\n共" . $sumCount . "条，剩余" . ($sumCount - $offset) . "条");
        echo iconv("UTF-8", "gb2312", "\r\r\n" . $dx . "\r\r\n编号：" . $resource['resource_id'] . $nbsp . $resource['title'] . $nbsp . date('Y-m-d H:i:s', $time) . $nbsp . "正在下载ing...");
        $y = date('Y', $time);
        $m = date('m', $time);
        $d = date('d', $time);

        $rootpath = DOWNLOAD_PATH;
        $savepath = 'upload/' . $y . '/' . $m . '/' . $d . '/';

//附件名
        $file_temp = explode("/", $resource['video_file']);
        $filename = $file_temp[count($file_temp) - 1];

        $thumbnail_temp = explode("/", $resource['guide_image']);
        $thumbnailname = $thumbnail_temp[count($thumbnail_temp) - 1];

//分类归属
        $cat_sql = "SELECT cat_name FROM contentcat where upper_id=154";
        $cat_result = $db->Select($cat_sql);
        $arrCat = array();
        for ($i = 0; $i < count($cat_result); $i++) {
            array_push($arrCat, $cat_result[$i]['cat_name']);
        }


        $cat_name = "";
        for ($i = 0; $i < count($arrCat); $i++) {
            if (strpos($resource['category'], $arrCat[$i])) {
                $cat_name = $arrCat[$i];
                break;
            } else {
                if ($i == (count($arrCat) - 1)) {
                    if (!in_array("其它", $arrCat)) {
                        $contentcat['cat_name'] = "其它";
                        $contentcat['upper_cat'] = ".154.";
                        $contentcat['upper_id'] = "154";
                        $cat_id = $db->InsertData('contentcat', $contentcat);
                        if ($cat_id) {
                            $cat_name = "其它";
                        }
                    } else {
                        $cat_name = "其它";
                    }
                }
            }
        }

        $cat_id_sql = 'SELECT id,upper_cat FROM contentcat where cat_name="' . $cat_name . '"';
        $cat_id_res = $db->Select($cat_id_sql);

        $contentinfo['cat_id'] = $cat_id_res[0]['id']; //获取分类id
        $contentinfo['category'] = $cat_id_res[0]['upper_cat'] . $cat_id_res[0]['id'] . '.';  //获取分类category
        $contentinfo['admin_id'] = "1";
        $contentinfo['title'] = $resource['title'];
        $contentinfo['type'] = "3";
        $contentinfo['recOption'] = "10,11,20,21,3";

        $contentinfo['is_recommended'] = "1";
        $contentinfo['brief_ctnt'] = $resource['title'];
        $contentinfo['content'] = $resource['title'];
        $contentinfo['brief_title'] = $resource['title'];
        $contentinfo['source'] = "本站";
        $contentinfo['time_length'] = $resource['duration'];
        $contentinfo['year'] = $y;

//        var_dump($contentinfo);exit;


        if ($file->is_path_exist($rootpath . $savepath)) {
            $fileDownload->OpenUrl($resource['video_file']);
            if ($fileDownload->SaveToBin($rootpath . $savepath . $filename)) {
                if (strstr($resource['guide_image'], "http://")) {
                    $fileDownload->OpenUrl($resource['guide_image']);
                    if ($fileDownload->SaveToBin($rootpath . $savepath . $thumbnailname)) {
                        $contentinfo['thumbnail'] = $savepath . $thumbnailname;  //缩略图
                        $contentinfo['thumbnails_size'] = "200*110";    //缩略图大小

                        $content_id = $db->InsertData('contentinfo', $contentinfo);
//                        var_dump($content_id);exit;
                        if ($content_id) {
                            $attachment['content_id'] = $content_id;
                            $attachment['file_path'] = $savepath . $filename;
                            $attachment['file_ext'] = $resource['format'];
                            $attachment['file_size'] = substr(number_format(filesize($rootpath . $savepath . $filename) / (1024 * 1024), 2), 0, -1) . 'M';
                            $attachment['year'] = $y;
                            $attachment['file_name'] = $resource['title'];
                            $attachment_id = $db->InsertData('attachment', $attachment);
                            if ($attachment_id) {
                                $resource_temp['resource_id'] = (int) $resource['resource_id'];
                                $temp_id = $db->InsertData('resource_temp', $resource_temp);

                                if (!$temp_id) {
                                    //插入临时对比表失败
                                    $res = "fail_temp";
                                    echo iconv("UTF-8", "gb2312", "\r\r\n\r\r\n" . date('Y-m-d H:i:s', $time) . "----507----插入临时对比表失败！\r\r\n\r\r\n");
                                    break;
                                }
                            } else {
                                //插入附件失败
                                echo iconv("UTF-8", "gb2312", "\r\r\n\r\r\n" . date('Y-m-d H:i:s', $time) . "-----508---插入附件失败！\r\r\n\r\r\n");
                                $res = "fail_attachment";
                                break;
                            }
                        } else {
                            //插入资源信息失败
                            $res = "fail_contentinfo";
                            echo iconv("UTF-8", "gb2312", "\r\r\n\r\r\n" . date('Y-m-d H:i:s', $time) . "----509----插入资源信息失败！\r\r\n\r\r\n");
                            break;
                        }
                    } else {
                        //缩略图下载失败
                        //$res="fail_thumnail";
                        //break;
                        $contentinfo['thumbnail'] = "";  //缩略图
                        $contentinfo['thumbnails_size'] = "";    //缩略图大小
                        $content_id = $db->InsertData('contentinfo', $contentinfo);
                        if ($content_id) {
                            $attachment['content_id'] = $content_id;
                            $attachment['file_path'] = $savepath . $filename;
                            $attachment['file_ext'] = $resource['format'];
                            $attachment['file_size'] = substr(number_format(filesize($rootpath . $savepath . $filename) / (1024 * 1024), 2), 0, -1) . 'M';
                            $attachment['year'] = $y;
                            $attachment['file_name'] = $resource['title'];
                            $attachment_id = $db->InsertData('attachment', $attachment);
                            if ($attachment_id) {
                                $resource_temp['resource_id'] = (int) $resource['resource_id'];
                                $temp_id = $db->InsertData('resource_temp', $resource_temp);
                                if (!$temp_id) {
                                    //插入临时对比表失败
                                    $res = "fail_temp";
                                    echo iconv("UTF-8", "gb2312", "\r\r\n\r\r\n" . date('Y-m-d H:i:s', $time) . "----501----插入临时对比表失败！\r\r\n\r\r\n");
                                    break;
                                }
                            } else {
                                //插入附件失败
                                $res = "fail_attachment";
                                echo iconv("UTF-8", "gb2312", "\r\r\n\r\r\n" . date('Y-m-d H:i:s', $time) . "----502----插入附件失败！\r\r\n\r\r\n");
                                break;
                            }
                        } else {
                            //插入资源信息失败
                            $res = "fail_contentinfo";
                            echo iconv("UTF-8", "gb2312", "\r\r\n\r\r\n" . date('Y-m-d H:i:s', $time) . "----503----插入资源信息失败！\r\r\n\r\r\n");
                            break;
                        }
                    }
                } else {
                    $contentinfo['thumbnail'] = "";  //缩略图
                    $contentinfo['thumbnails_size'] = "";    //缩略图大小
                    $content_id = $db->InsertData('contentinfo', $contentinfo);
                    if ($content_id) {
                        $attachment['content_id'] = $content_id;
                        $attachment['file_path'] = $savepath . $filename;
                        $attachment['file_ext'] = $resource['format'];
                        $attachment['file_size'] = substr(number_format(filesize($rootpath . $savepath . $filename) / (1024 * 1024), 2), 0, -1) . 'M';
                        $attachment['year'] = $y;
                        $attachment['file_name'] = $resource['title'];
                        $attachment_id = $db->InsertData('attachment', $attachment);
                        if ($attachment_id) {
                            $resource_temp['resource_id'] = (int) $resource['resource_id'];
                            $temp_id = $db->InsertData('resource_temp', $resource_temp);
                            if (!$temp_id) {
                                //插入临时对比表失败
                                $res = "fail_temp";
                                echo iconv("UTF-8", "gb2312", "\r\r\n\r\r\n" . date('Y-m-d H:i:s', $time) . "----504----插入临时对比表失败！\r\r\n\r\r\n");
                                break;
                            }
                        } else {
                            //插入附件失败
                            $res = "fail_attachment";
                            echo iconv("UTF-8", "gb2312", "\r\r\n\r\r\n" . date('Y-m-d H:i:s', $time) . "----505----插入附件失败！\r\r\n\r\r\n");
                            break;
                        }
                    } else {
                        //插入资源信息失败
                        $res = "fail_contentinfo";
                        echo iconv("UTF-8", "gb2312", "\r\r\n\r\r\n" . date('Y-m-d H:i:s', $time) . "----506----插入资源信息失败！\r\r\n\r\r\n");
                        break;
                    }
                }
            } else {
                //视频下载失败
                array_push($fail_video, $resource['video_file']);
                //$res="fail_video";	
                //break;
            }
        }
    } else {
        $time = time();
        echo iconv("UTF-8", "gb2312", "\r\r\n共" . $sumCount . "条，剩余" . ($sumCount - $offset) . "条");
        echo iconv("UTF-8", "gb2312", "\r\r\n" . $dx . "\r\r\n编号：" . $resource['resource_id'] . $nbsp . $resource['title'] . $nbsp . date('Y-m-d H:i:s', $time) . $nbsp . "对比已下载");
		//$catarr[]=$resource['category'];
	    
    }

    if ($res != "fail_temp" && $res != "fail_attachment" && $res != "fail_contentinfo" && $res != "fail_thumnail" && $res != "fail_video" && $res != "fail") {
        echo iconv("UTF-8", "gb2312", "\r\r\n\r\r\n编号：" . $resource['resource_id'] . $nbsp . $resource['title'] . $nbsp . date('Y-m-d H:i:s', $time) . $nbsp . "下载成功！\r\r\n" . $dx);
        //var_dump($fail_video);
    } else {
        echo iconv("UTF-8", "gb2312", "\r\r\n\r\r\n编号：" . $resource['resource_id'] . $nbsp . $resource['title'] . $nbsp . date('Y-m-d H:i:s', $time) . $nbsp . "下载失败！\r\r\n" . $dx);
    }
    $offset++;
}
$db->Close();
//sleep(10);
?>