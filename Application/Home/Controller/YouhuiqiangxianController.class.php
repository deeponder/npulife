<?php
namespace Home\Controller;
use Think\Controller;

//优惠抢先，要能点击弹出优惠券。
class YouhuiqiangxianController extends Controller {
    
	public function getIn() {
		$Youhuiqiangxian = M ( 'Youhuiqiangxian','nl_','DB_CONFIG_NPULIFE_DATA');
		$map['cate_id'] = 15;
		$CustomNews = M('CustomReplyNews')->where($map)->select();
		for($i=0;$i<count($CustomNews);$i++)
		{
			$dMap['title'] = $CustomNews[$i]['title'];
			$dMap['content'] = $CustomNews[$i]['content'];
			
			$dMap['view_count'] = $CustomNews[$i]['view_count'];
			$Youhuiqiangxian->add($dMap);
		}
		echo "OK";
	}
	
	//全部作品列表页面
	public function listYouhuiqiangxian() {
		
		$Youhuiqiangxian = M ( 'Youhuiqiangxian','nl_','DB_CONFIG_NPULIFE_DATA');
		$YouhuiqiangxianList = $Youhuiqiangxian->select();
		
		$this->time = time();
		$this->YouhuiqiangxianList = $YouhuiqiangxianList;
		
		$this->display();
	}
	public function getMoreYouhuiqiangxian() {
		$num = 20;
		$page = intval(I('get.page'));
		$time = intval(I('get.time'));
		$time = date('Y-m-d G:i:s',$time);
		$gMap['status'] = 1;
		$gMap['createdate'] = array('lt',$time);
		$Youhuiqiangxian = M ( 'Youhuiqiangxian','nl_','DB_CONFIG_NPULIFE_DATA');
		$voiceList = $Youhuiqiangxian->where($gMap)->limit($page*$num,$num)->order('createdate desc')->select();
		
		$result['status'] = 1;
		$result['data'] = $voiceList;
		$this->ajaxReturn($result,"JSON");
	}
	//////////////////////////////////////////////////////
	public function hotYouhuiqiangxian()
	{
		//$lMap['status'] = 1;
		$Youhuiqiangxian = M ( 'Youhuiqiangxian','nl_','DB_CONFIG_NPULIFE_DATA');
		$YouhuiqiangxianList = $Youhuiqiangxian->order('view_count desc')->select();
		
		$this->time = time();
		$this->YouhuiqiangxianList = $YouhuiqiangxianList;
		
		$this->display();
	}
	public function getMoreHotYouhuiqiangxian()
	{
		$num = 20;
		$page = intval(I('get.page'));
		$time = intval(I('get.time'));
		$time = date('Y-m-d G:i:s',$time);
		
		$Youhuiqiangxian = M ( 'Youhuiqiangxian','nl_','DB_CONFIG_NPULIFE_DATA');
		$gMap['status'] = 1;
		$gMap['createdate'] = array('lt',$time);
		$voiceList = $Youhuiqiangxian->where($gMap)->limit($page*$num,$num)->order('view_count desc')->select();
		
		$result['status'] = 1;
		$result['data'] = $voiceList;
		$this->ajaxReturn($result,"JSON");
	}
	
	
	public function playYouhuiqiangxian()
	{
		$youhuiid = intval(I('get.id'));
		
		$Youhuiqiangxian = M ( 'Youhuiqiangxian','nl_','DB_CONFIG_NPULIFE_DATA');
		$vMap['id'] = $youhuiid;
		$theYouhuiqiangxian = $Youhuiqiangxian->where($vMap)->find();
		
		$theYouhuiqiangxian['view_count']++;
		$Youhuiqiangxian->save($theYouhuiqiangxian);
		
		$this->theYouhuiqiangxian = $theYouhuiqiangxian;
		
		$openid = get_openid();
		if($openid != -1)
		{
			$this->sendYouhuiquan($youhuiid,$openid);
		}		
		
		$this->display();
	}
	public function detailYouhuiqiangxian()
	{
		$youhuiid = intval(I('get.id'));
		
		$Youhuiqiangxian = M ( 'Youhuiqiangxian','nl_','DB_CONFIG_NPULIFE_DATA');
		$vMap['id'] = $youhuiid;
		$theYouhuiqiangxian = $Youhuiqiangxian->where($vMap)->find();
		
		$theYouhuiqiangxian['view_count']++;
		$Youhuiqiangxian->save($theYouhuiqiangxian);
		
		$this->theYouhuiqiangxian = $theYouhuiqiangxian;
				
		$this->display();
	}
	
