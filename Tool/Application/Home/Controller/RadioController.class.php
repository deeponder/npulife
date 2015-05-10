<?php
namespace Home\Controller;
use Think\Controller;
class RadioController extends Controller {
    
	public function playRadio1(){
		$this->display();
	}
	
	//检查未审核的语音。未审核：0，合格：1，不合格：2; 还应该把审核者的ID加上。
	public function doCheckRadio() {
		
		$radioid = I('get.voiceid');
		$status = I('get.status');
		
		$Radio = M ( 'Radio','nl_','DB_CONFIG1');
		$vMap['id'] = $radioid;
		$theRadio = $Radio->where($vMap)->find();
		
		$theRadio['status'] = $status;
		$Radio->save($theRadio);
		
		//向选手发送一条提醒。
		$playerId = $theRadio['openid'];//选手的openid		
		$token = "535ca7e3cde42";//get_token();
		$msgtype = 'text';
		switch($status)
		{
			case 1:				
				$content = "恭喜您，审核通过了！想要更高的人气吗？那就赶快进入大赛页面分享给你的小伙伴吧~";
				break;
			case 2:
				$content = "不好意思，您的作品没有审核通过。可能是时长未达到40秒，或者声音很不清晰，或者被怀疑为非本人现场录制，或者其他原因导致无法审核。您可以重新提交作品~";
				break;
		}
		customSend($playerId, $token, $content, $msgtype);
		
		$result['message'] = "审核完成啦！";
		$result['status'] = $status;
		$this->ajaxReturn($result,"JSON");
		
		$this->display();
	}
	//检查某一个未审核的语音
	public function checkOneRadio() {
	
		$radioid = I('get.radioid');
		
		$Radio = M ( 'Radio','nl_','DB_CONFIG1');
		$vMap['id'] = $radioid;
		$theRadio = $Radio->where($vMap)->find();
		
		$theRadio['view_count']++;
		$Radio->save($theRadio);
		
		$this->theRadio = $theRadio;
		
		$this->display();
	}
	public function dispatchRadio($radioid) {
		//评委的ID列表，随机抽一个ID给他发送声音。评委打开图文审核。
		
		$uMap['uid'] = array('in','8,1,2465,2296,608,16317');
		$testlist = M('Member','nl_','DB_CONFIG1')->where($uMap)->select();
		$i = rand(0,count($testlist)-1);
		
		$articles[0] = array("Title"=>"请您审核第".$radioid."号作品，您辛苦了！",
							"Url"=>"http://wechat.npulife.com/Tool/index.php/Home/Radio/checkOneRadio?radioid=$radioid"
						);		
		$content = $articles;
		$token = "535ca7e3cde42";
		$msgtype = 'news';
		$touser = $testlist[$i]['openid'];//随机抽一个ID给他发送声音。评委打开图文审核。
		$ret = customSend($touser, $token, $content, $msgtype);
	}
	
	// //初赛全部作品列表页面
	public function listRadio() {
		
		$lMap['status'] = 1;
		$Radio = M ( 'Radio','nl_','DB_CONFIG1');
		$radioList = $Radio->where($lMap)->limit(0,20)->order('createdate desc')->select();
		
		$this->time = time();
		$this->radioList = $radioList;
		$count = count($radioList);
		$this->count = $count;
		$this->display();
	}
	//第二轮复赛全部作品列表页面
	// public function listRadio_round2() {
		
	// 	//$lMap['status'] = 1;
	// 	//$Radio = M ( 'Radio','nl_','DB_CONFIG1');
	// 	//$radioList = $Radio->where($lMap)->limit(0,20)->order('createdate desc')->select();
		
	// 	$Radio = M ( 'Xuanshouinfo','nl_','DB_CONFIG1');
	// 	$vMap['is_challenger']=1;
	// 	$radioList = $Radio->where($vMap)->order('love_count desc')->limit(10)->select();
	// 	//$radioList = $Radio->select();
	// 	$lMap['is_challenger']=0;
	// 	$is_pk = $Radio->where($lMap)->getField('song_name',true);
	// 	$this->is_pk = $is_pk;
	// 	$this->time = time();
	// 	$this->radioList = $radioList;
		
	// 	$this->display();
	// 	}
		
	// //第二轮复赛播放页面
	// public function playRadio_round2(){
	// 	$openId = get_openid();
		
	// 	if($openId != -1){
	// 	//获取音乐ID
	// 	$radioid = I('get.radioid');
	// 	//$radioid = 98;
		
		
	// 	$Radio = M ('Radio','nl_','DB_CONFIG1');
	
