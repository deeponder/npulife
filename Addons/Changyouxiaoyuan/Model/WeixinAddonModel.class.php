<?php
        	
namespace Addons\Changyouxiaoyuan\Model;
use Home\Model\WeixinModel;
        	
/**
 * Changyouxiaoyuan的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'Changyouxiaoyuan' ); // 获取后台插件的配置参数	
		//dump($config);
	} 
	
	// 上报地理位置事件
	public function location($data) {
		// M('collector')->add($data['FromUserName']);
		$url = "http://content.npulife.com/Tool/index.php/Home/Location/isReply";
		$post = $data;		
		$content = npulife_http_request($url,$post);
		
		//触发抓取。
		//$this->doWeixinPachong();
		
		return $res;
	}
	
	private function doWeixinPachong() 
	{
		//先检查上次是什么时候爬的。如果在300秒内就不爬。
		$Misc = M('Misc','nl_','DB_CONFIG_1');
		$dMap['theme'] = "weixinpachong";
		$theMisc = $Misc->where($dMap)->find();
		$lastdate = $theMisc['lastdate'];
		$nowdate = time();
		
		$theMisc['lastdate'] = time();
		$Misc->where($theMisc)->save();
		
		if(($nowdate-$lastdate)>600)
		{
			$url = "http://content.npulife.com/Tool/index.php/Home/Pa/index";
			$content = npulife_http_request($url,$post);
		}
	}
	
	private function doGetNickName()
	{
		
	}
}