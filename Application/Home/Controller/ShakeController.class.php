<?php
namespace Home\Controller;
use Think\Controller;
class ShakeController extends Controller {

	public function shake(){
		//��ȡ�û�openid
		$openid = get_openid();	
		
		//��ȡ���û����֤
		if($openid!=-1){
			$aamap['openid']=$openid;
			$sfz = M ( 'Member' )->where ( $aamap )->getField('shenfengzheng');
		}
		//����$sfz��ֵֻ�ǲ����ã����ӷ��������������ע�ʹ���
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
		
		//�õ��������
		$num = count($laoxiang);
		//���ȡ����ֵ
		$zhi = rand(0,$num-1);
		
		//��ȡ�û�ҡ���û���ͷ��
		$token = get_token();
		$openid = $laoxiang[$zhi]['openid'];
		$userinfo = getWeixinUserInfo($openid, $token);
		$headPic = $userinfo['headimgurl'];
		$laoxiang[$zhi]['nickname'] = $userinfo['nickname'];
		
		//�õ���Ӧ�ļ�¼
		$this->assign('content',$laoxiang[$zhi]);
		$this->assign('headPic',$headPic);
		$this->display();
	}
}
