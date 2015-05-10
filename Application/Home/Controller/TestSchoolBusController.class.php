<?php
// 本类由系统自动生成，仅供测试用途
namespace Home\Controller;
use Think\Controller;
class TestSchoolBusController extends Controller {

    public function index(){
	
		//是否是认证用户，如果不是跳转到认证页面。
		if(-1 != $this->getSchoolNumber() ){
			$this->assign('title',"校车预定");
			
			//显示可预定校车（这里需要加一个策略）
			$schoolbusList = $this->findUsableBusList();
			$this->assign("schoolbusList",$schoolbusList);
			
			//显示可用的订单
			$orderList = $this->findUsableOrderList();
			$this->assign("orderList",$orderList);
			//加一个历史订单和所有可用校车信息？
			$this->display();
		}else{
			redirect("http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/binding/_controller/binding/_action/index.html");
		}
    }
	//返回可预定的校车列表。发车前24小时。
	public function findUsableBusList()
	{	
		$starttime = date('Y-m-d G:i:s',strtotime('+30 minute'));
		$endtime = date('Y-m-d G:i:s',strtotime('+1 day'));
		
		$Schoolbus = M('Schoolbus');
		$sMap['schoolbus_date'] = array('between',array($starttime,$endtime));
		$schoolbusList = $Schoolbus->where($sMap)->select();		
		
		//还要清除掉不能订的车。订成功的不能再订
		$SchoolbusOrder = M('SchoolbusOrder');
		$oMap['order_status'] = 0;
		$oMap['openid'] = get_openid();
		$orderlist = $SchoolbusOrder->field('schoolbus_id')->where($oMap)->select();
		if($orderlist)
		{
			$schoolbusList = array_diff($schoolbusList,$orderlist);
		}	
		
		return $schoolbusList;
	}
	//返回可用的订单。
	public function findUsableOrderList()
	{
		$SchoolbusOrder = M('SchoolbusOrder');
		$oMap['openid'] = get_openid();
		$oMap['order_status'] = 0;
		$orderList = $SchoolbusOrder->where($oMap)->order('order_createdate desc')->select();
		return $orderList;
	}
	
	
	//订单详情
	public function OrderContent($order_id)
	{
		
		$SchoolbusOrder = M('SchoolbusOrder');
		$this->assign('title',"校车预定");
		$oMap['order_id'] = $order_id;
		$theOrder = $SchoolbusOrder->where($oMap)->find();
		$this->assign("theOrder",$theOrder);
		
		$Schoolbus = M('Schoolbus');
		$sMap['schoolbus_id'] = $theOrder['schoolbus_id'];
		$theBus = $Schoolbus->where($sMap)->find();
		$this->assign("theBus",$theBus);
		
		//先判断订单状态，从图文里还可以点进来。
		switch($theOrder['order_status'])
		{
			case 0:
				$this->assign("theOrderStatus","预定成功");
				$this->display();
				break;
			case 1:
				redirect("http://wechat.npulife.com/index.php/Home/SchoolBus");
				break;
			case 2:
				redirect("http://wechat.npulife.com/index.php/Home/SchoolBus");
				break;
			case 3:
				redirect("http://wechat.npulife.com/index.php/Home/SchoolBus");
				break;
		}
	}
	
	//校车详情
	public function SchoolBusContent($schoolbus_id)
	{
		$Schoolbus = M('Schoolbus');
		$sMap['schoolbus_id'] = $schoolbus_id;
		$theBus = $Schoolbus->where($sMap)->find();
		
		$this->assign("theBus",$theBus);
		
		//计算剩余座位数。
		$theBusRestNum = $this->countBusRestNum($schoolbus_id);
		$this->assign("theBusRestNum",$theBusRestNum);
		
		if(!$theBusRestNum)
		{
			$buttonHTML = '<form method="post" action="/index.php/Home/SchoolBus/OrderTheBus/schoolbus_id/'.$schoolbus_id.'" data-ajax="false">
    <input type="submit" data-inline="true" value="我要预定">
</form>';
		}
		else
		{
			$buttonHTML = '<form method="post" action="/index.php/Home/SchoolBus/OrderTheBus/schoolbus_id/'.$schoolbus_id.'" data-ajax="false">
    <input type="submit" disable="true" data-inline="true" value="我要预定">
</form>';
		}
		$this->assign("buttonHTML",$buttonHTML);
		
		$this->display();
	}
	private function countBusRestNum($schoolbus_id)
	{
		//总座位数减去订单数。
		$Schoolbus = M('Schoolbus');
		$SchoolbusOrder = M('SchoolbusOrder');
		$sMap['schoolbus_id'] = $schoolbus_id;
		$sMap['order_status'] = 0;
		$sb = $Schoolbus->where($sMap)->find();
		$orders = $SchoolbusOrder->where($sMap)->select();
		$num = $sb['schoolbus_num']-count($orders);
		
		return $num;
	}
	
