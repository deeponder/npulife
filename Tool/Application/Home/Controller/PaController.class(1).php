<?php
namespace Home\Controller;
use Think\Controller;
class PaController extends Controller {

	public function countClick()
	{
		/*
		for()
		{
			
		}
		*/
		$this->test();
	}


	public function autopa(){
			//自动更新，failed~~
  		ignore_user_abort(); //即使Client断开(如关掉浏览器)，PHP脚本也可以继续执行.
		set_time_limit(0); // 执行时间为无限制，php默认执行时间是30秒，可以让程序无限制的执行下去
		$interval=7200; 
		do{

		$this->index();
    	sleep($interval); // 按设置的时间等待2小时循环执行
   		// $sql="update blog set time=now()";
    	// dump("dkf");

		}while(true);
	}

	public function index(){
		
		//$this->test("开始爬微信文章");
		
		header("Content-type: text/html; charset=utf-8");
		$openidArr = M('Dingyuehao','nl_','DB_CONFIG_NPULIFE_DATA')->getField('openid',true);//dingyuehao这个数据表中存放的是每个订阅号的openid。openid我附在excel中了。
		$number = count($openidArr);
		
		$num = 0;
		
		for($i = 0;$i<$number;$i++)
		{
			$openid =$openidArr[$i];
			$page = 1;
			$time_tamp = time();
			$gap_url = $this->create_url($openid,$page,$time_tamp);
			$content = file_get_contents($gap_url);
			$json_content = $this->get_json($content);
			$json_info = json_decode($json_content,true);
			$total_page = $json_info['totalPages'];
			
			for($pa = 1;$pa <= $total_page;$pa++){
				$gap_url = $this->create_url($openid,$pa,$time_tamp);
				
				$content = file_get_contents($gap_url);
				$json_content = $this->get_json($content);
				$json_info = json_decode($json_content,true);
				$item_list = $json_info['items'];
				$item_count = count($item_list);
				
				if($item_list > 0){
					
					for($j = 0;$j < $item_count;$j++){
						
						$xml_obj = simplexml_load_string($this->order_xml_str($item_list[$j]),'SimpleXMLElement', LIBXML_NOCDATA);
												
						if($this->is_not_get($xml_obj))
						{
							$ret = $this->put_into_database($xml_obj,$openid);
							
							if($ret)
							{
								$num++;
							}
						}
					}
				}
				unset($content);
				unset($json_content);
				unset($json_info);
				unset($item_list);
			}
		}
		
		// $this->test("我强大的微信抓取工具获取了".$num."条数据");
	}
	
	private function put_into_database($obj,$openid){
	
		$PaArticle = M('PaArticle','nl_','DB_CONFIG_NPULIFE_DATA');
		
		$display = $obj->item->display;
		
		$url = htmlspecialchars($display->url);
		
		$data['picurl'] = htmlspecialchars($display->imglink);
		$data['pubdate'] = (int)($display->lastModified);
		$data['wxname'] = htmlspecialchars($display->sourcename);
		$data['title'] = htmlspecialchars($display->title);
		$data['url'] = htmlspecialchars($display->url);
		$data['openid'] = $openid;
		$data['description'] = htmlspecialchars($display->content168);
		
		$retData = $this->getContentByUrl($url);//htmlspecialchars($display->content168);//
		if(!empty($retData))
		{
			//根据内容查重
			//$data['content'] = "";$retData['content'];
			$data['author'] = $retData['author'];
			$id = $PaArticle->add($data);
						
			return true;
		}
		return false;
	}
	
	private function test($content)
	{
		$userlist = M('Member','nl_','DB_CONFIG1')->select();
		$token = "535ca7e3cde42";//get_token();
		$msgtype = 'text';
		$content = $content;
		$uMap['uid'] = 14615;
		$testlist = M('Member','nl_','DB_CONFIG1')->where($uMap)->select();
				
		// for($i=0;$i<count($testlist);$i++)
		{
			$touser = $testlist[$i]['openid'];
			$ret = customSend($touser, $token, $content, $msgtype);
		}
	}
	
	private function order_xml_str($str){
		$str = stripslashes($str);
		$find = 'encoding="gbk"';
		$replace = 'encoding="utf-8"';
		$str = str_replace($find,$replace,$str);
		return $str;
	}
	private function create_url($openid,$page,$time){
		$base_url = "http://weixin.sogou.com/gzhjs?cb=sogou.weixin.gzhcb&openid=%s&page=%d&t=%s";
		return sprintf($base_url,$openid,$page,$time);
	}
	private function get_json($content){
		$start_pos = strpos($content,'{',0);
		$end_pos = strrpos($content,'}');
		if($start_pos === false || $end_pos === false){
			return false;
		}
		return substr($content,$start_pos,($end_pos - $start_pos + 1));
	}
	private function is_not_get($obj)
	{
		$model = M('PaArticle','nl_','DB_CONFIG_NPULIFE_DATA');
		$display = $obj->item->display;
		$sour = htmlspecialchars($display->sourcename);
		$map['wxname'] = $sour;
		$sourArr = $model->where($map)->select();
		$sour_num = count($sourArr);
		if(!$sour_num)
		{
			return true;
		}
		else
		{
			$get_url = $model->where($map)->getField('title',true);
			$this_url = htmlspecialchars($display->title);
			if(in_array($this_url, $get_url))
			{
				return false;
			}
			else
			{
				return true;
			}
		}
	}
	
	///////////////////////////////////////////////////////////////////////////
	//根据链接抓取全部内容
	private function getContentByUrl($url)
	{
		
		if($url!=null)
		{
			header("Content-Type: text/html; charset=utf-8");
			$data = array();
			$fcontents = file_get_contents($url);
			if($fcontents==null){
				
				return false;//$this->error("网址输入有误");
			}
			else{
				preg_match('|<title>(.*?)<\/title>|i',$fcontents,$m);
				$data['title']=$m[1];
				//$a = M('pachong1')->where($data)->select();
				//$num = count($a);
				
				//$html=new DOMDocument();
				//$html->loadHTMLFile($url);
				//$jscontent=$html->getElementById('js_content');  
				//$title->item(0)->nodeValue;  

				$con = '<div class="rich_media_content" id="js_content">(.+?)</div>';
				preg_match($con,$fcontents,$content);
				$data['content']=$content[1];//$jscontent;//htmlspecialchars($fcontents->js_content);//
				$author = '%id="post-user.*?>(.*?)</a>%si';
				preg_match($author, $fcontents, $autho);
				$data['author']=$autho[1];
				$date1 = '%id="post-date.*?>(.*?)</em>%si';
				preg_match($date1, $fcontents, $date);
				$data['pubdate']=$date[1];
				$pattern="/<img.*?src=[\'|\"](.*?(?:))[\'|\"].*?[\/]?>/";
				preg_match($pattern, $fcontents,$img1);
				$data['picurl'] = $img1[1];
				
				//有能力的话做一次内容查重
				
				return $data;//$data;//带有作者author、发布时间pubdate、图片地址picurl、内容content
			}
		}
		return false;//else $this->error("请输入网址");
	}
}