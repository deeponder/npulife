<?php
namespace Home\Controller;
use Think\Controller;
class PersonController extends Controller {
    
	public function index(){
	 $openId = get_openid();
	$oMap['openid']=$openId;
	 $mem=M('member','nl_','DB_CONFIG1')->where($oMap)->find();
	// //获得头像
				// $token = get_token();
				// $openid = get_openid();		
				//$userinfo = getWeixinUserInfo($openid, $token);
				// $headPic = $userinfo['headimgurl'];
				
				//$this->assign('headPic',$headPic);
				
				$this->assign('mem',$mem);
				
		$this->display();
    }
	
	public function update(){
	$openId = get_openid();
	$oMap['openid']=$openId;
	$mem=M('Member','nl_','DB_CONFIG1')->where($oMap)->find();
	// $sn=I('get.student_ID');
	$name=I('get.name');
	$school=I('get.school');
	$qnum=I('get.qq_number');
	$data['qq']=$qnum;
	$data['school']=$school;
	$data['truename']=$name;
	$mem->save($data);
	$this->display();
	}
}