	public function test()
	{
		$openid = get_openid();
		if($openid != -1)
		{
			echo $openid."\n";
			echo $this->sendYouhuiquan(1,$openid);
		}		
	}
	
	private function sendYouhuiquan($youhuiid,$openid)
	{		
		$GLOBALS ['user'] ['appid'] ='wx4c81bc4055e38cf5';
		$GLOBALS ['user'] ['secret']='6936657155c874611fc77b3641164ae0';
	
		header("Content-type: text/html; charset=utf-8");
		
		if (empty ( $GLOBALS ['user'] ['appid'] )) {
			return false;
		}
		
		$access_token = getAccessToken();
		
		$at['access_token'] = $access_token;
		
		$Member = M('Member');
		$uMap['openid'] = $openid;
		$user = $Member->where($uMap)->find();
		$userid = $user['uid'];
				
		$Youhuiqiangxian = M ( 'Youhuiqiangxian','nl_','DB_CONFIG_NPULIFE_DATA');
		$yMap['id'] = $youhuiid;
		$youhui = $Youhuiqiangxian->where($yMap)->find();
		
		$Youhuiquan = M( 'Youhuiquan','nl_','DB_CONFIG_NPULIFE_DATA');
		$qMap['youhuiid'] = $youhuiid;
		$qMap['userid'] = $userid;
		$qMap['createdate'] = date('Y-m-d G:i:s');
		$qMap['status'] = 0; //未使用
		$qid = $Youhuiquan->add($qMap);
		
		/*
		{{first.DATA}}
		礼券类别：{{keyword1.DATA}}
		礼券券码：{{keyword2.DATA}}
		生效日期：{{keyword3.DATA}}
		失效日期：{{keyword4.DATA}}
		{{remark.DATA}}   
		*/
		$first = "恭喜您成功领取".$youhui['name']."优惠券~";
		$keyword1 = $youhui['type'];
		$keyword2 = $youhui['id']."-".$userid."-".$qid;
		$keyword3 = date('Y-m-d G:i:s');
		$keyword4 = "使用之后失效";
		$remark = "点击可以查看优惠详细信息。感谢您对瓜大生活圈的信任与支持~祝您天天快乐~";
		
		$data = "{
			\"touser\":\"".$openid."\",
			\"template_id\":\"JnmYHdZ_Zw9JBqel5IrTbOm8v6mKFw3GLH9elQG33lI\",
			\"url\":\"http://wechat.npulife.com/index.php/Home/Youhuiqiangxian/detailYouhuiqiangxian?id=".$youhui['id']."\",
			\"topcolor\":\"#FF0000\",
			\"data\":{
				\"first\":{\"value\":\"".$first."\",\"color\":\"#173177\"},
				\"keyword1\":{\"value\":\"".$keyword1."\",\"color\":\"#173177\"},
				\"keyword2\":{\"value\":\"".$keyword2."\",\"color\":\"#173177\"},
				\"keyword3\":{\"value\":\"".$keyword3."\",\"color\":\"#173177\"},
				\"keyword4\":{\"value\":\"".$keyword4."\",\"color\":\"#173177\"},
				\"remark\":{\"value\":\"".$remark."\",\"color\":\"#173177\"}
			}
		}";
		
		return $data;
		
		$url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?'.http_build_query($at);
			
		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		$ret = curl_exec($ch); 
		
		//如果是发送失败，换用图文消息的形式发送。
		$ret = json_decode($ret);
		if($ret['errcode']==0)
		{
			echo $ret['errcode'];//customSend();
		}
		
		return $ret;
	}
	
	private function testOne($content)
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