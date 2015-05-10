<?php

namespace Addons\AskingForLeave\Controller;
use Home\Controller\AddonsController;

class AskingForLeaveController extends AddonsController{
	public function _initialize()
	{	
		date_default_timezone_set ('PRC');
		header("Content-Type:text/html; charset=utf-8");
	}
	public function index(){
		$this->title='在线请假系统';
		$school_number=$this->getSchoolNumber();
		$isAdmin=$this->isAdmin($school_number);
		$this->assign('isAdmin',$isAdmin);
		if($school_number== -1 ){
			echo "<script charset='utf-8' type='text/javascript'>alert('请先实名认证！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/binding/_controller/binding/_action/index.html';</script>";
		}
		else{
			if($isAdmin==0){
				$this->studentPage();//如果是学生
				$style["student"]='inline';
				$style["admin"]='none';
				}
			else if($isAdmin==2){
				$this->adminPage();//如果是辅导员
				$this->studentPage();
				$style["student"]='inline';
				$style["admin"]='inline';
				}
			$this->assign('style',$style);
			$this->display();
		}
	}
	public function studentPage(){
		$school_number=$this->getSchoolNumber();
		$myName = $this->findName($school_number);
		//dump($school_number);
		$this->assign('myName',$myName);
		$tmap["trust"]=M("class")->field('trust')->where("school_number ='$school_number'" )->find();
		$tmap["isResume"]=M("qingjia")->where("school_number ='$school_number' AND  isResume='0'" )->count();
		$nowDate=date('Y-m-d');
		$chaxun["apply_time"]=array('like','$nowDate%');
		$chaxun["school_number"]=$school_number;
		$tmap["repeated"]=M("qingjia")->where($chaxun)->count();
		if(0/*$tmap["trust"]==0*/){
			$buttonHTML='<center><span style="color:red;"><b>你有不诚信记录，暂时不可在线请假</b></span></center>';
		}
		if($tmap["isResume"]!=0){
			$buttonHTML='<center><span style="color:red;"><b>您存在未销假行为，暂时不可在线请假</b></span></center>';
		}
		else if($tmap["repeated"]!=0){
			$buttonHTML='<center><span style="color:red;"><b>每日只可用本系统申请一次哦~</b></span></center>';
		}
		else{
			$buttonHTML = '<input type="submit" id="post" name="post" value="我要申请">';
		}
		$this->queryResult();
		$this->assign('buttonHTML',$buttonHTML);
	}
	public function adminPage(){
		$qingjiaList=M("qingjia")->field("school_number,wsqj_id,start_time,end_time,apply_time,reason")->where("auth='0'")->order('wsqj_id desc')->select();
		for($i=0;$i<count($qingjiaList);$i++)//未处理请假条
		{
			$name=$this->findName($qingjiaList[$i]["school_number"]);
			$qingjiaList[$i]["name"]=$name;
			
		}
		$this->assign('qingjiaList',$qingjiaList);
		$map["auth"]  = array('neq','0');
		$qingjiaHistory=M("qingjia")->field("school_number,wsqj_id,start_time,end_time,apply_time,reason,auth")->where($map)->order('wsqj_id desc')->select();	
		for($j=0;$j<count($qingjiaHistory);$j++)//历史请假条
		{
			$name=$this->findName($qingjiaHistory[$j]["school_number"]);
			$qingjiaHistory[$j]["name"]=$name;
			if($qingjiaHistory[$j]["auth"]==1){//申请状态
				$qingjiaHistory[$j]["auth"]="已同意本申请";
			}
			else if($qingjiaHistory[$j]["auth"]==2){
				$qingjiaHistory[$j]["auth"]="已拒绝本申请";
			}
		}
		$this->assign('qingjiaHistory',$qingjiaHistory);
	}

