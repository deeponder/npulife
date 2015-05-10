<?php
        	
namespace Addons\UpdateData\Model;
use Home\Model\WeixinModel;
  	
/**
 * UpdateData的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'UpdateData' ); // 获取后台插件的配置参数	
		//dump($config);
		$key = $dataArr['Content'];
		//分析Key 		
		$openId = get_openid();
        //$res  = D('Weixin')->replyText($openId);
	    // return $res;
		//转到本插件，我们的目的是对获取App的数据
		//1.获取他们的数据，要求返回我们所需要的格式（即custom——reply的所有数据项都要有）
		//2.存储到我们的数据库即可
       
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
        	
        	   	
        	
        	