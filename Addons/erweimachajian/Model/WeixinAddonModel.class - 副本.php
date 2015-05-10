<?php
        	
namespace Addons\erweimachajian\Model;
use Home\Model\WeixinModel;
        	
/**
 * erweimachajian的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'erweimachajian' ); // 获取后台插件的配置参数	
		//dump($config);

	}

	// 取消关注公众号事件
	public function unsubscribe() {
		
		return true;


	}
	
	// 扫描带参数二维码事件
	public function scan($data){
		// $mapm['eventkey'] = $data['EventKey'];
		// $mapm['openid'] = $data['FromUserName'];//get_openid();
		// $m = M('ErweimaMember')->where($mapm)->find();

		
		//在二维码用户表里把扫描二维码的人记录下来。
		// if(($m['openid']!=$mapm['openid'])&&($m['eventkey']!=$mapm['eventkey']))
		// {
		// 	$mapu['openid'] = get_openid();
		// 	$theUser = M('Member')->where($mapu)->find();
		// 	$uid = $theUser['uid'];	
		
		// 	$mapm['createdate'] = date('Y-m-d G:i:s');
		// 	$mapm['state'] = 0;
		// 	$mapm['uid'] = $uid;
		// 	M('ErweimaMember')->add($mapm);
		// }
		//将参与跑操的同学存表
		// if(($m['openid']!=$mapm['openid'])&&($m['eventkey']!=$mapm['eventkey']))
		// {
			// $mapu['openid'] = get_openid();

			// $theUser = M('Member')->where($mapu)->find();
			// $uid = $theUser['uid'];	
		
			// $mapm['createdate'] = date('Y-m-d G:i:s');
			// $mapm['state'] = 0;
			// $mapm['uid'] = $uid;
			// M('ErweimaMember')->add($mapm);
		// }

			$qrmember = M('ErweimaMember');
			$runtable = M('Nightrun');
						$runinfo = M('runinfo');

			$omap['openid'] = $data['FromUserName'];
			$vmap['openid'] = $data['FromUserName'];
			$vmap['eventkey'] = 103;
		 	$mmap['openid'] = $data['FromUserName'];
		 	$mmap['eventkey'] = 102;
		 	$outtime = $qrmember->where($vmap)->getField('createdate',true);
		 	$intime = $qrmember->where($mmap)->getField('createdate',true);
		 	$onum = count($outtime);
		 	$inum = count($intime);
			$lastintime = $qrmember->where($mmap)->order('createdate DESC')->limit('1')->getField('createdate',true);
			$ltime = strtotime($lastintime[0]);

		 // if($data['EventKey']==101){
		 // 	$this->replyText("您上次跑操离开时未扫二维码，请重新扫描~~");
		 // }

//判断是否扫描了旧的二维码
			if($data['EventKey']==100||$data['EventKey']==101){
				$qrmember->where($omap)->delete();
				$runinfo->where($omap)->delete();
			}elseif ($data['EventKey']==102) {
				//进入时间
			// $intime = time();
						// $this->replyText("您上次跑操离开时未扫二维码，请重新扫描~~");
			if(($data['CreateTime']-$ltime)<43200&&$onum==$inum){
				$this->replyText("您今天已经参与过我们的夜跑活动，每天您只能参与一次哦~~");
			}elseif($onum==$inum){
				$data['openid'] = $data['FromUserName'];
				$data['createdate'] = date('Y-m-d G:i:s');
				$data['eventkey'] = $data['EventKey'];
				$qrmember->add($data);
				$this->replyText("欢迎参加西北工大微生活夜跑。1.如果您没有在【两小时以内】出口扫二维码，本次夜跑无效. 2.我们将不定期更换操场的二维码，一旦发现您扫描了我们的旧二维码，将认为您作弊并进行【积分、夜跑时长清零】");
			}elseif($onum==$inum-1){
			 // $qrmember->where($mmap)->order('createdate DESC')->limit('1')->delete();
			$this->replyText("亲，不要重复扫入口二维码哦~~");

			}else{
				$qrmember->where($vmap)->order('createdate DESC')->limit('1')->delete();
			}

			}elseif ($data['EventKey']==103) {
					if($onum==$inum){
							$this->replyText("请先扫入口二维码哦~~");

			}elseif(($data['CreateTime']-$ltime)>7200&&$onum==$inum-1){
				$qrmember->where($mmap)->order('createdate DESC')->limit('1')->delete();
				// $runtable->where($omap)->setField(array('credit','totallong'),array(0,0));
				// 				$runtable->where($omap)->setField('totallong',0);
				// $runtable->where($omap)->setField('credit',0);
				$this->replyText("亲，您的夜跑时间已经超过2小时，违反了我们的夜跑规定，本次夜跑无效哦");

			}elseif($onum==$inum-1) {
				
				$data1['openid'] = $data['FromUserName'];
				$data1['createdate'] = date('Y-m-d G:i:s');
				$data1['eventkey'] = $data['EventKey'];
				$qrmember->add($data1);

			$this->replyText("您已离开跑操地点，欢迎明天继续参与我们的活动~~");

			}elseif($onum==$inum+1){
				$qrmember->where($vmap)->order('createdate DESC')->limit('1')->delete();
			}else{
				$this->replyText("亲，不要重复扫出口二维码哦~~");
			}
			
			}



		//二维码抽奖，还没写完。
		if($data['EventKey']==6)
		{
			$mapc['openid'] = get_openid();
			$theUser = M('ErweimaMember')->where($mapc)->find();
			$id = $theUser['id'];
			$uid = $theUser['uid'];
			
			$articles [0] = array (
					'Title' => "",
					'Description' => "",
					'PicUrl' => "",
					'Url' => "" 
			);
		}
		
		//校园寻宝
		// if($data['EventKey']<17||$data['EventKey']>=7)
		// {
		// 	//得到用户的ID，记录下用户扫描
		// 	$mape['openid'] = get_openid();
			
		// 	$theUser = M('ErweimaMember')->where($mape)->find();
		// 	$id = $theUser['id'];
		// 	$uid = $theUser['uid'];
						
		// 	//从场景表里找出地点的ID，
		// 	$Erweima = M("Erweima");
		// 	$eMap['eventkey'] = $data['EventKey'];
		// 	$erweima = $Erweima->where($eMap)->find();
			
		// 	//再从地点表里找到地点的介绍。
		// 	$LbsChangyou = M("LbsChangyou");
		// 	$lmap['id'] = $erweima['articleid'];
		// 	$location = $LbsChangyou->where($lmap)->find();
			
		// 	//向用户发送一条图文，表示扫描成功。
		// 	$articles [0] = array (
		// 			'Title' => "成功解锁：".$location['name'],
		// 			'Description' => "到哪一步了？点击页面可查看寻宝游戏当前状态。",
		// 			'PicUrl' => "",
		// 			'Url' => "" 
		// 	);
			
		// }
		// $ret = $this->replyNews($articles);
		
		// return $ret;
	}
		
	// 自定义菜单事件
	public function click() {
		return true;
	}	
}