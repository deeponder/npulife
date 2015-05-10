<?php
namespace Home\Controller;
use Think\Controller;
class ShakeController extends Controller {

	public function shake(){
		//获取用户openid
		$openid = get_openid();	
		
		//读取该用户身份证
		if($openid!=-1){
			$aamap['openid']=$openid;
			$sfz = M ( 'Member' )->where ( $aamap )->getField('shenfengzheng');
		}
		//下面$sfz赋值只是测试用，连接服务器后用上面的注释代码
		//$sfz = "610424199505057625";
		$q2w = substr($sfz,0,2);        
		$laoxiang = array();
		$Member = M("Member"); 
		$userlist = $Member->select();
		
		foreach($userlist as $user)
		{
			
			if(strlen($user['shenfengzheng'])>10)
			{
				
				$usersfz = $user['shenfengzheng'];
				$userq2w = substr($usersfz,0,2);
				
				if($q2w==$userq2w)
				{
					array_push($laoxiang,$user);
				}
			}
		}
		//echo count($laoxiang );
		
		//得到数组个数
		$num = count($laoxiang);
		//随机取数组值
		$zhi = rand(0,$num-1);
		
		//获取用户摇出用户的头像
		$token = get_token();
		$openid = $laoxiang[$zhi]['openid'];
		$userinfo = getWeixinUserInfo($openid, $token);
		$headPic = $userinfo['headimgurl'];
		$laoxiang[$zhi]['nickname'] = $userinfo['nickname'];
		
		//得到相应的记录
		$this->assign('content',$laoxiang[$zhi]);
		$this->assign('headPic',$headPic);
		$this->display();
	}
}
