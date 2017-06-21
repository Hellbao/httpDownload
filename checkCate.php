<?php

include 'config.ini.php';
include 'db.cls.php';
//include 'file.cls.php';

//本地拷贝下载

$db = new DB();
//$file = new FILE();

//$oldSql = "select c.id,c.title,c.cat_id,c.t FROM contentinfo c WHERE category = '.154.%' and add_time<='2017-06-15 00:00:00' ORDER BY id desc";
//$newSql = "select id,title,cat_id FROM contentinfo c WHERE category = '.154.%' and add_time>'2017-06-15 00:00:00' ORDER BY id desc";


//$oldRes = $db->Select($oldSql);
//$newRes = $db->Select($newSql);



$xmlUrl = XML_PATH;
$xml = simplexml_load_file($xmlUrl, null, LIBXML_NOCDATA);
$data = json_decode(json_encode($xml), TRUE);

foreach ($data["resources"]["resource"] as $v) {
   switch ($v['category']) {
    case '航空航天':
	    $cate['cat_id']=155;
		$cate['category']='.154.155.';
        break;
    case '科学百科':
	    $cate['cat_id']=158;
		$cate['category']='.154.158.';
        break;
    case '健康医疗':
	    $cate['cat_id']=286;
		$cate['category']='.154.286.';
        break;
    case '气候环境':
	    $cate['cat_id']=159;
		$cate['category']='.154.159.';
        break;
    case '应急避险':
	    $cate['cat_id']=160;
		$cate['category']='.154.160.';
        break;
    case '前沿技术':
	    $cate['cat_id']=157;
		$cate['category']='.154.157.';
        break;
    case '能源利用':
	    $cate['cat_id']=287;
		$cate['category']='.154.287.';
        break;
    case '食品安全':
	    $cate['cat_id']=288;
		$cate['category']='.154.288.';
        break;
    case '信息科技':
	    $cate['cat_id']=289;
		$cate['category']='.154.289.';
        break;	
    case '其他':
	    $cate['cat_id']=255;
		$cate['category']='.154.255.';
        break;		
    default:
	    $cate['cat_id']=255;
		$cate['category']='.154.255.';
        break;
}

$selsql="select id,title from contentinfo where add_time>'2017-06-15 00:00:00' and title='".$v['title']."'";
$selres=$db->select($selsql);
$sql = "UPDATE contentinfo SET cat_id=".$cate['cat_id'].",category='".$cate['category']."' WHERE id =".$selres[0]['id'];
$flag=$db->update($sql);
echo $selres[0]['title'].'------>'.$cate['cat_id'].'------>('.$flag.')<br>';

   


}

$db->Close();
//sleep(10);
?>