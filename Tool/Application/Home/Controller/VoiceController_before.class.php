<?php
namespace Home\Controller;
use Think\Controller;
class VoiceController extends Controller {
    
	//检查未审核的语音。未审核：0，合格：1，不合格：2; 还应该把审核者的ID加上。
	public function doCheckVoice() {
		
		$voiceid = I('post.voiceid');
		$status = intval(I('post.status'));
		
		$Voice = M ( 'Voice','nl_','DB_CONFIG_NPULIFE_DATA');
		$vMap['id'] = $voiceid;
		$theVoice = $Voice->where($vMap)->find();
		
		$theVoice['status'] = $status;
		$Voice->save($theVoice);
		
		//向选手发送一条提醒。
		$playerId = $theVoice['openid'];//选手的openid		
		$token = "535ca7e3cde42";//get_token();
		$msgtype = 'text';
		switch($status)
		{
			case 1:				
				$content = "恭喜您，审核通过了！";
				break;
			case 2:
				$content = "不好意思，您的作品没有审核通过。可能是时长未达到40秒，或者被怀疑为非本人现场录制，或者您撤回了语音导致无法审核。您可以重新提交作品~";
				break;
		}
		customSend($playerId, $token, $content, $msgtype);
		
		$result['message'] = "审核完成啦！";
		$result['status'] = $status;
		$this->ajaxReturn($result,"JSON");
		
		$this->display();
	}
	//检查某一个未审核的语音
	public function checkOneVoice() {
	
		$voiceid = I('get.voiceid');
		
		$Voice = M ( 'Voice','nl_','DB_CONFIG_NPULIFE_DATA');
		$vMap['id'] = $voiceid;
		$theVoice = $Voice->where($vMap)->find();
		
		$theVoice['view_count']++;
		$Voice->save($theVoice);
		
		$this->theVoice = $theVoice;
		
		$this->display();
	}
	public function dispatchVoice($voiceid) {
		//评委的ID列表，随机抽一个ID给他发送声音。评委打开图文审核。
		
		$uMap['uid'] = array('in','1,8,9960,2296,250,16106,71,3014');
		$testlist = M('Member','nl_','DB_CONFIG1')->where($uMap)->select();
		$i = rand(0,count($testlist)-1);
		
		$articles[0] = array("Title"=>"请您审核第".$voiceid."号作品，您辛苦了！",
							"Url"=>"http://wechat.npulife.com/Tool/index.php/Home/Voice/checkOneVoice?voiceid=$voiceid"
						);		
		$content = $articles;
		$token = "535ca7e3cde42";
		$msgtype = 'news';
		$touser = $testlist[$i]['openid'];//随机抽一个ID给他发送声音。评委打开图文审核。
		$ret = customSend($touser, $token, $content, $msgtype);
	}
	
	//全部作品列表页面
	public function listVoice() {
		
		$lMap['status'] = 1;
		$Voice = M ( 'Voice','nl_','DB_CONFIG_NPULIFE_DATA');
		$voiceList = $Voice->where($lMap)->limit(0,20)->order('createdate desc')->select();
		
		$this->time = time();
		$this->voiceList = $voiceList;
		
		$this->display();
	}
	public function getMoreVoice() {
		$num = 20;
		$page = intval(I('get.page'));
		$time = intval(I('get.time'));
		$time = date('Y-m-d G:i:s',$time);
		$gMap['status'] = 1;
		$gMap['createdate'] = array('lt',$time);
		$Voice = M ( 'Voice','nl_','DB_CONFIG_NPULIFE_DATA');
		$voiceList = $Voice->where($gMap)->limit($page*$num,$num)->order('createdate desc')->select();
		
		$result['status'] = 1;
		$result['data'] = $voiceList;
		$this->ajaxReturn($result,"JSON");
	}
	//////////////////////////////////////////////////////
	public function hotVoice()
	{
		$lMap['status'] = 1;
		$Voice = M ( 'Voice','nl_','DB_CONFIG_NPULIFE_DATA');
		$voiceList = $Voice->where($lMap)->limit(0,20)->order('view_count desc')->select();
		
		$this->time = time();
		$this->voiceList = $voiceList;
		
		$this->display();
	}
	public function getMoreHotVoice()
	{
		$num = 20;
		$page = intval(I('get.page'));
		$time = intval(I('get.time'));
		$time = date('Y-m-d G:i:s',$time);
		
		$Voice = M ( 'Voice','nl_','DB_CONFIG_NPULIFE_DATA');
		$gMap['status'] = 1;
		$gMap['createdate'] = array('lt',$time);
		$voiceList = $Voice->where($gMap)->limit($page*$num,$num)->order('view_count desc')->select();
		
		$result['status'] = 1;
		$result['data'] = $voiceList;
		$this->ajaxReturn($result,"JSON");
	}
	///////////////////////////////////////////////////////////////////////////////
	//列出所有等待审核的作品
	public function listCheckingVoice()
	{
		$lMap['status'] = 0;
		$Voice = M ( 'Voice','nl_','DB_CONFIG_NPULIFE_DATA');
		$voiceList = $Voice->where($lMap)->order('createdate desc')->select();
	
		$this->voiceList = $voiceList;
		
		$this->display();
	}
		
