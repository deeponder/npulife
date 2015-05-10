<?php
// 本类由系统自动生成，仅供测试用途
namespace Home\Controller;
use Think\Controller;
class SendToolController extends Controller {
    public function index(){
	
		$this->display();
    }	
	
	//向所有用户发图文
	public function sendAll()
	{
		$idList = explode(",",I('post.idList'));
		
		foreach($idList as $id)
		{
			$id = intval($id);
		}
		
		for($i=0;$i<count($idList);$i++)
		{
			$map['id'] = $idList[$i];
		
			$info = M ( 'custom_reply_news' )->where ( $map )->find ();			
			$param ['id'] = $info ['id'];
			$url = addons_url ( 'CustomReply://CustomReply/detail', $param );
		
			$articles [$i] = array (
					'Title' => $info ['title'],
					'Description' => $info ['intro'],
					'PicUrl' => get_cover_url ( $info ['cover'] ),
					'Url' => $url 
			);
		}
		$content = $articles;
		
		$userlist = M('Member')->select();
		$token = get_token();
		$msgtype = 'news';
	
		for($i=0;$i<count($userlist);$i++)
		{
			$touser = $userlist[$i]['openid'];
			$ret = customSend($touser, $token, $content, $msgtype);
			usleep(10);
		}
		echo "OK";
	}
	
	//向某一个用户组的成员发送图文
	public function sendByGroup()
	{
		$group_id = explode(",",I('post.group_id'));
		$idList = explode(",",I('post.idList'));
		
		foreach($idList as $id)
		{
			$id = intval($id);
		}
		
		for($i=0;$i<count($idList);$i++)
		{
			$map['id'] = $idList[$i];
		
			$info = M ( 'custom_reply_news' )->where ( $map )->find ();			
			$param ['id'] = $info ['id'];
			$url = addons_url ( 'CustomReply://CustomReply/detail', $param );
		
			$articles [$i] = array (
					'Title' => $info ['title'],
					'Description' => $info ['intro'],
					'PicUrl' => get_cover_url ( $info ['cover'] ),
					'Url' => $url 
			);
		}
		$content = $articles;		
		
		$mMap['groupid'] = intval($group_id);
		$userlist = M('UserGroup')->where($mMap)->select();
		
		$token = get_token();
		$msgtype = 'news';
		$Member = M('Member');
		
		foreach($userlist as $user)
		{
			$uMap['uid'] = $user['userid'];
					
			$u = $Member->where($uMap)->find();
			$touser = $u['openid'];
			
			usleep(50);
			$ret = customSend($touser, $token, $content, $msgtype);
		}
		echo "sendByGroup OK";
	}
	
	public function sendTheArticleByGroup()
	{
		$group_id = 3;
		$articles [0] = array (
				'Title' => "校车预订首发体验感恩回馈",
				'Description' => "感谢您参与瓜大生活圈校车预订功能首次体验,恭喜您成为幸运校车乘客,获得瓜大生活圈送出的16GU盘一枚!请与客服联系上报自己的ID号,核对领取奖品哟~",
				'PicUrl' => "http://mmbiz.qpic.cn/mmbiz/n7BDVLgj1PHfcib7Kzc7mAZO4y7juJ6mY37gK2wKkibpCKzub54jo7q0kuKqUWWic6ribb26tw31iaGgBweF8UPgCGQ/0",
				'Url' => "http://mp.weixin.qq.com/s?__biz=MjM5OTI4NjUxMw==&mid=200387646&idx=1&sn=aa081109190ed547f4b95a8ee36048d2#rd"
			);
		$content = $articles;		
		
		$mMap['groupid'] = $group_id;
		$userlist = M('UserGroup')->where($mMap)->select();
		
		$token = get_token();
		$msgtype = 'news';
		$Member = M('Member');
		
		foreach($userlist as $user)
		{
			$uMap['uid'] = $user['userid'];
					
			$u = $Member->where($uMap)->find();
			$touser = $u['openid'];
			
			usleep(50);
			$ret = customSend($touser, $token, $content, $msgtype);
		}
		echo "sendTheArticleByGroup OK";
	}
	
	
	/*发布优惠*/
	public function sendYouhui($openid,$sjid)
	{
		
		$uMap['openid'] = $openid; 
		$userinfo = M('Member')->where( $uMap )->find(); 
		$uid = $userinfo['uid'];
		
		$map['id'] = $sjid;
		$info = M ( 'custom_reply_news' )->where ( $map )->find ();			
		$param ['id'] = $info ['id'];
		$url = addons_url ( 'CustomReply://CustomReply/detail', $param );		
		
		$yMap['sjid'] = $sjid;
		$yMap['openid'] = $openid;
		$yMap['createdate'] = date('Y-m-d G:i:s');
		$yhid = M('Youhui')->add($yMap);
		
		$articles [0] = array (
					'Title' => "yhq:".$sjid."-".$uid."-".$yhid,
					'Description' => "",
					'PicUrl' => get_cover_url ( $info ['cover'] ),
					'Url' => "" 
			);
		$articles [1] = array (
					'Title' => $info ['title'],
					'Description' => $info ['intro'],
					'PicUrl' => get_cover_url ( $info ['cover'] ),
					'Url' => $url 
			);
		
		$touser = $openid;
		$token = get_token();
		$msgtype = "news";
		$content = $articles;
		$ret = customSend($touser, $token, $content, $msgtype);
		
		//还需要向商家发送一条优惠微信。
		
		
		$this->display();//echo "<h1></h1>";
	}	
	
	
	//群发音乐
	public function sendMusic()
	{		
		$content['title'] = explode(",",I('post.music_title'));
		$content['description'] = explode(",",I('post.music_description'));
		$content['musicurl'] = explode(",",I('post.music_url'));
		$content['hqmusicurl'] = explode(",",I('post.music_hqurl'));
		$content['thumb_media_id'] = explode(",",I('post.music_thumb_media_id'));
		
		$userlist = M('Member')->select();
		$token = get_token();
		$msgtype = 'music';
	
		/*
		for($i=0;$i<count($userlist);$i++)
		{
			$touser = $userlist[$i]['openid'];
			$ret = customSend($touser, $token, $content, $msgtype);
			usleep(10);
		}
		*/
		$touser = "o8TQCj8ch3DuyerWWZjI8zsONdEA";
		$ret = customSend($touser, $token, $content, $msgtype);
		
		echo "OK~Music".$ret;
	}	
	