	// 	$vMap['id'] = $radioid;
	// 	$theRadio = $Radio->where($vMap)->find();
		
	// 	$theRadio['view_count']++;
	
	// 	$Radio->save($theRadio);		
	// 	$this->theRadio = $theRadio;

 //        //获得已点赞的人
 //        $Love = M('Love_count2','nl_','DB_CONFIG1');
	// 	$wMap['openid']=$openId;
	// 	$songArr = $Love->where($wMap)->getField('song_id',true);
 //        if(in_array($radioid,$songArr))
	//     {
 //          $is_good = 1;		
	// 	}
	// 	else
	// 	{
	// 	  $is_good = 0;
	// 	}
	// 	  $this->is_good=$is_good;
	// 	}
	// 	$this->display();
		

	// }
	//Ajax处理点赞函数 
	public function love_count(){
		$openId = get_openid();
	
		if($openId != -1){
		$radioid = intval($_POST['radioid']);
	
		//点赞数的获取
		$Love = M('Love_count2','nl_','DB_CONFIG_NPULIFE_DATA');
		//$vMap['openid']=$openid;
		$wMap['openid']=$openId;
		$songArr = $Love->where($wMap)->getField('song_id',true);
		//歌曲信息获取
		$Radio = M ('Radio','nl_','DB_CONFIG1');
		$vMap['song_id'] = $radioid;
		$theRadio = $Radio->where($vMap)->find();
		if(in_array($radioid,$songArr))
		{
		 //ajax $a是传向前端的数据
		  $aftergood['is_good'] = "false";
		}
		else{
		
		 $aftergood['is_good'] = "true";
		
		//添加新的点赞记录
	    $exam['openid']=$openId;
		$exam['song_id'] = $radioid;
		//$time = time();
		//$date = date('Y-m-d G:i:s',$time);
		//$exam['create_time'] =$date;
		
		//$exam['create_time'] =date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']);
        $Love ->add($exam);
		
		//歌曲信息中点赞数增加
		$theRadio['love_count']++;
		$Radio->save($theRadio);
		}
		
		//返回Ajax判断数据
		//echo $a;
		//点赞之后显示最新点赞量
			
		//$this->theRadio1 = $theRadio;
		$Radio = M ('Radio','nl_','DB_CONFIG1');
		$vMap['song_id'] = $radioid;
		$theRadio1 = $Radio->where($vMap)->find();
        $aftergood['tem_count']=$theRadio1['love_count'];
		$this->ajaxReturn($aftergood,'JSON');
		}
	}
	
	
	
	
	// public function pkRadio(){
	//     $openId = get_openid();
	// 	//echo $openId;
	// 	if($openId != -1){
	//     $radioname = I('get.song_name');
	// 	$Radio = M ( 'Xuanshouinfo','nl_','DB_CONFIG1');
	// 	$vMap['song_name'] = $radioname;
	// 	$vMap['is_challenger']="1";
	// 	$theRadio = $Radio->where($vMap)->find();

		
	// 	$this->theRadio = $theRadio;

	// 	$lMap['song_name'] = $radioname;
	// 	$lMap['is_challenger']="0";
	// 	$theRadiolist = $Radio->where($lMap)->select();
	// 	$this->radioList = $theRadiolist;
	// 	//前台判断是否有挑战者
	// 	$num = count($theRadiolist);
	//     $this->num = $num;
	// 	//获得已点赞的人
 //        $Love = M('temp_count','nl_','DB_CONFIG1');
	// 	$wMap['openid']=$openId;
	// 	$wMap['song_name'] = $radioname;
	// 	$songArr = $Love->where($wMap)->getField('name',true);
       
	// 	  $this->is_good=$songArr;
	// 	 // dump($songArr);
	// 	}
	// 	$this->display();
	// }
	// /*public function temp_love()
	// {
	// $openId = get_openid();
	// 	//$openId='12312';
	// 	if($openId != -1){
	// 	$name = $_POST['name']; 	
	// 	//echo $name;
	// 	//$radioid=28;
	// 	//点赞数的获取
	// 	$Love = M('temp_count','nl_','DB_CONFIG1');
	// 	$wMap['openid']=$openId;
	// 	//$wMap['song_name'] = $radioname;
	// 	$songArr = $Love->where($wMap)->getField('song_name',true);
	// 	//歌曲信息获取
	// 	$Radio = M ('Xuanshouinfo','nl_','DB_CONFIG1');
	// 	$vMap['name'] = $name;
	// 	$theRadio = $Radio->where($vMap)->find();
	// 	$radioname = $theRadio['song_name'];
	// 	if(in_array($radioname,$songArr))
	// 	{
	// 	 //ajax $a是传向前端的数据
	// 	  $aftergood['is_good'] = "false";
	// 	}
	// 	else{
		
