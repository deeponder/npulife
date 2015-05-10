<?php
//
namespace Addons\Activity\Controller;
use Home\Controller\AddonsController;
class ActivityController extends AddonsController{
	public function _initialize()
	{	
		date_default_timezone_set ('PRC');
		header("Content-Type:text/html; charset=utf-8");
		$activity_ini=M("activity_list")->field('activity_ID,deadline,status')->where("status=1")->select();
		for($i=0;$i<count($activity_ini);$i++)//自动结束到达截止日期的活动报名
		{
			if($activity_ini[$i]['deadline']<=date("Y-m-d H:i:s",time())){
				$activity_ID=$activity_ini[$i]['activity_ID'];
				$terminate=M("activity_list")->where("activity_ID='$activity_ID'")->setField('status','0');
				//dump($terminate);
			}		
		}
	}
	public function index(){
		$this->title='组织活动';
		$latest=M("activity_list")->field('activity_ID,activity_theme,school_number,status')->order('activity_ID desc')->limit(5)->select();	//最新发起的5条活动
		for($i=0;$i<5;$i++){
			$map['school_number']=$latest[$i]['school_number'];
			$truename=M("member")->where($map)->field('truename')->find();
			$latest[$i]['truename']=implode($truename);
			//dump($latest[$i]['truename']);
			if($latest[$i]['status']==0)
				$color[$i]="gray";
			else{
				$color[$i]="blue";
				}
		}
		$this->assign('color',$color);
		$this->assign('latest',$latest);
		$this->display();
		//dump($color);
	}
	public function launch()//发起活动按钮
	{
		$this->title='发起活动';
		$school_number=$this->getSchoolNumber();
		if($school_number== -1 ){
			echo "<script charset='utf-8' type='text/javascript'>alert('请先实名认证！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/binding/_controller/binding/_action/index.html';</script>";
			//redirect(U('Binding/personInfo'));//跳转不到正确的地址？？？
		}
		else{
			$pInfo = M("member")->field('truename')->where("school_number ='$school_number'" )->find();
			$this->assign('pInfo',$pInfo);
			//dump($school_number);
			$this->display();
		}
	}
	public function launch_info()//填写活动信息
	{
		$school_number=$this->getSchoolNumber();
		if($school_number== -1 ){
			echo "<script language='javascript' type='text/javascript'>alert('请先实名认证！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/binding/_controller/binding/_action/index.html';</script>";
		}
		else{
			$map['activity_theme'] = I('post.activity_theme');
			$map['activity_detail'] = I('post.activity_detail');
			$map['deadline'] = I('post.deadline');
			$map['school_number'] = $school_number;
			if(!empty($map['activity_theme'])||!empty($map['activity_detail'])||!empty($map['deadline'])){
				$res = M("activity_list")->add($map);
				$name="发起";
				$summary="你可以点击发起的主题来查看报名信息或者管理本活动";
				$this->sendSuccess($name,$res,$summary);//向用户推送发起成功的消息。
				redirect('http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/activity/_controller/activity/_action/index.html');
			}
			else{
				echo "<script language='javascript'>alert('数据为空，请重新提交！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/activity/_controller/activity/_action/launch.html';</script>";
			}
		}
	}
	public function show()//显示活动详情
	{
		$id=I('get.ID');
		$this->assign('id',$id);//报名或取消报名时使用
		$activity_detail=M("activity_list")->field('school_number,activity_theme,activity_detail,deadline,status')->where("activity_ID ='$id'" )->find();//活动详情
		$map["school_number"]=$this->getSchoolNumber();
		$map["activity_ID"]=$id;
		$sign_up=M('activity_signup')->field('school_number,activity_ID')->where($map)->find();
		//查找报名信息以确定是否已经报名
		//dump($sign_up);
		if($map["school_number"]==$activity_detail["school_number"])//是否是本活动发起人
		{
			$signupinfo="查看报名信息";
			$this->assign('signupinfo',$signupinfo);
		}
		if(!empty($activity_detail))
		{
			$map['school_number']=$activity_detail['school_number'];
			$truename=M("member")->where($map)->field('truename')->find();
			$count=M("activity_signup")->where("activity_ID ='$id'" )->count();//报名人数
			//dump($count);
			$this->assign('activity_detail',$activity_detail);
			$this->assign('truename',$truename);
			$this->assign('count',$count);
			//dump($map['school_number']);
			if($activity_detail['status']==1){
				if(empty($sign_up)){
					$disp["join"]='';//设置参加活动和取消参加两个按钮的可用性
					$disp["unjoin"]='none';
				}
				else{
					$disp["join"]='none';//设置参加活动和取消参加两个按钮的可用性
					$disp["unjoin"]='';
				}
			}
			else{
				$disp["join"]='none';
				$disp["unjoin"]='none';
				$info="报名已结束！";
			}
			$this->title=$activity_detail['activity_theme'];
			$this->assign('info',$info);
			$this->assign('disp',$disp);
			$this->display();
		}
		else{
				echo "<script language='javascript'>alert('查看活动失败，请重试！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/activity/_controller/activity/_action/index.html';</script>";
			}
			
	}
	protected function signup_show(){//显示报名人员信息
		$school_number=$this->getSchoolNumber();
		$activity_ID=I('get.activity_ID');
		$activity_SC=M("activity_list")->field('school_number')->where("activity_ID ='$activity_ID'" )->find();//发起人学号
		if($school_number==implode($activity_SC)){//如果发起人学号和该用户学号一致，继续
			$signup_detail=M("activity_signup")->join('JOIN nl_member ON nl_activity_signup.school_number = nl_member.school_number')->join('JOIN nl_activity_list ON nl_activity_signup.activity_ID = nl_activity_list.activity_ID')->field('nl_activity_list.activity_theme AS activity_theme,signup_date,truename,nl_activity_signup.school_number AS school_number,mobile')->where("nl_activity_signup.activity_ID='$activity_ID'")->order('signup_date asc')->select();
			//连接activity_signup、activity_list和member表进行查询
			//包含报名时间，学号，真实名字,联系方式,活动主题
			//dump($signup_detail);
			echo "<script> var activity_ID = '".$activity_ID."'; </script>";
			$this->assign('signup_detail',$signup_detail);
			$this->display();
		}
	}
	public function join(){	
		$activity_ID=I('get.ID');
		$school_number=$this->getSchoolNumber();
		$activity["activity_ID"]=$activity_ID;
		$activity["school_number"]=$school_number;
		$sign_up=M("activity_signup")->field('school_number,activity_ID')->where($activity)->find();//查找报名信息以确定是否重复报名
		if($school_number== -1 ){
			echo "<script language='javascript'>alert('请先实名认证！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/binding/_controller/binding/_action/index.html';</script>";
		}
		else{
			if(empty($sign_up)){//参加活动
				$join["school_number"]=$school_number;
				$join["activity_ID"]=$activity_ID;
				$res=M("activity_signup")->add($join);
				if(!$res)
				{ 
					echo "<script language='javascript'>alert('参加失败！请联系人工客服报告错误！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/activity/_controller/activity/_action/index.html';</script>";
				}
				else{
					$name="报名";
					$summary="请保持手机畅通以便接收发起人的最新通知哦！你也可以在活动报名结束前点击本消息或前往“我参与的活动”取消参与！";
					$this->sendSuccess($name,$activity_ID,$summary);//向用户推送发起成功的消息。
					echo "<script language='javascript'>alert('参加活动成功!');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/activity/_controller/activity/_action/index.html';</script>";
				}
			}
			else{
				echo "<script language='javascript'>alert('请不要重复参加本活动');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/activity/_controller/activity/_action/index.html';</script>";
			}
		}
	}
	public function unjoin(){
		$activity_ID=I('get.ID');
		//dump($activity_ID);
		$school_number=$this->getSchoolNumber();
		$activity["activity_ID"]=$activity_ID;
		$activity["school_number"]=$school_number;
		$unsign_up=M('activity_signup')->field('school_number,activity_ID')->where($activity)->find();//查找报名信息以确定该信息仍存在
		if($school_number== -1 ){
			echo "<script language='javascript'>alert('请先实名认证！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/binding/_controller/binding/_action/index.html';</script>";
		}
		else if(!empty($unsign_up)){//取消参加
			$unjoin = M('activity_signup')->where($activity)->delete();
			if(!$unjoin)
			{
				echo "<script language='javascript'>alert('取消失败！请联系人工客服报告错误！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/activity/_controller/activity/_action/index.html';</script>";
			}
			else{
				echo "<script language='javascript'>alert('取消成功！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/activity/_controller/activity/_action/index.html';</script>";
			}
		}
		else{
			echo "<script language='javascript'>alert('报名已取消或者您没有参加次活动');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/activity/_controller/activity/_action/index.html';</script>";
		}
	}
	public function listr()
	{
		$flag=I('get.flag');
		switch($flag)
		{
			case 1://得到所有活动
				$this->title='活动列表';
				$theme=M("activity_list")->join('JOIN nl_member ON nl_activity_list.school_number = nl_member.school_number')->field('activity_ID,activity_theme,startime,truename,nl_activity_list.status AS status')->order('nl_activity_list.startime asc')->select();//连接activity_list和member进行查询
				//包含活动发起时间，以活动发起时间排序
				$type="<b>发起时间:</b>";
				$this->assign('theme',$theme);
				$this->assign('type',$type);
				//dump($theme);
				$this->display();
				break;
			case 2://我参加的活动
				$this->title='我参与的活动';
				$school_number =$this->getSchoolNumber();
				if($SchoolNumber!=-1){
					$theme=M("activity_signup")->join('JOIN nl_activity_list ON nl_activity_signup.activity_ID = nl_activity_list.activity_ID')->join('JOIN nl_member ON nl_activity_list.school_number = nl_member.school_number')->field('nl_activity_signup.activity_ID AS activity_ID ,activity_theme,signup_date,truename,nl_activity_list.status AS status')->where("nl_activity_signup.school_number='$school_number'")->order('signup_date asc')->select();
					//连接activity_signup、activity_list和member表进行查询
					//包含报名时间，以报名时间排序$theme
					$type="<b>报名时间:</b>";
					$this->assign('theme',$theme);
					$this->assign('type',$type);
					//dump($school_number);
					//dump($theme);
					$this->display();
				}
				else{
					echo "<script language='javascript'>alert('请先实名认证！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/binding/_controller/binding/_action/index.html';</script>";
				}
				break;
		}
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
	public function terminate()
	{
		echo "<script language='javascript'>alert('终止状态不正常！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/activity/_controller/activity/_action/index.html';</script>";
		if($this->getSchoolNumber() == -1 ){
			echo "<script language='javascript'>alert('请先实名认证！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/binding/_controller/binding/_action/index.html';</script>";
		}
		else{//终止活动
			$activity_ID=I('get.activity_ID');
			$isterm=M("activity_list")->field('status')->where("$activity_ID='$activity_ID'")->find;//是否已终止
			if(!$activity_ID||implode($isterm))
			{
				echo "<script language='javascript'>alert('终止状态不正常！');window.location.href='http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/activity/_controller/activity/_action/index.html';</script>";
			}
			else{
					$map["status"]=false;
					$terminate=M("activity_list")->where("activity_ID='$activity_ID'")->save($map);
				}
			}
	}
	private function sendSuccess($name,$activity_ID,$summary)
	{
		$activity_theme=M("activity_list")->field('activity_theme')->where("activity_ID='$activity_ID'")->find();
		$activity_theme=implode($activity_theme);
		$articles[0]['Title'] = "成功".$name."活动：".$activity_theme;
		$articles[0]['Description'] =$summary; 
		$articles[0]['PicUrl'] = "http://wechat.npulife.com/Public/Home/images/activity.jpg";
		$articles[0]['Url'] = "http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/activity/_controller/activity/_action/listr/flag/2.html";
		$touser =get_openid();
		$token = get_token();
		$content = $articles;
		$msgtype = "news";
		customSend($touser,$token,$content,$msgtype);
	}
}