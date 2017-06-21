<?php

include 'config.ini.php';
include 'db.cls.php';
//include 'file.cls.php';

//去重

$db = new DB();
//$file = new FILE();
$uniqSql = "select id,title,count(cat_id) as countnum,category FROM contentinfo WHERE category like '.154.%' GROUP BY title ORDER BY countnum DESC";
$res = $db->Select($uniqSql);
for($i=0;$i<count($res);$i++){
if($res[$i]['countnum']>1){
  $uniqRes[$i]=$res[$i];
}
}
$dx = "------------------------------------------------------------------------------------------------------------";
$sum=count($uniqRes);
echo iconv("UTF-8", "gb2312", "\r\r\n共计重复   ".$sum."\r\r\n".$dx);
$num=1;
foreach($uniqRes as $v){
    $contentSql="select * from contentinfo where title='".$v['title']."' and category like '.154.%' order by id asc";
	$contentRes = $db->Select($contentSql);
	$count=count($contentRes);
	$limitSql="select * from contentinfo where title='".$v['title']."' and category like '.154.%' order by id asc limit ".($count-1);
	$limitRes = $db->Select($limitSql);	
	foreach($limitRes as $va){
	    $attachSql="select * from attachment where content_id=".$va['id'];
		$attachRes = $db->Select($attachSql);

		if(count($attachRes)>0){
		    $attachUrl="E:/qs_resources/common/".$attachRes[0]['file_path'];
			$delAFlag=@unlink($attachUrl);
			if($delAFlag!==false){
			    $delAttach=$db->delete('delete from attachment where id='.$attachRes[0]['id']);
			    echo iconv("UTF-8", "gb2312", "\r\r\ndeleteattach   ".$attachUrl."\r\r\n".$dx);
			}
		}
		if($va['thumbnail']!==''){
		   $imgUrl="E:/qs_resources/common/".$va['thumbnail'];
		   $delIFlag=@unlink($imgUrl);
			if($delIFlag!==false){
			    //$delAttach=$db->delete($va['id'])
				echo iconv("UTF-8", "gb2312", "\r\r\ndeleteimg   ".$imgUrl."\r\r\n".$dx);
			}
		
		}
		$delA=$db->delete('delete from contentinfo where id='.$va['id']);
		echo iconv("UTF-8", "gb2312", "\r\r\ndelete   ".'no:'.$va['id'].'    title:'.$va['title']."      剩余".($sum-$num)."\r\r\n".$dx);		
	    
	}
	$num++;
}
//var_dump($uniqRes);
$db->Close();
sleep(10);
?>