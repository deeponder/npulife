<?php
        	
namespace Addons\Choujiang\Model;
use Home\Model\WeixinModel;
        	
/**
 * Choujiang的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'Choujiang' ); // 获取后台插件的配置参数	
		//dump($config);
		$uMap['eventkey'] = 6;
		$uMap['state'] = 0;
		$userlist = M('ErweimaMember')->where($uMap)->select();
		shuffle($userlist);
		$zhongjiangNum = 8;
		
		$mMap['theme'] = "lexiang";
		$misc = M('Misc')->where($mMap)->find(); 
		$level= $misc['state'];
		
		$choujiangshijian = date('Y-m-d G:i:s');
		
		/*
		if($level%3==0)
		{
			for($i=0;$i<8;$i++)
			{
				$articles [0] = array (
						'Title' => "恭喜您！中奖啦！瓜大生活圈战袍~",
						'Description' => "抽奖号：".$userlist[$i]['id']."，抽奖时间：".$choujiangshijian.",中奖时间：".date('Y-m-d G:i:s'),
						'PicUrl' => "http://wechat.npulife.com/Public/Home/images/lipin.jpg" ,
						'Url' => ""
				);
				$touser = $userlist[$i]['openid'];
				$token = get_token();
				$content = $articles;
				$msgtype = "news";
				customSend($touser,$token,$content,$msgtype);
				
				$userlist[$i]['state'] = 3;
				M('ErweimaMember')->save($userlist[$i]);
				
				$zhongjiangList.=$userlist[$i]['id']."\n";
			}
		}
		if($level%3==1)
		{
			for($i=0;$i<5;$i++)
			{
				$articles [0] = array (
						'Title' => "恭喜您！中奖啦！8GU盘耶~",
						'Description' => "抽奖号：".$userlist[$i]['id']."，抽奖时间：".$choujiangshijian.",中奖时间：".date('Y-m-d G:i:s'),
						'PicUrl' => "http://wechat.npulife.com/Public/Home/images/lipin.jpg" ,
						'Url' => ""
				);
				$touser = $userlist[$i]['openid'];
				$token = get_token();
				$content = $articles;
				$msgtype = "news";
				customSend($touser,$token,$content,$msgtype);
				
				$userlist[$i]['state'] = 2;
				M('ErweimaMember')->save($userlist[$i]);
				
				$zhongjiangList.=$userlist[$i]['id']."\n";
			}
		}
		if($level%3==2)
		{
			for($i=0;$i<3;$i++)
			{
				$articles [0] = array (
						'Title' => "恭喜您！中奖啦！16GU盘耶~",
						'Description' => "抽奖号：".$userlist[$i]['id']."，抽奖时间：".$choujiangshijian.",中奖时间：".date('Y-m-d G:i:s'),
						'PicUrl' => "http://wechat.npulife.com/Public/Home/images/lipin.jpg" ,
						'Url' => ""
				);
				$touser = $userlist[$i]['openid'];
				$token = get_token();
				$content = $articles;
				$msgtype = "news";
				customSend($touser,$token,$content,$msgtype);
				
				$userlist[$i]['state'] = 1;
				M('ErweimaMember')->save($userlist[$i]);
				
				$zhongjiangList.=$userlist[$i]['id']."\n";
			}
		}
		
		if($level%3==0)
		{
			for($i=0;$i<5;$i++)
			{
				$articles [0] = array (
						'Title' => "恭喜您！中奖啦！瓜大生活圈手机~",
						'Description' => "抽奖号：".$userlist[$i]['id']."，抽奖时间：".$choujiangshijian.",中奖时间：".date('Y-m-d G:i:s'),
						'PicUrl' => "http://wechat.npulife.com/Public/Home/images/lipin.jpg" ,
						'Url' => ""
				);
				$touser = $userlist[$i]['openid'];
				$token = get_token();
				$content = $articles;
				$msgtype = "news";
				customSend($touser,$token,$content,$msgtype);
				
				$userlist[$i]['state'] = 3;
				M('ErweimaMember')->save($userlist[$i]);
				
				$zhongjiangList.=$userlist[$i]['id']."\n";
			}
		}
		*/
		$misc['state']++;
		M('Misc')->save($misc);
		
		$ret = $this->replyText ($zhongjiangList);
		return $ret;
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
        	