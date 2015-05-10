<?php
namespace Home\Controller;
use Think\Controller;
class PaController extends Controller {

	public function index(){
	
		// $this->display();
		dump(time());
		

	}
	//爬公众号
	private function papublice(){
		header("Content-Type: text/html; charset=utf-8");
		$openidList = M('Dingyuehao','nl_','DB_CONFIG_NPULIFE_DATA')->field('openid,cate_id')->select();
		$page = 1;
		$time_stamp = time();
		
		//	遍历所有订阅号，获取首页文章存入数据库    //
		$pubic_num = count($openidList);
		for($i = 0;$i<$pubic_num;$i++){
			//$openid = $openidList[$i];
			$openid = $openidList[$i]['openid'];
			$data['openid'] = $openid;
			$data['cate_id'] = $openidList[$i]['cate_id'];
			
			//获取文章信息
			$url = $this->create_url($openid,$page,$time_stamp);
			$content = file_get_contents($url);
			if($content == false) continue;

			//对获取的内容进行【JSON】格式化，并转化成数组 //
			$json_content = $this->get_json($content);
			$json_arr = json_decode($json_content,true);
			if($json_arr == NULL) continue;
			
			//将XML格式的文章信息解析成【数组】格式,并写入数据库
			$num = count($json_arr['items']);
			for($j = 0;$j< $num;$j++){
			
				$xml_string = $json_arr['items'][$j];
				$xml_string = str_replace('gbk','UTF-8',$xml_string);
				$xml = simplexml_load_string($xml_string,NULL,LIBXML_NOCDATA);
				$json = json_encode($xml);
				$artical_arr = json_decode($json,true);
			
				//dump($artical_arr);//['item']['display']['headimage']
				$this->put_into_db($data, $artical_arr);
			}
		}

	}


	private function create_url($openid,$page,$time){
		$base_url = "http://weixin.sogou.com/gzhjs?cb=sogou.weixin.gzhcb&openid=%s&page=%d&t=%s";
		return sprintf($base_url,$openid,$page,$time);
	}
	private function get_json($content){
		preg_match('/{.*}/', $content,$json_string);
		return $json_string[0];
		
	}
	private function put_into_db($data,$artical_arr){
		$PaAtical = M('PaArticle','nl_','DB_CONFIG_NPULIFE_DATA');
		if($artical_arr == NULL || $data == NULL) return false;
		
		$item = $artical_arr['item']['display'];
		$data['docid'] = $item['docid'];
		/*查重*/
		$artical = $PaAtical->where('docid = "%s"',$data['docid'])->select();
		if($artical != NULL){

			// dump('此文章已存在！\n');
			return false;
		}
		$data['wxname'] = $item['sourcename'];
		$data['title'] = $item['title']; 
		$data['description'] = $item['content'];
		$data['picurl'] = $item['imglink'];
		$data['pudate'] = (int)$item['lastModified'];
		$data['url'] = $item['url'];
		
		//dump($data);
		$PaAtical->add($data);
		
	}

}