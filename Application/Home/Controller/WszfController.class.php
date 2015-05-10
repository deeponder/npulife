<?php
namespace Home\Controller;
use Think\Controller;


class WszfController extends HomeController {	
	
    public function wszfquery(){	
		$map["openid"] = get_openid();
		$userName = M("Member")->field("school_number")->where($map)->find();
		$userName = $userName["school_number"];
		if(!empty($userName) && count($userName)>=1){
						
			$password = $userName;
			//请从数据库或者session获取用户名和密码。初始密码为学号
			//修改财务处密码的功能，在绑定校园平台账号后可能会不需要，所以没有加进来。
			
			$verifySoap=new \SoapClient(C('VERIFYURL'),array('encoding'=>'UTF-8'));
			$verifyResult =$verifySoap->tc_get_yzm(array("yhbh"=>$userName,"password"=>$password));
			$infoObj = $verifyResult->return;
			if($infoObj->state == '0000' || $infoObj->state == '0001' ){
				$checkCode = $infoObj->yzm;
				$querySoap=new \SoapClient(C('QUERYURL'),array('encoding'=>'UTF-8'));
				$queryResult = $querySoap->tc_getxs_sfqk(array('xh'=>$userName,
					'id'=>$password,'yzm'=>$checkCode));
				$queryResObj=$queryResult->return;
				$queryState=$queryResObj->state;
				if($queryState=='0000'){
					$xhxh=$queryResObj->xh;
					$inforRes = $queryResObj->sfqks;
					$this->assign('xh',$xhxh);
					$this->assign('inforRes',$inforRes);
					$this->display();
				}
			}
		}else{
			redirect("http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/binding/_controller/binding/_action/personInfo.html");
		}
		
    } 
	public function jfmxquery(){	
		$map["openid"] = get_openid();
		$userName = M("Member")->field("school_number")->where($map)->find();
		$userName = $userName["school_number"];
		if(!empty($userName) && count($userName)>=1){
			$password = $userName;
			//请从数据库或者session获取用户名和密码。初始密码为学号
			//修改财务处密码的功能，在绑定校园平台账号后可能会不需要，所以没有加进来。

			$verifySoap=new \SoapClient(C('VERIFYURL'),array('encoding'=>'UTF-8'));
			$verifyResult =$verifySoap->tc_get_yzm(array("yhbh"=>$userName,"password"=>$password));
			$infoObj = $verifyResult->return;
			if($infoObj->state == '0000' || $infoObj->state == '0001' ){
				$checkCode = $infoObj->yzm;
				$querySoap=new \SoapClient(C('QUERYURL'),array('encoding'=>'UTF-8'));
				$queryResult = $querySoap->tc_getxs_jfmx(array('xh'=>$userName,
					'id'=>$password,'yzm'=>$checkCode));
				$queryResObj=$queryResult->return;
				$queryState=$queryResObj->state;
				if($queryState=='0000'){
					$inforRes = $queryResObj->jfmxs;
		
					$this->assign('inforRes',$inforRes);
					$this->display();
				}
			}
		}else{
			redirect("http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/binding/_controller/binding/_action/personInfo.html");
		}
    }
	
	public function index(){
		$map["openid"] = get_openid();
		$userName = M("Member")->field("school_number")->where($map)->find();
		$userName = $userName["school_number"];
		if(!empty($userName) && count($userName)>=1){
			$this->display();
		}else{
			redirect("http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/binding/_controller/binding/_action/personInfo.html");
		}
	}
}