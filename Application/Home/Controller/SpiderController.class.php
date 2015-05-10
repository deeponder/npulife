<?php
namespace Home\Controller;
use Think\Controller;
//require_once('./simple_html_dom.php');
require_once(APP_PATH.'Common/simple_html_dom.php');

class SpiderController extends Controller {
public function index(){
	if('npulife' == I("get.id") && "535ca7e3cde42"==I("get.token") ){
		echo '爬数据很慢，请您耐心等待!! 请等一个爬完以后，再爬第二个！！<br/><a href="Spider/newsNpu" target="_blank">爬通知公告</a><br/><a href="Spider/mediaNpu"  target="_blank">爬媒体工大等</a>';
	}else{
		echo '如果您看到了这个，说明您没有权限访问这个功能。返回刷新再试试？';
	}
	
}
public function newsNpu(){
	
	$homeUrl="http://news.nwpu.edu.cn/";
	$tzggHomeUrl="http://www.nwpu.edu.cn/";
	$tzggUrl="http://www.nwpu.edu.cn/index/tzgg.htm";
	set_time_limit(0);
	echo '现在开始爬新闻，请耐心等待... 如果出现错误，请重试，或咨询开发人员。。。 ';
	
//---------------传统框架下的爬虫------------
	//通知公告！！！！！
	$titleList = file_get_html($tzggUrl)->find('a[class=c48341]');
	echo '爬 通知公告... ';
	$Model = new \Think\Model();
	$Model->execute("truncate table nl_news_tzgg");
	foreach ($titleList as $value)
	{
		$primUrlString=$value->href;
		//echo $primUrlString;
		if(strpos($primUrlString,"news.nwpu.edu.cn")!==false)//是视窗网的新闻通告，略过
		continue;
		if(strpos($primUrlString,"info")==false){//是否是新闻
				continue;
		}else {//是新闻
				if(strpos($primUrlString,"nwpu.edu.cn")!==false)//是西北工业大学网站,包含前缀
				{						
					$imNewsUrl = $primUrlString;						
				}else {//加前缀
					$fuck = explode("../",$primUrlString);
					 $imNewsUrl = $tzggHomeUrl . $fuck[1];
				}
		}
		
		//测试多附件---
		//$imNewsUrl = "http://www.nwpu.edu.cn/info/1007/12136.htm";
		
		$newsContnentBody=file_get_html($imNewsUrl);
		//echo '通知公告';
		if(!empty($newsContnentBody)){
		//echo 'begin\n';
			$fuck1 = $newsContnentBody->find('td[class=titlestyle48308]');
			
			$fuck2 = $fuck1[0];
					$newsTitle=$fuck2->plaintext;
					$fuck3 = $newsContnentBody->find('span[class=timestyle48308]');
					$fuck4 = $fuck3[0];
					$fuck5 = $newsContnentBody->find('span[class=authorstyle48308]');
					$fuck6 = $fuck5[0];
					$newsOrigin=($fuck4->plaintext)."  ".$fuck6->plaintext;
					$fuck7 = $newsContnentBody->find('td[class=contentstyle48308]');
					
					 $newsContnent =  $fuck7[0];
					 //$newsImgP = $newsContnent->find('td[align=center]');
					 //var_dump($newsImgP);
					// foreach ($newsImgP as $value)
					// {
						// $value->style = 'text-indent: 0em;';
					// }
					// $newsImg = ($newsContnent->find('img'));
					
					// foreach ($newsImg as $value)
					// {
						// $fuck9 = explode("../../",$value->src);
						// $value->src = $homeUrl.$fuck9[1];
						// $value->width = "80%";
					// }	
				if(!empty($newsTitle)){
					$sql = " INSERT INTO  nl_news_tzgg  VALUES " . 
								" (  null,'". $newsTitle . "' , '". $newsOrigin . "' , '" . $newsContnent . "' , '". $imNewsUrl .  "' )";
					//echo "\n".$sql;
					
					$Model = new \Think\Model();
					$Model->execute($sql);	
				}
				
					//echo $sql;
		}
		//->find('form[name=form48308a]')			
					
			
	}	
	echo "爬取结束！";
}
public function mediaNpu(){
	$homeUrl="http://news.nwpu.edu.cn/";
	echo '爬 媒体工大... ';
		//---------------视窗网框架下的爬虫--------------
	$categories = file_get_html($homeUrl)->find('a[class=menu0_3_]');
		//媒体工大！！！！！
	$Model = new \Think\Model();
	$Model->execute("truncate table nl_news_mtgd");
	 
	$mtgdUrl = $homeUrl . $categories[7]->href;
	$mtgdList = file_get_html($mtgdUrl)->find('a[class=c48019]');
	foreach ($mtgdList as $value)
	{
		$imNewsUrl=$value->href;				  
		$newsTitle=$value->plaintext;
		$sql = " INSERT INTO  nl_news_mtgd  VALUES " . 
					" (  null,'" . $newsTitle . "' , '" . $imNewsUrl ."' )";
		$Model = new \Think\Model();
		$Model->execute($sql);
	}
	echo '爬 新闻关注... ';
		//新闻关注！！！！！
		//echo $sql;
	$Model = new \Think\Model();
	$Model->execute("truncate table nl_news_info");
	$tableColumn[0]=$homeUrl . $categories[1]->href;
	$tableColumn[1]=$homeUrl . $categories[2]->href;
	$tableColumn[2]=$homeUrl . $categories[3]->href;
	$tableColumn[3]=$homeUrl . $categories[4]->href;
	$tableColumn[4]=$homeUrl . $categories[8]->href;
	$tableColumn[5]=$homeUrl . $categories[16]->href;
	$tableColumn[6]=$homeUrl . $categories[17]->href;
	$tableColumn[7]=$homeUrl . $categories[19]->href;
	 
    for ($i=0; $i<8; $i++)
	{
		$newsListUrl = $tableColumn[$i];
		$index=$i;
	
		$newsList = file_get_html($newsListUrl)->find('a[class=c48019]');	 

		foreach ($newsList as $value)
		{
			$primUrlString=$value->href;
			$imNewsUrl;
				  //echo $primUrlString;
			if(strpos($primUrlString,"info")==false){//是否是新闻
				continue;
			}						
			 else {//是新闻
				if(strpos($primUrlString,"nwpu.edu.cn")!==false)//是西北工业大学网站,包含前缀
				{						
					$imNewsUrl = $primUrlString;						
				}	
				else {//加前缀
					$fuck10 =explode("../",$primUrlString);
					 $imNewsUrl =  $homeUrl . $fuck10[1];
				}
			}
				  
					
					//无图网页测试
					//$imNewsUrl = "http://news.nwpu.edu.cn/info/1002/25801.htm";
					//附件网页测试
					//$imNewsUrl = "http://news.nwpu.edu.cn/info/1009/24633.htm";
					
			$newsContnentBody=file_get_html($imNewsUrl);
			if(!empty($newsContnentBody)){
				$fuck11 = $newsContnentBody->find('span[class=c52366_title]');
				$newsTitle=$fuck11[0]->plaintext;
				$fuck12 = $newsContnentBody->find('span[class=c52366_date]');
				$newsOrigin=$fuck12[0]->plaintext;
				$fuck13 = $newsContnentBody->find('div[class=c52366_content]');
				$newsContnent = $fuck13[0];
				$newsImgP = $newsContnent->find('p[align=center]');
				foreach ($newsImgP as $value)
				{
					$value->style = 'text-indent: 0em;';
				}
				$newsImg = ($newsContnent->find('img'));
					
				foreach ($newsImg as $value)
				{
					$fuck14 = explode("../../",$value->src);
					$value->src = $homeUrl . $fuck14[1];
					$value->width = "80%";
				}
				$fuck15 = $newsContnent->find('img');
				$newsImgSrc = $fuck15[0]->src;
					//echo $newsImgSrc;
				$fuck16 = $newsContnentBody->find('ul[style=list-style-type:none]');
				$newsAttachments = $fuck16[0];
				if($newsAttachments!=null)
				{
					$newsAttachment = $newsAttachments->find('li a');
					foreach ($newsAttachment as $value)
					{				
						$fuck17 = explode("../../",$value->href);
						$value->href = $homeUrl . $fuck17[1];
					}
				}
					

				$sql = " INSERT INTO  nl_news_info  VALUES " . 
							" (  null," . $index . ", '" . $newsTitle . "' , '". $newsOrigin . "' , '" . $newsContnent . "' , '" . $newsAttachments . "' , '" . $imNewsUrl . 
							"' , '" . $newsImgSrc . "' )";
				//echo $sql;
				$Model = new \Think\Model();
				$Model->execute($sql);
			}	
		} 
	 sleep(8);
	}	
	echo "爬取结束！";
}

}