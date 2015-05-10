<?php
namespace Home\Controller;
use Think\Controller;
class VideoController extends Controller {

	//全部作品列表页面
	public function listVideo() {
		
		$lMap['status'] = 1;
		$Video = M ( 'Video','nl_','DB_CONFIG_NPULIFE_DATA');
		$voiceList = $Video->where($lMap)->limit(0,20)->order('createdate desc')->select();
		
		$this->time = time();
		$this->voiceList = $voiceList;
		
		$this->display();
	}
	public function getMoreVideo() {
		$num = 20;
		$page = intval(I('get.page'));
		$time = intval(I('get.time'));
		$time = date('Y-m-d G:i:s',$time);
		$gMap['status'] = 1;
		$gMap['createdate'] = array('lt',$time);
		$Video = M ( 'Video','nl_','DB_CONFIG_NPULIFE_DATA');
		$voiceList = $Video->where($gMap)->limit($page*$num,$num)->order('createdate desc')->select();
		
		$result['status'] = 1;
		$result['data'] = $voiceList;
		$this->ajaxReturn($result,"JSON");
	}
	//////////////////////////////////////////////////////
	public function hotVideo()
	{
		$lMap['status'] = 1;
		$Video = M ( 'Video','nl_','DB_CONFIG_NPULIFE_DATA');
		$voiceList = $Video->where($lMap)->limit(0,20)->order('view_count desc')->select();
		
		$this->time = time();
		$this->voiceList = $voiceList;
		
		$this->display();
	}

	
	
	public function getMoreHotVideo()
	{
		$num = 20;
		$page = intval(I('get.page'));
		$time = intval(I('get.time'));
		$time = date('Y-m-d G:i:s',$time);
		
		$Video = M ( 'Video','nl_','DB_CONFIG_NPULIFE_DATA');
		$gMap['status'] = 1;
		$gMap['createdate'] = array('lt',$time);
		$voiceList = $Video->where($gMap)->limit($page*$num,$num)->order('view_count desc')->select();
		
		$result['status'] = 1;
		$result['data'] = $voiceList;
		$this->ajaxReturn($result,"JSON");
	}
	
	
	public function playVideo()
	{
		$voiceid =I('get.voiceid');
		$Video = M ( 'Video','nl_','DB_CONFIG_NPULIFE_DATA');
		$vMap['id'] = $voiceid;
		$theVoice = $Video->where($vMap)->find();

		$theVoice['view_count']+=rand(1,3);
		$Video->save($theVoice);
		
		$this->theVoice = $theVoice;
		
		// dump($theVideo);
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