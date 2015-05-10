<?php
        	
namespace Addons\ykbcj\Model;
use Home\Model\WeixinModel;
        	
/**
 * ykbcj的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'ykbcj' ); // 获取后台插件的配置参数	
		//dump($config);
		$openid = $dataArr["FromUserName"];
		
		//看是不是认证，不是的话跳到认证页面；

		$Member = M('Member');
		$mData['openid'] = $openid;
		$theUser = $Member->where($mData)->find();
		
		if(!empty($theUser['shenfengzheng']))
		{
			//认证用户返回成绩；
			$yData["snum"] = $theUser['school_number'];
			$YKBCJ = M("ykbcj");
			$theYK = $YKBCJ->where($yData)->find();
			if($theYK)
			{
				$content = "您的成绩单如下：\n"."尊姓大名：".$theYK['name']."\n"."中文面试：".$theYK['ch']."\n"."英文面试：".$theYK['en']."\n"."棒棒哒！";
				$this->replyText($content);
			}
			else
			{
				$content = "没找到。小孩别瞎凑热闹~";
				$this->replyText($content);
			}
		}
		else
		{
			$this->replyText("我就知道！亲，您还没有认证捏，回复 认证 填写信息成为认证用户，之后再查询。");
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
        	