	public function apply(){
		$school_number=$this->getSchoolNumber();
		$map['school_number'] = $school_number;
		//$map['toWho'} = I('post.toWho');选择向谁请假
		$map['type'] = I('post.type');
		$map['start_time'] = I('post.start_time');
		$map['end_time'] = I('post.end_time');
		$map['reason'] = I('post.reason');
		$map['ECP'] = I('post.ECP');
		$map['ECP_phone'] = I('post.ECP_phone');
		//if($tmap["trust"]!=0 AND $tmap["isResume"]==0 AND $tmap["repeated"]==0){
		$res = M("qingjia")->add($map);//存入请假表并向学生和辅导员同时推送消息
		$apply_time=implode( M("qingjia")->field("apply_time")->where("wsqj_id='$res'")->find());
		$first="你收到一条于'$apply_time'发起的未处理申请";
		$second="你于'$apply_time'发起了请假申请，请等待批复";
		$name=$this->findName($school_number);
		$remark="";
		$toWhoSc =implode(M("class")->field("counselor")->/*where("school_number='$school_number'")->*/find());//没有班长、辅导员信息，直接写死
		$toWho=implode(M("member")->field("openid")->where("school_number='$toWhoSc'")->find());
		$self=implode(M("member")->field("openid")->where("school_number='$school_number'")->find());
		$this->sendQingjiatiao($first,$toWho,$name,$map['reason'],$map['start_time'],$map['end_time'],$res,$remark);
		$this->sendQingjiatiao($second,$self,$name,$map['reason'],$map['start_time'],$map['end_time'],$res,$remark);
		echo "<script language='javascript'>alert('请假申请已提交，请等待负责老师回复！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/AskingForLeave/_controller/AskingForLeave/_action/index.html';</script>";
	}
	public function content(){
		$school_number=$this->getSchoolNumber();
		$isAdmin=$this->isAdmin($school_number);
		$wsqj_id=I('get.ID');
		$applyerSC=$this->getApplyer($wsqj_id);
		//dump($applyerSC);
		$qingjiaResult=M("qingjia")->where("wsqj_id='$wsqj_id'")->find();//school_number ='$school_number' AND 
		$qingjiaResult["myName"]=$this->findName($applyerSC);
		if(empty($qingjiaResult)){
			echo "<script language='javascript'>alert('查看请假信息时出现错误，请重试！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/AskingForLeave/_controller/AskingForLeave/_action/index.html';</script>";
		}
		switch($isAdmin){
			case 0:	$this->title="请假详情";//普通学生
				if($qingjiaResult["auth"]==1){//申请状态
					$qingjiaResult["auth"]="同意申请";
					if($qingjiaResult["isResume"]==0){//销假状态
						$url=U('resumption?resID='.$wsqj_id);
						$qingjiaResult["isResume"]="<input type='button' id='resumption' name='resumption' value='我要销假' onClick='location.href=\"$url\"'>";
					}
					else{
						$qingjiaResult["isResume"]="已销假";
					}
				}
				else if($qingjiaResult["auth"]==2){
					$qingjiaResult["auth"]="申请被拒绝";
					$qingjiaResult["isResume"]="无需销假";
				}
				else{
					$qingjiaResult["auth"]="请假申请未被处理";
					$qingjiaResult["isResume"]="请等待准假";
				}
				break;
			case 1:break;
			case 2:$this->title="审阅请假信息";//辅导员
					if($qingjiaResult["auth"]==1){//申请状态
						$qingjiaResult["auth"]="已同意本申请";
					}
					else if($qingjiaResult["auth"]==2){
						$qingjiaResult["auth"]="已拒绝本申请";
					}
					else{//处理申请
						$aurl=U('auth?resID='.$wsqj_id);//同意跳转
						$uurl=U('unAuth?resID='.$wsqj_id);//不同意跳转
						$qingjiaResult["auth"]="<br><input type='button' id='auth' name='auth' value='同意申请' onClick='location.href=\"$aurl\"'></br>
						<input type='button' id='unauth' name='unAuth' value='拒绝申请' onClick='location.href=\"$uurl\"'>";
					}
					if($qingjiaResult["isResume"]==1){//销假状态
						$qingjiaResult["isResume"]="已销假";
					}
					else if($qingjiaResult["isResume"]==0){
						$qingjiaResult["isResume"]="未销假";
					}
					break;
			}
		//dump($qingjiaResult["auth"]);
		$this->assign('qingjiaResult',$qingjiaResult);
		$this->display();
	}
	public function queryResult(){
		$school_number=$this->getSchoolNumber();
		$isAdmin=$this->isAdmin($school_number);
		$qingjiaResult=M("qingjia")->field("wsqj_id,start_time,end_time,apply_time,reason,auth")->where("school_number ='$school_number'")->order('wsqj_id desc')->select();
		for($i=0;$i<count($qingjiaResult);$i++)
		{
			if($qingjiaResult[$i]["auth"]==0){
				$qingjiaResult[$i]["auth"]="请假申请未被处理";
			}
			else if($qingjiaResult[$i]["auth"]==1){
				$qingjiaResult[$i]["auth"]="同意申请"; 
			}
			else{
				$qingjiaResult[$i]["auth"]="申请被拒绝"; 
			}
			
		}
		$this->assign('qingjiaResult',$qingjiaResult);
	}
	public function auth(){//同意申请
		$wsqj_id=I('get.resID');
		$school_number=$this->getSchoolNumber();
		$isAdmin=$this->isAdmin($school_number);
		if($isAdmin){
			$authInfo=M("qingjia")->field("auth")->where("wsqj_id ='$wsqj_id'")->find();
			if($authInfo["auth"]==0){
				$nowDate=date('Y-m-d H:i:s',time());
				$auth=array('auth'=>'1','auth_time'=>"$nowDate");
				$res=M("qingjia")->where("wsqj_id='$wsqj_id'")->setField($auth);
				$applyerSC=$this->getApplyer($wsqj_id);
				$map=M("qingjia")->field("reason,start_time,end_time")->where("wsqj_id='$wsqj_id'")->find();
				$auth_time=implode( M("qingjia")->field("auth_time")->where("wsqj_id='$wsqj_id'")->find());//同意时间
				$first="辅导员于'$auth_time'同意了你的申请";
				$name=$this->findName($applyerSC);
				$remark="辅导员同意了你的申请，假期间请注意安全，请保持与辅导员的联系！请在假期结束前前往请假页面销假，否则可能进入不诚信名单并限制在线请假！";
				//$toWhoSc =implode(M("class")->field("counselor")->/*where("school_number='$school_number'")->*/find());//没有班长、辅导员信息，直接写死
				$toWho=implode(M("member")->field("openid")->where("school_number='$applyerSC'")->find());
				$this->sendQingjiatiao($first,$toWho,$name,$map['reason'],$map['start_time'],$map['end_time'],$wsqj_id,$remark);
				echo "<script language='javascript'>alert('已同意该申请，对方将接到一条确认消息！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/AskingForLeave/_controller/AskingForLeave/_action/index.html';</script>";
			}
			else{
				echo "<script language='javascript'>alert('此申请已被处理！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/AskingForLeave/_controller/AskingForLeave/_action/index.html';</script>";
			}
		}
		else{
			echo "<script language='javascript'>alert('身份认证错误，请勿越权批准申请！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/AskingForLeave/_controller/AskingForLeave/_action/index.html';</script>";
		}
		
	}
	public function unAuth(){//拒绝申请
		$wsqj_id=I('get.resID');
		$school_number=$this->getSchoolNumber();
		$isAdmin=$this->isAdmin($school_number);
		if($isAdmin){
			$authInfo=M("qingjia")->field("auth")->where("wsqj_id ='$wsqj_id'")->find();
			if($authInfo["auth"]==0){
				$nowDate=date('Y-m-d H:i:s',time());
				$unAuth=array('auth'=>'2','auth_time'=>"$nowDate");
				$res=M("qingjia")->where("wsqj_id='$wsqj_id'")->setField($unAuth);
				$applyerSC=$this->getApplyer($wsqj_id);
				$map=M("qingjia")->field("reason,start_time,end_time")->where("wsqj_id='$wsqj_id'")->find();
				$auth_time=implode( M("qingjia")->field("auth_time")->where("wsqj_id='$wsqj_id'")->find());//
				$first="辅导员于'$auth_time'拒绝了你的申请";
				$name=$this->findName($school_number);
				$remark="辅导员拒绝了你的申请，如果有疑问，建议直接咨询辅导员";
				//$toWhoSc =implode(M("class")->field("counselor")->/*where("school_number='$school_number'")->*/find());//没有班长、辅导员信息，直接写死
				$toWho=implode(M("member")->field("openid")->where("school_number='$applyerSC'")->find());
				$this->sendQingjiatiao($first,$toWho,$name,$map['reason'],$map['start_time'],$map['end_time'],$wsqj_id,$remark);
				
				echo "<script language='javascript'>alert('已拒绝该申请，对方将接到一条确认消息！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/AskingForLeave/_controller/AskingForLeave/_action/index.html';</script>";
			}
			else{
				echo "<script language='javascript'>alert('此申请已被处理！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/AskingForLeave/_controller/AskingForLeave/_action/index.html';</script>";
			}
		}
		else{
			echo "<script language='javascript'>alert('身份认证错误，请勿越权批准申请！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/AskingForLeave/_controller/AskingForLeave/_action/index.html';</script>";
		}
	}
	public function isAdmin($school_number){//是否是辅导员或者班长etc,
		if($school_number=="2012262505"){//由于暂时还没有辅导员信息，先规定某个人是辅导员
			return 2;//辅导员
		}
		else{
			return 0;//不是管理人员
		}
		//return 1;//班长
	
	
	}
	public function resumption (){//销假操作
		$wsqj_id=I('get.resID');
		$school_number=$this->getSchoolNumber();
		$resumption=M("qingjia")->where("school_number ='$school_number' AND wsqj_id='$wsqj_id'")->find();
		if($resumption["isResume"]==0){
			$nowDate=date('Y-m-d H:i:s',time());
			$isResume=array('isResume'=>'1','resume_time'=>"$nowDate");
			//dump($isResume);
			$res=M("qingjia")->where("wsqj_id='$wsqj_id' AND school_number='$school_number'")->setField($isResume);
			if($res){//销假成功，发送销假成功消息
				$name=$this->findName($school_number);
				$resume_time=implode( M("qingjia")->field("resume_time")->where("wsqj_id='$wsqj_id'")->find());
				$map=M("qingjia")->field("reason,start_time,end_time")->where("wsqj_id='$wsqj_id'")->find();
				$first="'$name'于'$resume_time'进行了销假操作";
				$second="你于'$resume_time'进行了销假操作";
				$remark="";
				$toWhoSc =implode(M("class")->field("counselor")->/*where("school_number='$school_number'")->*/find());//没有班长、辅导员信息，直接写死
				$toWho=implode(M("member")->field("openid")->where("school_number='$toWhoSc'")->find());
				$self=implode(M("member")->field("openid")->where("school_number='$school_number'")->find());
				$this->sendQingjiatiao($first,$toWho,$name,$map['reason'],$map['start_time'],$map['end_time'],$wsqj_id,$remark);
				$this->sendQingjiatiao($second,$self,$name,$map['reason'],$map['start_time'],$map['end_time'],$wsqj_id,$remark);
				echo "<script language='javascript'>alert('销假成功，你和辅导员将收到销假成功的消息！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/AskingForLeave/_controller/AskingForLeave/_action/index.html';</script>";
				//$this->sendSuccess();//发送销假信息
			}
			else{//销假失败
				echo "<script language='javascript'>alert('销假失败，请重试！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/AskingForLeave/_controller/AskingForLeave/_action/index.html';</script>";
			}
			
		}
		else{
			echo "<script language='javascript'>alert('销假状态错误！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/AskingForLeave/_controller/AskingForLeave/_action/index.html';</script>";
		}
	}
	public function findName($school_number){//查找真实姓名
		$name=M('member')->field('truename')->where("school_number ='$school_number'" )->find();
		return implode($name);
	}
	protected function getSchoolNumber(){//获取学号
		$openId = get_openid();
		if($openId != -1){
			$school_number = M('Member')->field('school_number')->where("openid ='$openId'" )->find();
			if(!empty($school_number[school_number])){
				return  $school_number[school_number];
			}
		}
		return -1;
	}
	protected function getApplyer($ID){//获取申请人学号
		$school_number = M('qingjia')->field('school_number')->where("wsqj_id ='$ID'" )->find();
		if(!empty($school_number[school_number])){
			return  $school_number[school_number];
		}
		return -1;
	}
	private function sendSuccess()//发送消息
	{
		$activity_theme=M("activity_list")->field('activity_theme')->where("activity_ID='$activity_ID'")->find();
		$activity_theme=implode($activity_theme);
		$articles[0]['Title'] = "请假申请已提交：".$activity_theme;
		$articles[0]['Description'] =$summary; 
		$articles[0]['PicUrl'] = "http://wechat.npulife.com/Public/Home/images/activity.jpg";
		$articles[0]['Url'] = "http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/activity/_controller/activity/_action/listr/flag/2.html";
		$touser =get_openid();
		$token = get_token();
		$content = $articles;
		$msgtype = "news";
		customSend($touser,$token,$content,$msgtype);
	}
	/*
	private function sendQingjiatiao($first,$toWho,$name,$reason,$start_time,$end_time,$wsqj_id,$remark) {
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

		姓名：{{childName.DATA}}
		请假时间：{{time.DATA}}
		请假理由：{{reason.DATA}}
		{{remark.DATA}}
		
		$thisUrl='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/AskingForLeave/_controller/AskingForLeave/_action/content/ID/'.$wsqj_id;
		$data = "{
			\"touser\":\"".$toWho."\",
			\"template_id\":\"mFwWgXZc-J-9cBPoo5Kb4pQeF80GvSlwjXIZj3b7My4\",
			\"url\":\"$thisUrl\",
			\"topcolor\":\"#FF0000\",
			\"data\":{
				\"first\":{\"value\":\"$first\",\"color\":\"#173177\"},
				\"childName\":{\"value\":\"$name\",\"color\":\"#173177\"},
				\"time\":{\"value\":\"$start_time至$end_time\",\"color\":\"#173177\"},
				\"reason\":{\"value\":\"$reason\",\"color\":\"#173177\"},
				\"remark\":{\"value\":\"$remark\",\"color\":\"#173177\"}
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
	}
*/
}
