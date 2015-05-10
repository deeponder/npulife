<?php
namespace Home\Controller;
use Think\Controller;
//require_once('simple_html_dom.php');
require_once(APP_PATH.'Common/simple_html_dom.php');

class SpiderController extends Controller {

	public function test1(){
		$id = $_GET['time'];



    	echo $id."往后的文章列表";
	}

	public function test2(){
		
		/*查询上一次同步的时间*/
		$time = "555";

		$ch = curl_init();
		$str ='http://wechat.npulife.com/tool/home/spider/test1?time='.$time;
		curl_setopt($ch, CURLOPT_URL, $str);
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$output = curl_exec($ch);
		echo $output;

		/*解析$output*/

		/*下载图片*/
		$url = "http://img1.imgtn.bdimg.com/it/u=4201652800,2609158885&fm=21&gp=0.jpg";

		 GrabImage($url,$filename="");

		/*保存到数据库*/

		/*保存当前时间到数据库*/
	}


function GrabImage($url,$filename="") {
   if($url==""):return false;endif;

   if($filename=="") {
     $ext=strrchr($url,".");
     if($ext!=".gif" && $ext!=".jpg"):return false;endif;
     $filename=date("dMYHis").$ext;
   }

   ob_start();
   readfile($url);
   $img = ob_get_contents();
   ob_end_clean();
   $size = strlen($img);

   $fp2=@fopen($filename, "a");
   fwrite($fp2,$img);
   fclose($fp2);

   return $filename;
}

	



public function index(){
	if('npulife' == I("get.id") && "535ca7e3cde42"==I("get.token") ){
		echo '';
		$this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>爬数据很慢，请您耐心等待!! 当页面显示爬取结束时代表成功了！<br/>期间请不要关闭或刷新页面！！</p><br/><a href="http://content.npulife.com/Tool/index.php/Home/Spider/newsNpu" target="_blank">爬通知公告</a><br/><a href="http://content.npulife.com/Tool/index.php/Home/Spider/mediaNpu"  target="_blank">爬媒体工大</a><br/>
		<a href="http://content.npulife.com/Tool/index.php/Home/Spider/testNewsinfo" target="_blank">爬新闻关注</a></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
	}else{
		echo '如果您看到了这个，说明您没有权限访问这个功能。返回刷新再试试？';
	}
	
}

public function autopa(){
			//自动更新，failed~~
  		ignore_user_abort(); //即使Client断开(如关掉浏览器)，PHP脚本也可以继续执行.
		set_time_limit(0); // 执行时间为无限制，php默认执行时间是30秒，可以让程序无限制的执行下去
		$interval=7200; 
		do{

		$this->newsNpu();
		$this->mediaNpu();
		$this->testNewsinfo();
    	sleep($interval); // 按设置的时间等待2小时循环执行
   		// $sql="update blog set time=now()";
    	// dump("dkf");

		}while(true);
	}

public function newsNpu(){
	
	$homeUrl="http://news.nwpu.edu.cn/";
	$tzggHomeUrl="http://www.nwpu.edu.cn/";
	$tzggUrl="http://www.nwpu.edu.cn/index/tzgg.htm";
	set_time_limit(0);
	$this->show('现在开始爬新闻，请耐心等待... 如果出现错误，请重试，或咨询开发人员。。。 '); 
	
//---------------传统框架下的爬虫------------
	//通知公告！！！！！
	$titleList = file_get_html($tzggUrl)->find('a[class=c48341]');
	//调整爬取的顺序 按链接的反顺序
	$titleList = array_reverse($titleList);
	//dump($titleList);
	echo '爬 通知公告... ';
	/*$Model = new \Think\Model();
	$Model->execute("truncate table nl_news_tzgg");*/
	$Model = M('news_tzgg','nl_','DB_CONFIG1');
	foreach ($titleList as $value)
	{
		$primUrlString=$value->href;
		//echo $primUrlString.'<br/>';
		//echo '<br/>';
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
				//echo $imNewsUrl.'<br/>';
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
					
					 $newsContent =  $fuck7[0]->plaintext;
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
					/*$sql = " INSERT INTO  nl_news_tzgg  VALUES " . 
								" (  null,'". $newsTitle . "' , '". $newsOrigin . "' , '" . $newsContent . "' , '". $imNewsUrl .  "' )";
					//echo "\n".$sql;
					
					$Model = new \Think\Model();
					$Model->execute($sql);*/
					$titleArr = $Model->getField('news_title',true);
					if(!in_array($newsTitle,$titleArr)){
					$add['news_title'] = $newsTitle;
					$add['news_origin'] = $newsOrigin;
					$add['news_content'] = $newsContent;
					$add['news_source'] = $imNewsUrl;
					$Model->add($add);
					}
				}
				
					//echo $sql;
		}
		//->find('form[name=form48308a]')			
					
			
	}	
	$this->show( "<br/><H1>爬取结束！</H1>");
} 

