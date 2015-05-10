<?php
namespace Home\Controller;
use Think\Controller;
class PersonController extends Controller {
    
	public function index(){
	 $openId = get_openid();
		$oMap['openid']=$openId;
		if($openId!=-1){
			$pInfo=M('Member')->where($oMap)->find();
			$last_login_time = M('Member')->where($oMap)->getField('last_login_time', true);
			$inttime=(int)$last_login_time[0];
			$deadtime = time()-$inttime;
			// dump( $last_login_time);
			if($deadtime<17280){
				$lionAddr = "11"; 
			}elseif(($deadtime>=17280)&&($deadtime<34560)){
				$lionAddr = "3"; 
			}elseif(($deadtime>=34560)&&($deadtime<51840)){
				$lionAddr = "2"; 
			}elseif(($deadtime>=51840)&&($deadtime<69120)){
				$lionAddr = "10"; 
			}elseif(($deadtime>=69120)&&($deadtime<86400)){
				$lionAddr = "4"; 
			}elseif(($deadtime>=86400)&&($deadtime<172800)){
				$lionAddr = "9"; 
			}elseif(($deadtime>=17280)&&($deadtime<345600)){
				$lionAddr = "5"; 
			}else{
				$lionAddr = "8"; 
			}
				
			//获得头像
			$token = get_token();
			$openid = get_openid();		
			$userinfo = getWeixinUserInfo($openid, $token);

			$this->nickname = $userinfo['nickname'];
			$headPic= $userinfo['headimgurl'];
			$this->assign('headPic',$headPic);
		
			// $nickname=$userinfor['nickname'];
			$this->assign('user',$pInfo);
			// dump($lionAddr);
			$this->assign('lionAddr',$lionAddr);
			//获取活动内容
			$activity=M('activity');
			$list=$activity->limit(5)->select();
			// dump($list);
			
			// dump($headPic);
			$this->list=$list;	
			
			//获取信息提示
			$note=M('notes')->limit(5)->select();
			$this->note=$note;
			
			$this->display();
			
			} 
			
    }
	
	public function updatehandle(){
			$openId = get_openid();
		$oMap['openid']=$openId;
		
			$mem=D('Member');
			if(!$mem->create()){
				$this->error($mem->getError());
			}else{
			 $mem->where($oMap)->save();
			}
			redirect(U('index'));

		}
	
	public function addadvice(){
	$openid = get_openid();
	$advice=M('advice');
	$content=I('post.advice');
	// dump($content);
	$data['openid']=$openid;
	$data['contents']=$content;
	$ad=$advice->add($data);
	if($ad){
	$this->success('操作成功！');
	}else{
			$this->error('操作失败！');
		}
	
redirect(U('index'));
	}
	public function updateInfo(){
	$this->display();
	}
	
	public function test(){
	dump('dfd');
	}

	
}