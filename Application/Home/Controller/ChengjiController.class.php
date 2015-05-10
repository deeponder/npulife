<?php
namespace Home\Controller;
use Think\Controller;


class ChengjiController extends HomeController {	
	
	public function index(){
		$map["openid"] = get_openid();
		$userName = M("Member")->field("school_number")->where($map)->find();
		$userName = $userName["school_number"];
		if(!empty($userName) && count($userName)>=1){
		    $map["XH"] = $userName;
		    $chengji = M("Chengji")->field("KM, XF, CJ")->order("DFRQ desc")->where($map)->select();
			$this->assign("chengji",$chengji);
			$this->assign("title","学习成绩");
			$this->display();
		}else{
			redirect("http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/binding/_controller/binding/_action/personInfo.html");
		}
	}
}