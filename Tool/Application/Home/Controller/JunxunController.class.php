<?php
namespace Home\Controller;
use Think\Controller;
class JunxunController extends Controller {
    
	//全部作品列表页面
	public function listJunxun() {
		
		$lMap['status'] = 1;
		$Junxun = M ( 'Junxun','nl_','DB_CONFIG_NPULIFE_DATA');
		$junxunList = $Junxun->where($lMap)->limit(0,20)->order('createdate desc')->select();
		
		$this->time = time();
		$this->junxunList = $junxunList;
		
		$this->display();
	}
	//////////////////////////////////////////////////////
	public function hotJunxun()
	{
		$lMap['status'] = 1;
		$Junxun = M ( 'Junxun','nl_','DB_CONFIG_NPULIFE_DATA');
		$junxunList = $Junxun->where($lMap)->limit(0,20)->order('view_count desc')->select();
		
		$this->time = time();
		$this->junxunList = $junxunList;
		
		$this->display();
	}
	
	
	public function playJunxun()
	{
		$junxunid = I('get.id');
		
		$Junxun = M ( 'Junxun','nl_','DB_CONFIG_NPULIFE_DATA');
		$vMap['id'] = $junxunid;
		$theJunxun = $Junxun->where($vMap)->find();
		
		$this->theJunxun = $theJunxun;
		
		$this->display();
	}
		
	
	private function test($content)
	{
		$userlist = M('Member','nl_','DB_CONFIG1')->select();
		$token = "535ca7e3cde42";//get_token();
		$msgtype = 'text';
		$content = $content;
		$uMap['uid'] = array('in','1');
		$testlist = M('Member','nl_','DB_CONFIG1')->where($uMap)->select();
				
		for($i=0;$i<count($testlist);$i++)
		{
			$touser = $testlist[$i]['openid'];
			$ret = customSend($touser, $token, $content, $msgtype);
		}
	}
}