	public function sendMedia()
	{
		$filename = I('post.filename');
		$filetype = I('post.filetype');
				
		$media_id = $this->getMediaId($filename,$filetype);
		
		//$uMap['uid'] = array('in','1,2480,2521,7,5241');
		//$userlist = M('Member')->where($uMap)->select();
		$userlist = M('Member')->select();
			
		$token = get_token();
		$msgtype = $filetype;
		
		for($i=0;$i<count($userlist);$i++)
		{
			$touser = $userlist[$i]['openid'];
			$content['media_id'] = $media_id;
			$ret = customSend($touser, $token, $content, $msgtype);
			usleep(10);
		}
		
		$content['media_id'] = $media_id;
		$touser = "o8TQCj8ch3DuyerWWZjI8zsONdEA";
		$ret = customSend($touser, $token, $content, $msgtype);
				
		echo "OK Media:".$ret;
	}
	
	//上传多媒体文件，获得media_id
	public function getMediaId($filename,$filetype)
	{
		
		$access_token = getAccessToken();
		
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$access_token."&type=".$filetype;
						
		echo $url;
		
		$filepath = "C:/xampp/htdocs/Public/media/".$filename;
		$data = array("media" => "@".$filepath);
		
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
		$result = curl_exec($ch);
		
		$result = json_decode($result, true);
		$media_id = $result["media_id"];
		
		echo $media_id;
		
		return $media_id;
	}
	
	public function sendVoiceNotify()
	{
		$Voice = M('Voice','nl_','DB_CONFIG_NPULIFE_DATA');
		$vMap['id'] = array('in','1');
		$playerList = $Voice->where($vMap)->select();
		for($i=0;$i<count($playerList);$i++)
		{
			$openid = $playerList[$i]['openid'];
			$nickname = $playerList[$i]['nickname'];
			echo $this->notifyTemplate($openid,$nickname);
		}		
	}
	/*
	private function notifyTemplate($openid,$nickname) {
		
		$GLOBALS ['user'] ['appid'] ='wx4c81bc4055e38cf5';
		$GLOBALS ['user'] ['secret']='4c469884a983d92a80e2967c9845bfef';
	
		header("Content-type: text/html; charset=utf-8");
		
		if (empty ( $GLOBALS ['user'] ['appid'] )) {
			return false;
		}
		
		$access_token = getAccessToken();
		
		$at ['access_token'] = $access_token;
		
		/*
		{{first.DATA}}


学生姓名：{{childName.DATA}}
考试名称：{{courseName.DATA}}
考试成绩：{{score.DATA}}
{{remark.DATA}}
		
		$data = "{
			\"touser\":\"".$openid."\",
			\"template_id\":\"QW6sGT0TlpxM3eQmbaej_sgM4LPBbM1In8TrqnGhYYU\",
			\"url\":\"\",
			\"topcolor\":\"#FF0000\",
			\"data\":{
				\"first\":{\"value\":\"亲耐的选手：恭喜你！你成为了首届微唱大赛海选候选选手，为保证大赛的顺利进行，请将个人信息（信息将严格保密）于2014年9月6日（周六）21：00前发送至微唱大赛组委会官方邮箱（2894095726@qq.com）,（个人信息包括：姓名、学校、学院、照片（生活照）、QQ、手机等。）\",\"color\":\"#173177\"},
				\"childName\":{\"value\":\"$nickname\",\"color\":\"#173177\"},
				\"courseName\":{\"value\":\"首届微唱大赛\",\"color\":\"#173177\"},
				\"score\":{\"value\":\"通过海选初选\",\"color\":\"#173177\"},
				\"remark\":{\"value\":\"按照大赛规则，将会有有30名选手晋级复赛，希望大家及时回复邮件，过期则视为自动放弃。请各位参赛选手加微唱大赛组委会官方QQ：2894095726。获取更多海选评选信息请及时关注“西北工大微生活”微信平台。\",\"color\":\"#173177\"}
			}
		}";
		
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
		
		return $ret;
	}*/
	
	
	public function sendwinner()
	{
		
		$articles [0] = array (
				'Title' => "您中奖啦！！！！",
				'Description' => "请在晚会结束后到前台领取您的精美小礼品哦~~",
				'PicUrl' => "",
				'Url' => ""
			);
		$content = $articles;		
		
		$follow=M('follows','nl_','DB_CONFIG1');
		$userlist = $follow->group('openid')->where('status=1')->select();
		
		$token = get_token();
		$msgtype = 'news';
		$Member = M('Member');
		
		foreach($userlist as $user)
		{		

			
			$touser = $user['openid'];
			
			// dump($user);
			usleep(50);
			$ret = customSend($touser, $token, $content, $msgtype);
			
		}
		
	}
}