<?php
namespace Home\Controller;
use Think\Controller;

//通用的投票页面。需要在微信界面内投票，这里只是展示。
class VoteController extends Controller {
    
	//全部作品列表页面
	public function listVote() {
		
		$lMap['status'] = 1;
		$Vote = M ( 'Vote','nl_','DB_CONFIG_NPULIFE_DATA');
		$voiceList = $Vote->where($lMap)->limit(0,20)->order('createdate desc')->select();
		
		$this->time = time();
		$this->voiceList = $voiceList;
		
		$this->display();
	}
	public function getMoreVote() {
		$num = 20;
		$page = intval(I('get.page'));
		$time = intval(I('get.time'));
		$time = date('Y-m-d G:i:s',$time);
		$gMap['status'] = 1;
		$gMap['createdate'] = array('lt',$time);
		$Vote = M ( 'Vote','nl_','DB_CONFIG_NPULIFE_DATA');
		$voiceList = $Vote->where($gMap)->limit($page*$num,$num)->order('createdate desc')->select();
		
		$result['status'] = 1;
		$result['data'] = $voiceList;
		$this->ajaxReturn($result,"JSON");
	}
	//////////////////////////////////////////////////////
	public function hotVote()
	{
		$lMap['status'] = 1;
		$Vote = M ( 'Vote','nl_','DB_CONFIG_NPULIFE_DATA');
		$voiceList = $Vote->where($lMap)->limit(0,20)->order('view_count desc')->select();
		
		$this->time = time();
		$this->voiceList = $voiceList;
		
		$this->display();
	}
	public function getMoreHotVote()
	{
		$num = 20;
		$page = intval(I('get.page'));
		$time = intval(I('get.time'));
		$time = date('Y-m-d G:i:s',$time);
		
		$Vote = M ( 'Vote','nl_','DB_CONFIG_NPULIFE_DATA');
		$gMap['status'] = 1;
		$gMap['createdate'] = array('lt',$time);
		$voiceList = $Vote->where($gMap)->limit($page*$num,$num)->order('view_count desc')->select();
		
		$result['status'] = 1;
		$result['data'] = $voiceList;
		$this->ajaxReturn($result,"JSON");
	}
		
	public function playVote()
	{
		$voiceid = I('get.voiceid');
		
		$Vote = M ( 'Vote','nl_','DB_CONFIG_NPULIFE_DATA');
		$vMap['id'] = $voiceid;
		$theVote = $Vote->where($vMap)->find();
		
		$theVote['view_count']++;
		$Vote->save($theVote);
		
		$this->theVote = $theVote;
		
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