<?php

namespace Home\Controller;

class PersoninfoController extends HomeController {
    public function index(){
    	//判断是否是从微信进入
    	$openId = get_openid();
		if($openId!=-1){
			$pInfo=$this->getInfo();
			$this->pInfo = $pInfo;
			$this->display();
			
		}else{
				echo "啊哈，请关注西北工大为生活（npulife）~~";
		}
	}
	
	public function getInfo(){
		$openId = get_openid();
		$oMap['openid']=$openId;
		$pInfo=M('Member')->where($oMap)->find();
		//获得头像和昵称
		$token = get_token();
		$openid = get_openid();		
		$userinfo = getWeixinUserInfo($openid, $token);
		$pInfo['nickname'] = $userinfo['nickname'];
		$pInfo['headPic'] = $userinfo['headimgurl'];
		$pInfo['openid'] = $openid;
		return $pInfo;			
	}

	public function addAdvice(){
		$content = I('get.content');

		$pInfo = $this->getInfo();
		$data['openid']=$pInfo['openid'];
		$data['mobile'] = $pInfo['mobile'];
		$data['nickname'] = $pInfo['nickname'];
		$data['cTime'] = date('Y-m-d');
		$data['contents']=$content;

		$advice=M('advice')->add($data);
		if($advice){
			$result = "您的反馈 '".$content."' 已成功提交，谢谢您的宝贵意见，么么哒~~";
		}else{
			$result = "不好意思，您的反馈提交失败，请稍后重试哦~~";
		}
		$this->ajaxReturn($result,'JSON');
	}
	

	public function edit(){
		$item = I('get.item');
		switch ($item) {
			case '0':
				$itemName = "姓名";
				break;
			case '1':
				$itemName = "学号";
			default:
				# code...
				break;
		}
		$this->item = $item;
		$this->itemName = $itemName;
		$this->display();
	}

	public  function update(){
		$info = I('get.info');
		$item = I('get.item');
		switch ($item) {
			case '0':
				$data['truename'] = $info;
				break;
			case '1':
				$data['school_number'] = $info;
			default:
				# code...
				break;
		}
		$openId = get_openid();
		$oMap['openid']=$openId;
		$update=M('Member')->where($oMap)->save($data);
		if($update){
			$result = 1;;
		}else{
			$result = 0;
		}
		$this->ajaxReturn($result,'JSON');

		
	}
}

?>