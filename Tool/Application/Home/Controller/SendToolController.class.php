<?php
// 本类由系统自动生成，仅供测试用途
namespace Home\Controller;
use Think\Controller;
class SendToolController extends Controller {
    public function index(){
	
		$this->display();
    }	
	
	//专为每日导览设计
	public function sendEveryday()
	{
		$newsList = M ( 'custom_reply_news','nl_','DB_CONFIG1')->order('cTime desc')->limit(40)->select ();			
		$this->assign('newsList',$newsList);		
		$this->display();
	}
	public function preview($id)
	{
		$map['id'] = $id;
		$news = M('custom_reply_news','nl_','DB_CONFIG1')->where($map)->find();
		$this->assign('news',$news);
		
		$this->display();
	}
	
	//向所有用户发图文
	public function sendAll()
	{
		
		//$openid = get_openid();
			
		$idList = explode(",",I('post.keyword'));
		
		//需要检测是不是为空.
		if(0==count($idList))
		{
			echo "NO ARITICLE";
			exit;
		}
		
		foreach($idList as $id)
		{
			$id = intval($id);
		}
		
		for($i=0;$i<count($idList);$i++)
		{
			$map['id'] = $idList[$i];
		
			$info = M ( 'custom_reply_news','nl_','DB_CONFIG1')->where ( $map )->find ();			
			$param ['id'] = $info ['id'];
			$url = "http://wechat.npulife.com/index.php/Home/NewSchoolHome/detail/id/".$param ['id'];//addons_url ( 'CustomReply://CustomReply/detail', $param );
		
			$articles [$i] = array (
					'Title' => $info ['title'],
					'Description' => $info ['intro'],
					'PicUrl' => get_cover_url ( $info ['cover'] ),
					'Url' => $url 
			);
		}
		$content = $articles;
		
		$userlist = M('Member','nl_','DB_CONFIG1')->select();
		$token = "535ca7e3cde42";//get_token();
		$msgtype = 'news';

		//$test = 1;
		
		//$testuser = $userlist[$i]['openid'];
		//$ret = customSend($testuser, $token, $content, $msgtype);
		$uMap['uid'] = array('in','1,2722');
		$testlist = M('Member','nl_','DB_CONFIG1')->where($uMap)->select();
		
		for($i=0;$i<count($testlist);$i++)
		{
			$touser = $testlist[$i]['openid'];
			$ret = customSend($touser, $token, $content, $msgtype);
		}
		
		for($i=0;$i<count($userlist);$i++)
		{
			$touser = $userlist[$i]['openid'];
			$ret = customSend($touser, $token, $content, $msgtype);
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
		
			$info = M ( 'custom_reply_news','nl_','DB_CONFIG1' )->where ( $map )->find ();			
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
		$Member = M('Member','nl_','DB_CONFIG1');
		
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
	
	
	//群发音乐
	public function sendMusic()
	{		
		$content['title'] = explode(",",I('post.music_title'));
		$content['description'] = explode(",",I('post.music_description'));
		$content['musicurl'] = explode(",",I('post.music_url'));
		$content['hqmusicurl'] = explode(",",I('post.music_hqurl'));
		$content['thumb_media_id'] = explode(",",I('post.music_thumb_media_id'));
		
		$userlist = M('Member','nl_','DB_CONFIG1')->select();
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
		
		//$uMap['uid'] = array('in','1,2480,2521');
		//$userlist = M('Member','nl_','DB_CONFIG1')->where($uMap)->select();
		$userlist = M('Member','nl_','DB_CONFIG1')->select();
			
		$token = get_token();
		$msgtype = $filetype;
		
		for($i=0;$i<count($userlist);$i++)
		{
			$touser = $userlist[$i]['openid'];
			$content['media_id'] = $media_id;
			$ret = customSend($touser, $token, $content, $msgtype);
			usleep(10);
		}
		
		//$content['media_id'] = $media_id;
		//$touser = "o8TQCj8ch3DuyerWWZjI8zsONdEA";
		//$ret = customSend($touser, $token, $content, $msgtype);
				
		echo "OK Media:".$ret;
	}
	
	//上传多媒体文件，获得media_id
	public function getMediaId($filename,$filetype)
	{
		
		$access_token = getAccessToken();
		
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$access_token."&type=".$filetype;
		
		$filepath = "D:/wamp/www/Public/media/".$filename;
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
}