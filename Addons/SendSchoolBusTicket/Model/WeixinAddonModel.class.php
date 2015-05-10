<?php
        	
namespace Addons\SendSchoolBusTicket\Model;
use Home\Model\WeixinModel;
        	
/**
 * SendSchoolBusTicket的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'SendSchoolBusTicket' ); // 获取后台插件的配置参数	

		//找最近的校车,获得schoolbusid
		$Map['openid'] = get_openid();
		$SchoolbusAdmin = M('SchoolbusAdmin');
		$isAdmin = $SchoolbusAdmin->where($Map)->find();
		if(!empty($isAdmin))//说明是管理人员
		{		
		    $starttime = date('Y-m-d G:i:s',strtotime('now'));
			$endtime = date('Y-m-d G:i:s',strtotime('+20 minute'));
			$Schoolbus = M('Schoolbus');
			$sMap['schoolbus_date'] = array('between',array($starttime,$endtime));
			$schoolbusid = $Schoolbus->field('schoolbus_id')->where($sMap)->find();//查询有无校车,这里有也只能有一辆
			if(!empty($schoolbusid))//20分钟有校车
			{
			    $res = $this->replyText("OK ".$schoolbusid['schoolbus_id']);
			    $this->sendQcodeToUser($schoolbusid['schoolbus_id']);	
				
			}
			else
			{
			    $ret = $this->replyText("20分钟内无校车！");
			}
		}
		return $res; 
	} 
	/*function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'SendSchoolBusTicket' ); // 获取后台插件的配置参数	

		$mmp['order_status']=3;
		$users = M("SchoolbusOrder")->where($mmp)->select();
		
	   $msg="您好，您通过微生活预定了6月28日16点10分从友谊校区发往长安校区的校车，但是没有通过扫描电子票检票上车。根据微生活校车预定规则，您的校车预定诚信值已被扣除，您将不能继续使用校车预定功能。\n如您当天已乘坐校车，或因特殊原因未乘坐校车，请直接发送原因至本公众账号申诉，申诉成功即可恢复诚信记录，申诉截止2014年06月30日21:00分。";
	 
		foreach($users as $touser){
			customSend($touser['openid'], "535ca7e3cde42", $msg, "text");
		}
		
	}*/
	/*发车前提醒上车，发放乘车二维码，这个功能写在插件SchoolBus里*/
	public function sendQcodeToUser($schoolbusid)
	{
		//根据schoolbusid找出这一车次的订单列表。
		$SchoolbusOrder = M('SchoolbusOrder');
		$oMap['schoolbus_id'] = $schoolbusid;
		$oMap['order_status'] = 0;
		$orderlist = $SchoolbusOrder->where($oMap)->select();
		
		//这段只是根据order得到二维码图片
		$accessToken = getAccessToken();
		
		foreach($orderlist as $order)
		{
			$sceneid = $order['order_id']%100000+100000;//把100001-200000定为校车场景号段
			$qcode = '{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$sceneid.'}}}';
			$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$accessToken;
			$result = $this->https_post($url,$qcode);
			$jsoninfo = json_decode($result,true);
			$ticket = $jsoninfo["ticket"];
			$qcodePicUrl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket);
			/*
			//再处理一下二维码，变成720*400的一张图。
			$imageInfo = $this->downloadImageFromWeixin($url);
			$filename = "qcodeImage/".$order['order_id'].".jpg";
			$local_file = fopen($filename, 'w');
			if($local_file)
			{
				if(fwrite($local_file, $imageInfo['body']))
				{
					fclose($local_file);
				}
			}
			
			$backImg = ImageCreate(720,400);//创建底图
			$qcodeImg = imagecreatefromjpeg($filename);
			$newFileName ="/public/qcodeImage/".$order['order_id'].".jpg";
			$local_file = fopen($newFileName, 'w');
			if($local_file)
			{
				if(fwrite($local_file, $qcodeImg))
				{
					fclose($local_file);
				}
			}
			$qcodePicUrl ="http://wechat.npulife.com".$newFileName;
			*/
			
			
			//这段是发送二维码到用户。这里有个问题，二维码半小时失效，能不能扫描完？
			$articles[0]['Title'] = "提醒上车！订单号：".$order['order_id'];
			$articles[0]['Description'] = "本电子票将在半小时内失效，乘车时，请点击电子票，打开二维码，由校车工作人员扫描，作为上车凭证";
			$articles[0]['PicUrl'] = $qcodePicUrl;
			$articles[0]['Url'] = $qcodePicUrl;
			//"http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/school_bus/_controller/school_bus/_action/index.html/OrderContent/order_id/".$order['order_id'];		
			$touser = $order['openid'];//"o8TQCj8ch3DuyerWWZjI8zsONdEA";
			$token = get_token();
			$content = $articles;
			$msgtype = "news";
			customSend($touser,$token,$content,$msgtype);
		}		
	}	
	private function https_post($url,$data=null)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if(!empty($data))
		{
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	private function downloadImageFromWeixin($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$package = curl_exec($ch);
		$httpinfo = curl_getinfo($ch);
		curl_close($ch);
		return array_merge(array('body'=>$package),array('header'=>$httpinfo));
	}
}
        	