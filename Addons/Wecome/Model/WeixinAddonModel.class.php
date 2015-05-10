<?php

namespace Addons\Wecome\Model;

use Home\Model\WeixinModel;

/**
 * Vote模型
 */
class WeixinAddonModel extends WeixinModel {
	function reply($dataArr, $keywordArr = array()) {
		return true;
	}
	// 关注时的操作
	function subscribe($dataArr) {
		
		$config = getAddonConfig ( 'Wecome' ); // 获取后台插件的配置参数
		
		$openid = get_openid();
		$token = get_token();
		
		$userinfo = getWeixinUserInfo($openid,$token);
		
		$mData['openid'] = $openid;
		$theUser = M('Member')->where($mData)->find();
		
		if($theUser)
		{
			$uid = $theUser['uid'];
		}
		else
		{
			$uid = D('Member')->initWeixinUser($userinfo['nickname']);
		}
		
		switch ($config ['type']) {
			case '3' :
				$articles [0] = array (
						'Title' => $config ['title']." \n您的ID是：".$uid,
						'Description' => $config ['description'],
						'PicUrl' => $config ['pic_url'],
						'Url' => $config ['url'] 
				);
			
				$res = $this->replyNews ( $articles );
				break;
// 			case '2' :
// 				$media_id = 1;
// 				$res = $this->replyImage ( $media_id );
// 				break;
			default :
				$ret = $this->replyText ("欢迎来到瓜大生活圈，您的ID是：".$uid);
		}
		
		return $ret;
	}
}