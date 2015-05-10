<?php
namespace Home\Controller;
use Think\Controller;
class DiantaiController extends Controller {
    
    public function a(){


// $voiceid = I('get.voiceid');
		$voiceid =1;
		$Diantai = M ( 'Diantai','nl_','DB_CONFIG_NPULIFE_DATA');
		$vMap['id'] = $voiceid;
		$theVoice = $Diantai->where($vMap)->find();
		
		//检查现在时间是否过了电台播放时间。如果超过就改标题“现在是留言准备时间”。
		
		$theVoice['view_count']++;
		$Diantai->save($theVoice);
		

		$this->theVoice = $theVoice;
		
		// dump($theDiantai);
		$this->display();
		
		    }
	//全部作品列表页面
	public function listDiantai() {
		
		$lMap['status'] = 1;
		$Diantai = M ( 'Diantai','nl_','DB_CONFIG_NPULIFE_DATA');
		$voiceList = $Diantai->where($lMap)->limit(0,20)->order('createdate desc')->select();
		
		$this->time = time();
		$this->voiceList = $voiceList;
		
		$this->display();
	}
	public function getMoreDiantai() {
		$num = 20;
		$page = intval(I('get.page'));
		$time = intval(I('get.time'));
		$time = date('Y-m-d G:i:s',$time);
		$gMap['status'] = 1;
		$gMap['createdate'] = array('lt',$time);
		$Diantai = M ( 'Diantai','nl_','DB_CONFIG_NPULIFE_DATA');
		$voiceList = $Diantai->where($gMap)->limit($page*$num,$num)->order('createdate desc')->select();
		
		$result['status'] = 1;
		$result['data'] = $voiceList;
		$this->ajaxReturn($result,"JSON");
	}
	//////////////////////////////////////////////////////
	public function hotDiantai()
	{
		$lMap['status'] = 1;
		$Diantai = M ( 'Diantai','nl_','DB_CONFIG_NPULIFE_DATA');
		$voiceList = $Diantai->where($lMap)->limit(0,20)->order('view_count desc')->select();
		
		$this->time = time();
		$this->voiceList = $voiceList;
		
		$this->display();
	}

	
	
	public function getMoreHotDiantai()
	{
		$num = 20;
		$page = intval(I('get.page'));
		$time = intval(I('get.time'));
		$time = date('Y-m-d G:i:s',$time);
		
		$Diantai = M ( 'Diantai','nl_','DB_CONFIG_NPULIFE_DATA');
		$gMap['status'] = 1;
		$gMap['createdate'] = array('lt',$time);
		$voiceList = $Diantai->where($gMap)->limit($page*$num,$num)->order('view_count desc')->select();
		
		$result['status'] = 1;
		$result['data'] = $voiceList;
		$this->ajaxReturn($result,"JSON");
	}
	
	
	public function playDiantai()
	{
		$voiceid =I('get.voiceid');
		$Diantai = M ( 'Diantai','nl_','DB_CONFIG_NPULIFE_DATA');
		$vMap['id'] = $voiceid;
		$theVoice = $Diantai->where($vMap)->find();

		$this->sendMusicToUser($theVoice);
		
		$theVoice['view_count']++;
		$Diantai->save($theVoice);
		
		$this->theVoice = $theVoice;
		
		// dump($theDiantai);
		$this->display();
		
	}
	
	/**/
	private function sendMusicToUser($theVoice)
	{
		
		$filename="Diantai.jpg";
		$filetype="image";

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
		
		
		$touser = get_openid();
		$token = "535ca7e3cde42";
		$msgtype = 'music';
		$content['title'] = $theVoice['songname'];
		$content['description'] = "瓜大电台 By ".$theVoice['nickname'];
		$content['musicurl'] = "http://wechat.npulife.com/Tool/Upload/Diantai/".$theVoice['id'].".mp3";
		$content['hqmusicurl'] = "http://wechat.npulife.com/Tool/Upload/Diantai/".$theVoice['id'].".mp3";
		$content['thumb_media_id'] = $media_id;
		customSend($touser, $token, $content, $msgtype);

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