public function mediaNpu()
{
	$homeUrl="http://news.nwpu.edu.cn/";
	echo '爬 媒体工大... ';
		//---------------视窗网框架下的爬虫--------------
	$categories = file_get_html($homeUrl)->find('a[class=menu0_3_]');
		//媒体工大！！！！！
	//$Model = new \Think\Model();
	//$Model->execute("truncate table nl_news_mtgd");
	$Model= M ( 'news_mtgd','nl_','DB_CONFIG1');
	 
	$mtgdUrl = $homeUrl . $categories[7]->href;
	$mtgdList = file_get_html($mtgdUrl)->find('a[class=c48019]');
	$mtgdList = array_reverse($mtgdList);
	foreach ($mtgdList as $value)
	{
		$imNewsUrl=$value->href;				  
		$newsTitle=$value->plaintext;
		//$sql = " INSERT INTO  nl_news_mtgd  VALUES " . 
			//		" (  null,'" . $newsTitle . "' , '" . $imNewsUrl ."' )";
		//$Model = new \Think\Model();
		//$Model->execute($sql);
		//判空
		if(!empty($newsTitle))
		{
			$titleArr = $Model->getField('news_title',true);
			//判重
			if(!in_array($newsTitle,$titleArr))
			{
				$news['news_title'] = $newsTitle;
				$news['news_source'] = $imNewsUrl;
				$Model->add($news);
			}
		}
	}
	
	$this->show( "<br/><H1>爬取结束！</H1>");	
	
}


public function testNewsinfo()
{
    $Model= M ( 'news_info','nl_','DB_CONFIG1');  
	$homeUrl="http://news.nwpu.edu.cn/";
	echo '爬 新闻关注... ';
		//新闻关注！！！！！
		//echo $sql;
	//$Model = new \Think\Model();
	//$Model->execute("truncate table nl_news_info");
	$categories = file_get_html($homeUrl)->find('a[class=menu0_3_]');
	$tableColumn[0]=$homeUrl . $categories[1]->href;
	$tableColumn[1]=$homeUrl . $categories[2]->href;
	$tableColumn[2]=$homeUrl . $categories[3]->href;
	$tableColumn[3]=$homeUrl . $categories[4]->href;
	$tableColumn[4]=$homeUrl . $categories[8]->href;
	$tableColumn[5]=$homeUrl . $categories[16]->href;
	$tableColumn[6]=$homeUrl . $categories[17]->href;
	$tableColumn[7]=$homeUrl . $categories[19]->href;
    for ($i=7; $i>=0; $i--)
	{
		$newsListUrl = $tableColumn[$i];
		$index=$i;
	
		$newsList = file_get_html($newsListUrl)->find('a[class=c48019]');	 
        $mtgdList = array_reverse($newsList);
		foreach ($mtgdList as $value)
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
			//secho $newsContnentBody;
			if(!empty($newsContnentBody)){
				$fuck11 = $newsContnentBody->find('span[class=c52366_title]');
				$newsTitle=$fuck11[0]->plaintext;
				$fuck12 = $newsContnentBody->find('span[class=c52366_date]');
				$newsOrigin=$fuck12[0]->plaintext;
				$fuck13 = $newsContnentBody->find('div[class=c52366_content]');
				$newsContnent = $fuck13[0];
				$newsImg = ($newsContnent->find('img'));
				 
				foreach ($newsImgP as $value)
				{
					$value->style = 'text-indent: 0em;';
				}
				
					
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
					//echo $newsAttachment;
					foreach ($newsAttachment as $value)
					{				
						$fuck17 = explode("../../",$value->href);
						$value->href = $homeUrl . $fuck17[1];
					}
				}
					
               // $content = $newsContnent->plaintext;
				//$sql = " INSERT INTO  nl_news_info  VALUES " . 
						" (  null," . $index . ", '" . $newsTitle . "' , '". $newsOrigin . "' , '" . $content. "' , '" . "null" . "' , '" . $imNewsUrl . 
							"' , '" . $newsImgSrc . "' )";
				//echo $sql;
				//$Model = new \Think\Model();
				//$Model->execute($sql);
		 $titleArr = $Model->getField('news_title',true);
		 //判重
	      if(!in_array($newsTitle,$titleArr)){
		   
		      $content = $newsContnent->plaintext;
				$add['new_column'] = $index;
				$add['news_title'] = $newsTitle;
				$add['news_origin'] = $newsOrigin;
				$add['news_content'] = $content; 
				//$add['news_attachment'] =$newsAttachments; 
				$add['news_source'] = $imNewsUrl;
				$add['news_first_img'] = $newsImgSrc;
				$Model->add($add);
			}	
		} 
		 }
	 sleep(8);
	}
	$this->show( "<br/><H1>爬取结束！</H1>");
	
	
	
}

	
	
	
}