	//作品提交页面，可以填写信息。
	public function submitVoice() {
		
		$openid = I('get.openid');
		$media_id = I('get.media_id');
		$this->openid = $openid;
		$this->media_id = $media_id;
		
		//再检查Voice表里是不是已经有这个openid了。如果曾经被审核不通过，也可以参赛。
		$Voice = M ( 'Voice','nl_','DB_CONFIG_NPULIFE_DATA');
		$oMap['openid'] = $openid;
		$oMap['status'] = array('in','1,0');
		$theSong = $Voice->where($oMap)->find();
		
		if($theSong)
		{
			redirect("http://wechat.npulife.com/Tool/index.php/Home/Voice/listVoice");
		}
		else
		{
			$this->display();
		}
	}
	
	public function playVoice()
	{
		$voiceid = I('get.voiceid');
		
		$Voice = M ( 'Voice','nl_','DB_CONFIG_NPULIFE_DATA');
		$vMap['id'] = $voiceid;
		$theVoice = $Voice->where($vMap)->find();
		
		$theVoice['view_count']++;
		$Voice->save($theVoice);
		
		$this->theVoice = $theVoice;
		
		$this->display();
	}
		
	public function recordVoice()
	{
		$media_id = I('post.media_id');
		$openid = I('post.openid');
		$songname = I('post.songname');
		$nickname = I('post.nickname');		
		
		//还有可能填的是空格，非法字符，注入，超字数
		if(strstr($nickname," ")||strstr($nickname,"　")||strstr($songname," ")||strstr($songname,"　"))
		{
			$result['status'] = 2;
			$result['message'] = "不能有空格";
			$this->ajaxReturn($result,"JSON");
		}
		if(empty($nickname)||mb_strlen($nickname)>30)
		{
			$result['status'] = 2;
			$result['message'] = "昵称填写不正确";
			$this->ajaxReturn($result,"JSON");
		}
		if(empty($songname)||mb_strlen($songname)>60)
		{
			$result['status'] = 2;
			$result['message'] = "歌曲名填写不正确";
			$this->ajaxReturn($result,"JSON");	
		}
		
		//先检查openid看是不是用户。防止伪造Openid参赛。
		$Member = M('Member','nl_','DB_CONFIG1');
		$oMap['openid'] = $openid;
		$theUser = $Member->where($oMap)->find();
		if(!$theUser)
		{
			$result['status'] = 2;
			$result['message'] = "用户不存在";
			$this->ajaxReturn($result,"JSON");	
		}	
		
		//再检查Voice表里是不是已经有这个openid了。
		$Voice = M ( 'Voice','nl_','DB_CONFIG_NPULIFE_DATA');
		$oMap['openid'] = $openid;
		$oMap['status'] = array('in','1,0');
		$theSong = $Voice->where($oMap)->find();
		
		if(!$theSong)
		{
			//保存作品		
			$sMap['openid'] = $openid;
			$sMap['nickname'] = $nickname;
			$sMap['songname'] = $songname;
			$sMap['status'] = 0;
			$id = $Voice->add($sMap);	//要产生选手编号。
			if($id)
			{
				$access_token = getAccessToken();
				$url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$media_id";
				$fileInfo = $this->downloadWeixinFile($url);
				$filename = "$id.amr";
				$fileurl = "Upload/WeSing/".$filename;
				$this->saveWeixinFile($fileurl, $fileInfo["body"]);
				
				exec("ffmpeg.exe -i Upload/WeSing/$id.amr Upload/WeSing/$id.mp3");//把amr转码成mp3
				
				//$this->test($fileInfo['header']['content_type']);
			
				//$sMap['id'] = $id;
				//$sMap['filename'] = $filename;
				//$Voice->save($sMap);
				
				//分发给评委来审核。图文的形式。
				$this->dispatchVoice($id);
				
				//AJAX返回
				$result['status'] = 1;
				$result['id'] = $id;
				$result['message'] = "提交成功";
				$this->ajaxReturn($result,"JSON");
			}
			else
			{
				$result['status'] = 2;
				$result['message'] = "没有保存成功";
				$this->ajaxReturn($result,"JSON");
			}		
		}
		else
		{
			$result['status'] = 2;
			$result['message'] = "已提交过作品。";
			$this->ajaxReturn($result,"JSON");
		}
	}
	
	private function downloadWeixinFile($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);    
		curl_setopt($ch, CURLOPT_NOBODY, 0);    //只取body头
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$package = curl_exec($ch);
		$httpinfo = curl_getinfo($ch);
		curl_close($ch);
		$imageAll = array_merge(array('header' => $httpinfo), array('body' => $package)); 
		return $imageAll;
	}
 
	private function saveWeixinFile($filename, $filecontent)
	{
		/*
		$local_file = fopen($filename, 'w');
		if (false !== $local_file){
			if (false !== fwrite($local_file, $filecontent)) {
				fclose($local_file);
			}
		}
		*/
		file_put_contents($filename,$filecontent);
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