	// 	 $aftergood['is_good'] = "true";
		
	// 	//添加新的点赞记录
	//     $exam['openid']=$openId;
	// 	$exam['song_name'] = $radioname;
	// 	$exam['xuanshou_id'] = $theRadio['song_id'];
	// 	$exam['name'] = $name;
 //        $Love ->add($exam);
		
	// 	//歌曲信息中点赞数增加
	// 	$theRadio['temp_count']++;
	// 	$Radio->save($theRadio);
		
	// 	}
		
	// 	//返回Ajax判断数据
	// 	//echo $a;
	// 	//点赞之后显示最新点赞量
			
	// 	//$this->theRadio1 = $theRadio;
	// 	$Radio = M ('Xuanshouinfo','nl_','DB_CONFIG1');
	// 	$vMap['name'] =$name;
	// 	$theRadio1 = $Radio->where($vMap)->find();
 //        $aftergood['tem_count']=$theRadio1['temp_count'];
	// 	$this->ajaxReturn($aftergood,'JSON');
	// 	}
	// }*/

	public function getMoreRadio() {
		$num = 20;
		$page = intval(I('get.page'));
		// $time = intval(I('get.time'));
		// $time = date('Y-m-d G:i:s',$time);
		$gMap['status'] = 1;
		// $gMap['createdate'] = array('lt',$time);
		$Radio = M ( 'Radio','nl_','DB_CONFIG1');
		$radioList = $Radio->where($gMap)->limit($page*$num,$num)->order('createdate desc')->select();
		
		$result['status'] = 1;
		$result['data'] = $radioList;
		$this->ajaxReturn($result,"JSON");
	}
	//////////////////////////////////////////////////////
	public function hotRadio()
	{
		$lMap['status'] = 1;
		$Radio = M ( 'Radio','nl_','DB_CONFIG1');
		$radioList = $Radio->where($lMap)->limit(0,20)->order('view_count desc')->select();
		
		$this->time = time();
		$this->radioList = $radioList;
		$count = count($radioList);
		$this->count = $count;
		$this->display();
	}
	public function getMoreHotRadio()
	{
		$num = 20;
		$page = intval(I('get.page'));
		// $time = intval(I('get.time'));
		// $time = date('Y-m-d G:i:s',$time);
		
		$Radio = M ( 'Radio','nl_','DB_CONFIG1');
		$gMap['status'] = 1;
		// $gMap['createdate'] = array('lt',$time);
		$radioList = $Radio->where($gMap)->limit($page*$num,$num)->order('view_count desc')->select();
		
		$result['status'] = 1;
		$result['data'] = $radioList;
		$this->ajaxReturn($result,"JSON");
	}
	// ///////////////////////////////////////////////////////////////////////////////
	// //列出所有等待审核的作品
	// public function listCheckingRadio()
	// {
	// 	$lMap['status'] = 0;
	// 	$Radio = M ( 'Radio','nl_','DB_CONFIG1');
	// 	$radioList = $Radio->where($lMap)->order('createdate desc')->select();
	
	// 	$this->radioList = $radioList;
		
	// 	$this->display();
	// }

	public function playRadio()
	{
			$openId = get_openid();
		
		if($openId != -1){
		//获取音乐ID
		$radioid = I('get.radioid');
		//$radioid = 98;
		
		
		$Radio = M ('Radio','nl_','DB_CONFIG1');
	
		$vMap['id'] = $radioid;
		$theRadio = $Radio->where($vMap)->find();
		
		$theRadio['view_count']++;
	
		$Radio->save($theRadio);		
		$this->theRadio = $theRadio;

        //获得已点赞的人
        $Love = M('Love_count2','nl_','DB_CONFIG_NPULIFE_DATA');
		$wMap['openid']=$openId;
		$songArr = $Love->where($wMap)->getField('song_id',true);
        if(in_array($radioid,$songArr))
	    {
          $is_good = 1;		
		}
		else
		{
		  $is_good = 0;
		}
		  $this->is_good=$is_good;
		}
		$this->display();
	}
		