	////////////////////////////////////////////////////////////////////////////
	/*预定校车*/
	public function OrderTheBus($schoolbus_id)
	{
		//已经确保是认证用户了。
		
		//判断之前没有预定过这一车次。

		//判断没有预定过临近的车次。
		
		$openid = get_openid();
		if($openid!=-1)
		{
			$SchoolbusOrder = M('SchoolbusOrder');
			$sMap['openid'] = $openid;
			$sMap['schoolbus_id'] = $schoolbus_id;
			$sMap['order_createdate'] = date('Y-m-d G:i:s');
			$orderid = $SchoolbusOrder->add($sMap);			
			
			//向用户推送一条消息。
			$this->sendOrderToUser($orderid,$openid);
		}
		
		redirect("http://wechat.npulife.com/index.php/Home/SchoolBus");
	}
	private function sendOrderToUser($orderid,$openid)
	{
		$articles[0]['Title'] = "预定成功！订单号：".$orderid;
		$articles[0]['Description'] = "我们会在发车前半小时给您推送一个上车二维码~";
		$articles[0]['PicUrl'] = "";
		$articles[0]['Url'] = "http://wechat.npulife.com/index.php/Home/SchoolBus/OrderContent/order_id/".$orderid;
			
		$touser = $openid;
		$token = get_token();
		$content = $articles;
		$msgtype = "news";
		customSend($touser,$token,$content,$msgtype);
	}
	
	/*取消预定*/
	public function QuitTheBus($order_id)
	{
		$SchoolbusOrder = M('SchoolbusOrder');
		$oMap['order_id'] = $order_id;
		$theOrder = $SchoolbusOrder->where($oMap)->find();
		$theOrder['order_status'] = 1;
		$theOrder['order_quitdate'] = date('Y-m-d G:i:s');
		$SchoolbusOrder->save($theOrder);
		
		redirect("http://wechat.npulife.com/index.php/Home/SchoolBus");
	}
	
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
			$sceneid = $order['order_id']%10000+10000;//把10001-20000定为校车场景号段
			$qcode = '{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$sceneid.'}}}';
			$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$accessToken;
			$result = $this->https_post($url,$qcode);
			$jsoninfo = json_decode($result,true);
			$ticket = $jsoninfo["ticket"];
			$qcodePicUrl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
			
			//再处理一下二维码，变成720*400的一张图。
			import("@.ORG.Image");
			//$ticketImg = new Image();
			
			
			//这段是发送二维码到用户。这里有个问题，二维码半小时失效，能不能扫描完？
			$articles[0]['Title'] = "提醒上车！订单号：".$order['order_id'];
			$articles[0]['Description'] = "本二维码将在半小时内失效，请校车工作人员扫描，作为上车凭证";
			$articles[0]['PicUrl'] = "http://wechat.npulife.com/index.php/Home/SchoolBus/generateTicket";//$qcodePicUrl;
			$articles[0]['Url'] = "http://wechat.npulife.com/index.php/Home/SchoolBus/OrderContent/order_id/".$order['order_id'];		
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
	private function generateTicket($qImg)
	{
		header("Content-Type: image/png");
		$data=12;
		$im = @imagecreate(100, 20)
			or die("Cannot Initialize new GD image stream");
		$background_color = imagecolorallocate($im, 0, 0, 0);
		$text_color = imagecolorallocate($im, 233, 14, 91);
		imagestring($im, 1, 5, 5,  $data, $text_color);
		imagepng($im);
		imagedestroy($im);
	}
	
	//判断是否为认证用户，返回学号或-1
	protected function getSchoolNumber(){
		$users = M('Member');
		$openId = get_openid();
		if($openId != -1){
			$school_number = $users->field('school_number')->where("openid ='$openId'" )->select();
			if(!empty($school_number[0][school_number])){
				return  $school_number[0][school_number];
			}
		}
		return -1;
	}
	
	//
}
