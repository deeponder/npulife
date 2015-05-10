<?php
namespace Home\Controller;
use Think\Controller;
class SchoolController extends Controller {
	// public function test(){
	// 		$token = get_token();
	// 			$openid = get_openid();		
	// 			$userinfo = getWeixinUserInfo($openid, $token);
	// 			$headPic = $userinfo['headimgurl'];
	// 			dump($userinfo);
	// }


	public function test(){
		// $a = getAccessToken();
		$token = get_token();
			$openid = get_openid();		
	 			$userinfo = getWeixinUserInfo($openid, $token);
		dump($userinfo);
	}
    
	public function index(){
		//获取用户的订阅信息
		// $openid = get_openid();
		$check = M('usercheck','nl_','DB_CONFIG1');
		$omap['openid'] = 'a';
		$all = $check->group('cateid')->select();
		// dump($all);
		$checklist = $check->where($omap)->select();
		// dump($checklist);
		

		$this->checklist=$checklist;
		// dump($checklist);
		// $uncheknum = count($all)-count($checklist);
		// dump($uncheknum);

			$i=0;
			for ($j=0; $j<count($all); $j++) { 
				for($k=0;$k<count($checklist);$k++){
					if($all[$j]['cateid']==$checklist[$k]['cateid']){
						$state = 1; break;
					}else{
						$state=0;
					}
				}

				if(!$state){
					$unchecklist[$i]['cateid']=$all[$j]['cateid'];
					$unchecklist[$i]['cate'] = $all[$j]['cate'];
					$i++;
				}
			}
			// dump($unchecklist);
		$this->unchecklist=$unchecklist;
			
		
		//默认显示推荐的内容
		$info = M('CustomReplyNews','nl_','DB_CONFIG1');
		$cmap['cate_id'] = 7;
		$list = $info->where($cmap)->order('cTime desc')->limit('5')->select();
			// dump($list);

		$this->list=$list;
		$this->display();
    }
	
//ajax 获取各个栏目的内容
	public function content(){
		$n = I('get.n');
		$info = M('CustomReplyNews','nl_','DB_CONFIG1');
		$cmap['cate_id'] = $n;
		$result = $info->where($cmap)->order('cTime desc')->limit('10')->select();
		$this->ajaxReturn($result,'JSON');
	}

	public function uncheck(){
		$check = M('usercheck','nl_','DB_CONFIG1');
		$uncheckid = I('get.cateid');
		$openid = get_openid();
		$omap['openid'] = $openid;
		$omap['cateid'] = $uncheckid;
		$check->where($omap)->delete();

	}

	// public function check(){
	// 	$check = M('usercheck','nl_','DB_CONFIG1');
	// 	$check['cateid'] = I('get.cateid');
	// 	$check['cate'] = I('get.cate');
	// 	$openid = get_openid();
	// 	$check['openid'] = $openid;
	// 	// $omap['cateid'] = $checkid;
	// 	$check->add($check);
		
	// 	// $this->ajaxReturn($check,'JSON');
	// }	
	

	
}