		public function test1(){
					$radio = M('radio','nl_','DB_CONFIG1');
			$sMap['openid'] = "dfd";
			$sMap['nickname'] = "dfd";
			$sMap['songname'] ="dfd";
			$sMap['status'] = 0;
			$sMap['media_id'] = "dfd";
			// $id = $Radio->add($sMap);
			$radio->add($sMap);
		}


	//作品提交页面，可以填写信息。
	public function submitRadio() {
		
		$openid = I('get.openid');
		$media_id = I('get.media_id');
		$this->openid = $openid;
		$this->media_id = $media_id;
		
		//再检查Radio表里是不是已经有这个openid了。如果曾经被审核不通过，也可以参赛。
		$Radio = M ( 'Radio','nl_','DB_CONFIG1');
		$oMap['openid'] = $openid;
		$oMap['status'] = array('in','1,0');
		$theSong = $Radio->where($oMap)->find();
		
		if($theSong)
		{
			redirect("http://content.npulife.com/Tool/index.php/Home/Radio/listRadio");
		}
		else
		{
			$this->display();
		}
	}
	
	public function recordRadio()
	{
		$media_id = I('get.media_id');
		$openid = I('get.openid');
		$songname = I('get.songname');
		$nickname = I('get.nickname');		
		
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
		
		//再检查Radio表里是不是已经有这个openid了。
		$Radio = M ( 'Radio','nl_','DB_CONFIG1');
		$oMap['openid'] = $openid;
		$oMap['status'] = array('in','1,0');
		$theSong = $Radio->where($oMap)->find();
		
		if(!$theSong)
		{
			//保存作品		
			$sMap['openid'] = $openid;
			$sMap['nickname'] = $nickname;
			$sMap['songname'] = $songname;
			$sMap['status'] = 0;
			//$smap['createdate'] = time();
			// $sMap['media_id'] = $media_id;
			$id = $Radio->add($sMap);	//要产生选手编号。
			// dump($Radio);
			if($id)
			{
				$access_token = getAccessToken();
				$url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$media_id";
				$fileInfo = $this->downloadWeixinFile($url);
				$filename = "$id.amr";
				$fileurl = "Upload/Radio/".$filename;
				$this->saveWeixinFile($fileurl, $fileInfo["body"]);
				
				exec("ffmpeg.exe -i Upload/Radio/$id.amr Upload/Radio/$id.mp3");//把amr转码成mp3
				//分发给评委来审核。图文的形式。
				$this->dispatchRadio($id);
				
				//AJAX返回
				// $rasult['nickname'] = $nickname;
				$result['status'] = 1;
				$result['id'] = $id;
				$result['message'] = "提交成功";
				$this->ajaxReturn($result,"JSON");
			}
			else
			{
				// $rasult['nickname'] = $nickname;
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
		file_put_contents($filename,$filecontent);
	}
	
	public function toOnePlayer(){
		$token = "535ca7e3cde42";//get_token();
		$msgtype = 'text';
		$content = "您好,我是瓜大生活圈的音乐审核员,你的音乐很不错,十分新颖,可惜声音实在太小,听不清楚,希望您能重新录制提交.";
		$uMap['id'] = array('in','1');
		$Radio = M ( 'Radio','nl_','DB_CONFIG1');
		$testlist = $Radio->where($uMap)->select();
				
		for($i=0;$i<count($testlist);$i++)
		{
			$touser = $testlist[$i]['openid'];
			$ret = customSend($touser, $token, $content, $msgtype);
		}
		echo "OK";
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

	 //通知中奖信息
   public function send(){
			$radioer = M ( 'Radio','nl_','DB_CONFIG1');
			// $list = $runtable->order('credit DESC')->limit('10')->select();
			$map['id'] = array("in","3,7,8,9,11,12");
			$list = $radioer->where($map)->select();
			// dump($list);
			$num =count($list);
			for($i=0;$i<$num;$i++){			
				$touser = $list[$i]['openid'];
				$token = "535ca7e3cde42";
				$msgtype = "text";
				// $rank = (string)($i+1);
				$content = "恭喜你，通过了我们瓜大星主播的筛选，请将你的姓名和电话以短信的方式，发到我们的小叶同学--18829237119。 我们会进一步联系你，期待你的加入哦~~";
					// $content = "恭喜你在我们的夜跑活动跑了第了，请发送你的联系方式（电话）和昵称到 15399407020，以方便我们联系你，派发奖品";
				// dump($content);
				customSend($touser, $token, $content, $msgtype);
// 
				// dump($openid);
			}
			echo "done!";
			// dump($list[0]['openid']);

}
}