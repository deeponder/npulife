<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    
	public function index(){
        
		$a = M('Member','nl_','DB_CONFIG1')->select();
		
		echo count($b);
    }
		
	//把CustomReplyNews最新的内容更新到wechat.npulife.com服务器；
	public function updateContent($id)
	{
		$map['id'] = $id;
		$Content = M('CustomReplyNews')->where($map)->field('keyword,keyword_type,title,intro,cate_id,cover,content,cTime,sort,view_count,token')->find();
		//把这个内容写入远程数据库
		$jsonContent = json_encode($Content);
		echo $jsonContent;
	}	
}