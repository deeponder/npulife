<?php

namespace Home\Controller;

/**
 * 私人定制
 * 主要获取和反馈微信平台的数据
 */
class MyCenterController extends HomeController {
    
	public function index(){
		$openId = get_openid();
		
		if($openId != -1){
			
		}

		$this->display();
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	//首次提交个人信息
	public function submit(){
		$openid = get_openid();
		if($openId != -1){
			//检查是否已经认证，如果认证过，就不必再进入。
			
			//如果没有认证，则进入该页。
			$this->openid = $openid;
			$this->display();
		}
	}
	//ajax首次提交个人信息
	public function ajaxSubmit() {		
		
		//先拿身份证和真实姓名核对一下。
		$pMap['name'] = I('post.truename');
		$pMap['ID_card'] = I('post.phone');
		$PersonalInfo = M('PersonalInfo');
		$person = $PersonalInfo->where($pMap)->find();
		
		if($person)
		{
			//如果正确就把信息更新。
			$uMap['openid'] = I('post.openid');
			$Member = M('Member');
			$user = $Member->where($pMap)->find();
			$user['truename'] = I('post.truename');
			$user['mobile'] = I('post.phone');
			$user['qq'] = I('post.qq');		
			$Member->save($user);
			
			$result['status'] = 1;
		}
		else
		{
			$result['status'] = 0;
		}
		
		$this->ajaxReturn($result,"JSON");
	}
	//补全个人信息
	public function full(){
		
		$this->display();
	}
	//ajax补全个人信息
	public function ajaxFull() {		
		
		$this->ajaxReturn($result,"JSON");
	}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
}

?>