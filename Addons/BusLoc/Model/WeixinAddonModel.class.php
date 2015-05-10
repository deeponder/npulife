<?php
        	
namespace Addons\BusLoc\Model;
use Home\Model\WeixinModel;
        	
/**
 * BusLoc的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'BusLoc' ); // 获取后台插件的配置参数	
		//dump($config);

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
		// 	//校车定位功能中的扫码上车---105：老->新；106：新到老
		// $colMod = M('collector','nl_','DB_CONFIG_NPULIFE_DATA');
		// $openid = $data['FromUserName'];
		// if($data['EventKey']==105){
		// 	$data['direction'] = 0;
		// 	$data['openid'] = $openid;
		// 	$re =$colMod->where("openid='%s'",$openid)->find();
		// 	if(!$re){
		// 		$colMod->add($data);
		// 	}else{
		// 		$re['direction'] = $data['direction'];
		// 		$colMod->save($data);
		// 	}
		// 	$busLoc[0] = array('Title' => '【点我更新状态】', 
		// 					  'Description'=> ' ', 
		// 					  'PicUrl'=> '', 
		// 					  'Url'=> 'http://www.wechat.npulife.com/tool/home/busloc/aboard');

		// 	$this->replyNews($busLoc);

		// }
		// if($data['EventKey']==106){
		// 	$data['direction'] = 1;
		// 	$data['openid'] = $openid;
		// 	$re =$colMod->where("openid='%s'",$openid)->find();
		// 	if(!$re){
		// 		$colMod->add($data);
		// 	}else{
		// 		$re['direction'] = $data['direction'];
		// 		$colMod->save($data);
		// 	}

		// 	$busLoc[0] = array('Title' => '【点我更新状态】', 
		// 					  'Description'=> ' ', 
		// 					  'PicUrl'=> '', 
		// 					  'Url'=> 'http://www.wechat.npulife.com/tool/home/busloc/aboard');

		// 	$this->replyNews($busLoc);

		// }
	}
	
	//上报地理位置事件
	public function location($data) {
		$colMod = M('collector','nl_','DB_CONFIG_NPULIFE_DATA');
		$busMod = M('schoolbus','nl_','DB_CONFIG_NPULIFE_DATA');
		$openid = $data['FromUserName'];
		$cmap['openid'] = $openid;
		$cmap['state'] = 1;
		$bmap['collector'] = $openid;
		$re1 = $colMod->where($cmap)->find();
		$re2 = $busMod->where($bmap)->find();
		//判断是否为正在坐车的信息员及是否开始追踪
		if(!$re2&&$re1){
			$code = $colMod->where($cmap)->getField('scode',true);
			$data1['scode'] = $code[0];
			$data1['collector'] = $openid;
			$data1['latitude'] = $data['Latitude'];
			$data1['longitude'] = $data['Longitude'];
			$data1['ctime'] = $data['CreateTime'];
			$busMod->add($data1);
		}
		elseif($re1&&$re2){
			$re2['ctime'] = $data['CreateTime'];
			$re2['latitude'] = $data['Latitude'];
			$re2['longitude'] = $data['Longitude'];
			$busMod->save($re2);	
		}
	}
	
	// 自定义菜单事件
	public function click() {
		return true;
	}	
}
        	