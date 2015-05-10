<?php

namespace Addons\Binding\Controller;
use Home\Controller\AddonsController;

class BindingController extends AddonsController{
	public function index(){
		$openId = get_openid();
		//dump($openId);
		if($this->getSchoolNumber() != -1){
			redirect(U('personInfoNew'));
		}else{
			$this->display();
		}	
	}
	public function unbind($str){
		$openId = get_openid();
		
		if($openId==-1 || $this->getSchoolNumber() == -1 ){
		redirect("http://wechat.npulife.com/index.php/Home/SchoolHome");
		
		}else{//解除绑定
		
			$debind = D('Member')->where("openid='$openId'")->setField('school_number',"");
			if($debind>=1){//解除成功
			     D('Member')->where("openid='$openId'")->setDec('score',200);//积分减去200
				 redirect("http://wechat.npulife.com/index.php/Home/SchoolHome");
			}else{
				redirect(U('personInfo'));
			}
		}
	}
	public function updatehandle(){
			$openId = get_openid();
		if($openId==-1 || $this->getSchoolNumber() == -1){
		   redirect("http://wechat.npulife.com/index.php/Home/SchoolHome");
		}else{
			$uid = D('Member')->where("openid='$openId'")->find();
				//echo $openId;
			$uid = $uid['uid'];
			$params['uid'] =$uid;
			$params['mobile'] = I('post.phone_number');
			$params['qq'] = I('post.qq_number');
			$res = D('Member')->save($params);
			redirect(U('personInfo'));
		}
	}
	
	public function bindhandle(){
		$openId = get_openid();
		if($openId==-1 || $this->getSchoolNumber() != -1){
		   redirect("http://wechat.npulife.com/index.php/Home/SchoolHome");
		}else{
			$map['work_number'] = I('post.student_ID');
			$map['name'] = I('post.name');
			$map['ID_card'] = I('post.ID_number');
			
			$trueInfo = D('PersonalInfo')->where($map)->field('count(*) num')->find();
			$bMap['school_number']=$map['work_number'];
			$schollUsed =  D('Member')->where($bMap)->find();
			//print_r($schollUsed) ;
			
			if(!empty($schollUsed)){
			
				$this->assign("msg","您的学号已和别的微信号绑定");
				$this->display();
			}else if($trueInfo['num'] >= 1){//绑定成功
			    D('Member')->where("openid='$openId'")->setInc('score',200);//积分增加200
				$uid = D('Member')->where("openid='$openId'")->find();
				//echo $openId;
				$uid = $uid['uid'];
				$params['uid'] =$uid;
				$params['mobile'] = I('post.phone_number');
				$params['qq'] = I('post.qq_number');
				$params['shenfengzheng']=$map['ID_card'] ;
				$params['school_number'] = $map['work_number'];
				$params['truename']=$map['name'];
				$res = D('Member')->save($params);
				redirect(U('personInfoNew'));
			}else{
				$this->assign("msg","您输入的信息不准确，或者我们还没有收录您的信息（抱歉,暂时没有博士信息！）");
				$this->display();
			}
		}	
	}
	public function personInfo(){
		$openId = get_openid();
		$oMap['openid']=$openId;
		if($openId!=-1){
			$pInfo = M('Member')->field('school_number,truename,mobile,qq,score')->where($oMap)->find();
			if(!empty($pInfo) && !empty($pInfo['school_number'])){
				$this->assign('pInfo',$pInfo);
				
				$vcard['vname'] = $pInfo['truename'];
				$vcard['vtel'] = $pInfo['mobile'];
				
				$vcardPic = $this->generateQRfromGoogle($vcard);
				$this->assign('vcardPic',$vcardPic);
				
				$this->display();
			}else{
				redirect(U('index'));
			}
		}		
	}
	
	public function personInfoNew()
	{
		$openId = get_openid();
		$oMap['openid']=$openId;
		if($openId!=-1){
			$pInfo = M('Member')->where($oMap)->find();
			if(!empty($pInfo) && !empty($pInfo['school_number'])){
				
				//获得头像
				$token = get_token();
				$openid = get_openid();		
				$userinfo = getWeixinUserInfo($openid, $token);
				$headPic = $userinfo['headimgurl'];
				
				$this->assign('headPic',$headPic);
				
				$this->assign('user',$pInfo);
				
				$vcard['vname'] = $pInfo['truename'];
				$vcard['vtel'] = $pInfo['mobile'];
				
				$vcardPic = $this->generateQRfromGoogle($vcard);
				$this->assign('vcardPic',$vcardPic);
				
				$this->display();
			}else{
				redirect(U('index'));
			}
		}
	}
	
	public function updateInfo(){
	    $this->display();
	}
	
	protected function getSchoolNumber(){
		$users = M('Member');
		$openId = get_openid();
		$oMap['openid']=$openId;
		if($openId != -1){
			$school_number = $users->field('school_number')->where($oMap )->find();
			if(!empty($school_number['school_number']) &&  $school_number['school_number']!='' ){
				return  $school_number['school_number'];
			}
		}
		return -1;
	}
	
	protected function generateQRfromGoogle($vcard,$widhtHeight ='150',$EC_level='L',$margin='0') 
	{
		if($vcard){
			$chl = "BEGIN:VCARD\nVERSION:3.0". //vcard头信息   
				"\nFN:".$vcard['vname'].
				"\nTEL:".$vcard['vtel']. 
				"\nADR:".$vcard['vaddress']. 
				"\nEND:VCARD"; //vcard尾信息
			
			return '<img src="http://chart.apis.google.com/chart?chs='.$widhtHeight.'x'.$widhtHeight.'&cht=qr&chld='.$EC_level.'|'.$margin.'&chl='.urlencode($chl).'" alt="QR code" widhtHeight="'.$size.'" widhtHeight="'.$size.'"/>'; 
		} 
	}
}
