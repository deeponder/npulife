<?php
        	
namespace Addons\id\Model;
use Home\Model\WeixinModel;
        	
/**
 * id的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'id' ); // 获取后台插件的配置参数	
		//dump($config);
		$uData['openid'] = get_openid();
		$u = M('Member')->where($uData)->find();
		$this->replyText ( "您的ID是：".$u['uid']."\n您已获得积分 ".$u['score']);
	} 

	// 关注公众号事件
	public function subscribe() {
		return true;
	}
	
	// 取消关注公众号事件
	public function unsubscribe() {
		return true;
	}
	
	// 扫描带参数二维码事件
	public function scan() {
		return true;
	}
	
	// 上报地理位置事件
	public function location() {
		return true;
	}
	
	// 自定义菜单事件
	public function click() {
		return true;
	}	
}
        	