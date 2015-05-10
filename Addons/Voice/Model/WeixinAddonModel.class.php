<?php
        	
namespace Addons\Voice\Model;
use Home\Model\WeixinModel;
        	
/**
 * Voice的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($data, $keywordArr = array()) {
		$config = getAddonConfig ( 'Voice' ); // 获取后台插件的配置参数	
		$articles = array(
				[0] => array(
						"Title" => "adfasdf",
						"Description" => "asdfasdf",
						"PicUrl" => "",
						"Url" => ""
					)
			);
		
		$ret = $this->replyNews($articles);
		
		return $ret;
	}
	
	//处理语音。
	public function voice($data) {
		$articles = array(
				[0] => array(
						"Title" => "adfasdf",
						"Description" => "asdfasdf",
						"PicUrl" => "",
						"Url" => ""
					)
			);
		
		$ret = $this->replyNews($articles);
		return $ret;
	}	
}
        	