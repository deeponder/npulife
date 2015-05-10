<?php
        	
namespace Addons\Lvsetongdao\Model;
use Home\Model\WeixinModel;
        	
/**
 * Lvsetongdao的微信模型 绿色通道结果查询
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'Lvsetongdao' ); // 获取后台插件的配置参数	
		
		//先检查有没有实名认证
		$openid = $dataArr['FromUserName'];
		$Member = M ( 'Member');
		$uMap['openid'] = $openid;
		$user = $Member->where($uMap)->find();
		
		$isCertified = $this->isTruenameCertified($user);
		
		if(!$isCertified)
		{
			$articles[0]["Title"] = "欢迎查询绿色通道信息";
			$articles[0]["Description"] = "检测到您还没有实名认证，点击进入实名认证页面。认证通过后，重新输入关键词即可查询。";
			$articles[0]["PicUrl"] = "";
			$articles[0]["Url"] = "http://wechat.npulife.com/index.php/Home/MyCenter/submit";
			$this->replyNews($articles);
		}
		else
		{
			$Huanjiaoxuefei = M ( 'Huanjiaoxuefei','nl_','DB_CONFIG_NPULIFE_DATA');
			$hMap['truename'] = $user['truename'];
			$hMap['schoolno'] = $user['school_number'];
			$hUser = $Huanjiaoxuefei->where($hMap)->find();
			if($hUser)
			{
				$content = "姓名：".$hUser['truename']."\n"."学号：".$hUser['schoolno']."\n"."额度：".$hUser['xuefei']."\n"."类别：缓交";
				$this->replyText($content);
			}
			else
			{
				$this->replyText("抱歉，您没有在绿色通道的名单里。或者是您认证信息有误。");
			}
		}
		
	} 
	
	private function isTruenameCertified($user) {
		
		if(empty($user['truename'])||empty($user['shenfengzheng']))
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	// 自定义菜单事件
	public function click() {
	
		return true;
	}	
}
        	