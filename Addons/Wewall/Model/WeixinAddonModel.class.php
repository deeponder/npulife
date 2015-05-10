<?php
        	
namespace Addons\Wewall\Model;
use Home\Model\WeixinModel;
        	
/**
 * Wewall的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
	
	
		// $config = getAddonConfig ( 'Wewall' ); // 获取后台插件的配置参数	
		//dump($config);

		// preg_match('/\#微唱\#(.+)/i',$dataArr[Content], $matchs);
		
		$follows=M('follows');
		$vmap['openid']=$dataArr[FromUserName];
		$status=$follows->where($vmap)->getField('status',true);
		// $data['ctime']=$ctime;
		
		
		// $token = get_token();
		// $openid = get_openid();		
		// $userinfo = getWeixinUserInfo($openid, $token);
		// $headPic = $userinfo['headimgurl'];
		// $data['headimge']=$headPic;
		// $data['nickname']=$userinfo['nickname'];
		

		// $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$data['openid'];
		// $detail=file_get_contents($url);
		// $detail=json_decode($detail,true);
		// $data['nickname']=$detail['nickname'];
		// $data['headimge']=$detail['headimgurl'];
		// $follows->add($data);
		if(!$status){
		$this->replyText("很遗憾，没中奖~~");
		}else{
		$this->replyText("您中奖了~~~~！！请会后到前台领取");
		}
		
		

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
        	