<?php
        	
namespace Addons\SchoolBus\Model;
use Home\Model\WeixinModel;
        	
/**
 * SchoolBus的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'SchoolBus' ); // 获取后台插件的配置参数	
		//dump($config);
		$msgs = "校车预定统计数据\n";
		$Schoolbus = M('Schoolbus');
		$SchoolbusOrder = M('SchoolbusOrder');
		$SchoolbusStat = M('SchoolbusStat');
		$starttime = date('Y-m-d G:i:s',strtotime('-1 day'));
		$endtime = date('Y-m-d G:i:s',strtotime('+1 day'));
		$sMap['schoolbus_date'] = array('between',array($starttime,$endtime));
		$schoolbusList = $Schoolbus->where($sMap)->select();
		
		foreach($schoolbusList as $aBus){
			$bMap['schoolbus_id'] = $aBus['schoolbus_id'];
			$observNum = $SchoolbusStat->field('count(*)')->where($bMap)->find();
			$observNum = $observNum['count(*)']+100;
			$bMap['order_status']=0;
			$orderedNum = $SchoolbusOrder->field('count(distinct openid)')->where($bMap)->find();
			$orderedNum=$orderedNum['count(distinct openid)'];
			$bMap['order_status']=1;
			$canceleddNum = $SchoolbusOrder->field('count(distinct openid)')->where($bMap)->find();
			$canceleddNum=$canceleddNum['count(distinct openid)'];
			$bMap['order_status']=2;
			$loadedNum = $SchoolbusOrder->field('count(distinct openid)')->where($bMap)->find();
			$loadedNum=$loadedNum['count(distinct openid)'];			
			$msgs= $msgs."\n发车地点: ".$aBus['schoolbus_from']."校区";
			$msgs= $msgs."\n发车时间: ".$aBus['schoolbus_date'];
			$msgs= $msgs."\n预约人数: ".($orderedNum+$loadedNum);
			$msgs= $msgs."\n退订人数: ".$canceleddNum;
			$msgs= $msgs."\n浏览人数: ".$observNum;
			$msgs= $msgs."\n上车人数: ".$loadedNum;
		}
		$res = $this->replyText($msgs);
		return $res;
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
	public function scan($data) {
	    
		$cmapm['openid'] = get_openid();
		//还要验证admin
		$isBudAdmin = M('SchoolbusAdmin')->where($cmapm)->find();
		if(!empty($isBudAdmin)){
			$mapm['order_id'] = $data['EventKey']%10000;
			$mapm['order_status'] = 0;
			
			$schoolbusO = M('SchoolbusOrder');
			$ordered = $schoolbusO->where($mapm)->find();
			if(!empty($ordered) ){//查询该订单是否合法
				//修改状态
				$newStatu['order_status']=2;
				$schoolbusO->where($mapm)->save($newStatu);
				
				$touser = $ordered['openid'];
				$token = get_token();
				$content = "您已成功验票上车，您的订单号为 ".$ordered['order_id'];
				$msgtype = "text";
				customSend($touser,$token,$content,$msgtype);
				
				$ret = $this->replyText($mapm['order_id']." 号订单验证上车成功");
			}else{
				$ret = $this->replyText($mapm['order_id']." 验证出错！！");
			}
		}else{
			$ret = $this->replyText("微生活为您服务");
		}
	
		
		return $ret;
		
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
        	