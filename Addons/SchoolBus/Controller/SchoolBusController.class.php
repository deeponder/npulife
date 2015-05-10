<?php

namespace Addons\SchoolBus\Controller;
use Home\Controller\AddonsController;

class SchoolBusController extends AddonsController{
    public function index(){
	
		//是否是认证用户，如果不是跳转到认证页面。
		if(-1 != $this->getSchoolNumber() ){
			$this->assign('title',"校车预订");
			if(true==$this->isChengxin()){
				//显示可预订校车（这里需要加一个策略）
				$schoolbusList = $this->findUsableBusList();
				$this->assign("schoolbusList",$schoolbusList);
				
				//显示可用的订单
				$orderList = $this->findUsableOrderList();
				$this->assign("orderList",$orderList);
				//加一个历史订单
				$historyOrderList = $this->findHistoryOL();
				$this->assign("histroryOL",$historyOrderList);
				//统计一下有多少人访问了这个订阅
				$schoolbusStat = M("SchoolbusStat");
				foreach($schoolbusList as $aBus){
				   $openId = get_openid();
				   $busID = $aBus['schoolbus_id'];
				   $schoolbusStat->query("insert ignore nl_schoolbus_stat values($busID,$openId)");
				   
				}
				foreach($orderList as $aBus){
				   $openId = get_openid();
				   $busID = $aBus['schoolbus_id'];
				   $schoolbusStat->query("insert ignore nl_schoolbus_stat values($busID,\"$openId\")");
				   
				}
			}
			
			
			$this->display();
		}else{
			redirect("http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/binding/_controller/binding/_action/index.html");
		}
    }
	//返回可预订的校车列表。发车前24小时。
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
		$Schoolbus = M('Schoolbus');
		for($i=0; $i<count($orderList);$i++){//the respective bus info should be shown
			$cMap['schoolbus_id']=$orderList[$i]['schoolbus_id'];
			$fromPlace = $Schoolbus->field('schoolbus_from,schoolbus_date')->where($cMap)->find();
			$orderList[$i]['schoolbus_from'] = $fromPlace['schoolbus_from'];
			$orderList[$i]['schoolbus_date'] = $fromPlace['schoolbus_date'];
		}
		return $orderList;
	}
	private function findHistoryOL(){
		$oMap['openid'] = get_openid();
		$SchoolbusOrder = M('SchoolbusOrder');
		$histOrder = $SchoolbusOrder->where($oMap)->select();	
		return $histOrder;
	}
	
	//订单详情
	public function OrderContent()
	{
		$order_id = $_REQUEST ['order_id'] ;
		$SchoolbusOrder = M('SchoolbusOrder');
		$this->assign('title',"校车预订");
		$oMap['order_id'] = $order_id;
		$theOrder = $SchoolbusOrder->where($oMap)->find();
		$this->assign("theOrder",$theOrder);
		$this->assign('orderID',$order_id);
		$Schoolbus = M('Schoolbus');
		$sMap['schoolbus_id'] = $theOrder['schoolbus_id'];
		$theBus = $Schoolbus->where($sMap)->find();
		$this->assign("theBus",$theBus);
		$theBusRestNum = $this->countBusRestNum($sMap['schoolbus_id']);
		$this->assign("theBusRestNum",$theBusRestNum);
		//先判断订单状态，从图文里还可以点进来。
		switch($theOrder['order_status'])
		{
			case 0:
				$this->assign("theOrderStatus","<font color=green>预订成功</font>");
				$this->display();
				break;
			case 1:
				redirect("http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/school_bus/_controller/school_bus/_action/index.html");
				break;
			case 2:
				redirect("http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/school_bus/_controller/school_bus/_action/index.html");
				break;
			case 3:
				redirect("http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/school_bus/_controller/school_bus/_action/index.html");
				break;
		}
	}
	
	//校车详情
	public function SchoolBusContent()
	{
		$schoolbus_id=$_REQUEST ['schoolbus_id'] ;
		$Schoolbus = M('Schoolbus');
		$sMap['schoolbus_id'] = $schoolbus_id;
		$theBus = $Schoolbus->where($sMap)->find();
		$this->assign('title',"校车预订");
		$this->assign("theBus",$theBus);
		
		//计算剩余座位数。
		$theBusRestNum = $this->countBusRestNum($schoolbus_id);
		
		$this->assign("theBusRestNum",$theBusRestNum);
		
		if($theBusRestNum>0)
		{
			$buttonHTML = '<form method="post" action="/index.php?s=/home/addons/execute/_addons/school_bus/_controller/school_bus/_action/OrderTheBus/schoolbus_id/'.$schoolbus_id.'.html" data-ajax="false">
    <input type="submit" data-inline="true" value="我要预订">
</form>';
		}
		else
		{
			$reURL = U("index");
			$buttonHTML = "<a href=$reURL><button data-role=\"button\" >返回</button></a>";
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
	/*预订校车*/
	public function OrderTheBus()
	{
		
	//判断没有预订过临近的车次。
		$schoolbus_id=$_REQUEST ['schoolbus_id'] ;
		$openid = get_openid();
		if($openid!=-1 && -1 != $this->getSchoolNumber() && true==$this->isChengxin())
		{	//已经确保是认证用户了。
			$SchoolbusOrder = M('SchoolbusOrder');
			$sMap['openid'] = $openid;
			$memberInfo = M('Member')->field('uid,school_number')->where($sMap)->find();
			$sMap['schoolbus_id'] = $schoolbus_id;
			$tMap = $sMap;
			$tMap['order_status']=0;
			//判断之前没有预订过这一车次。
			$hasOdered = $SchoolbusOrder->where($tMap)->select();
			  if(count($hasOdered) < 1){
				$sMap['uid']=$memberInfo['uid'];
				$sMap['school_no']=$memberInfo['school_number'];
				$sMap['order_createdate'] = date('Y-m-d G:i:s');
				$orderid = $SchoolbusOrder->add($sMap);			
			
			//向用户推送一条消息。
				$this->sendOrderToUser($orderid,$openid);
			}
			
		}
		
		redirect("http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/school_bus/_controller/school_bus/_action/index.html");
	}
	private function sendOrderToUser($orderid,$openid)
	{
		$articles[0]['Title'] = "恭喜您，成功预订校车！订单号：".$orderid;
		$articles[0]['Description'] = "我们会在发车前半小时给您发送一个电子车票，电子车票上的二维码将是您上车的凭据，请执电子车票和一卡通上车";
		$articles[0]['PicUrl'] = "";
		$articles[0]['Url'] = "http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/school_bus/_controller/school_bus/_action/index.html";
			
		$touser = $openid;
		$token = get_token();
		$content = $articles;
		$msgtype = "news";
		customSend($touser,$token,$content,$msgtype);
	}
	
	/*取消预订*/
	public function QuitTheBus()
	{
	    $order_id = $_REQUEST ['order_id'] ;
		$SchoolbusOrder = M('SchoolbusOrder');
		$oMap['order_id'] = $order_id;
		$theOrder = $SchoolbusOrder->where($oMap)->find();
		$sMap['schoolbus_id']=$theOrder['schoolbus_id'];
		$starttime = date('Y-m-d G:i:s',strtotime('+60 minute'));
		$endtime = date('Y-m-d G:i:s',strtotime('+1 day'));
		$Schoolbus = M('Schoolbus');
		$sMap['schoolbus_date'] = array('between',array($starttime,$endtime));
		$schoolbusList = $Schoolbus->where($sMap)->find();	
		if(!empty($schoolbusList)){
			$theOrder['order_status'] = 1;
			$theOrder['order_quitdate'] = date('Y-m-d G:i:s');   
			
			$SchoolbusOrder->save($theOrder);
		}
		
		
		redirect("http://wechat.npulife.com/index.php?s=/home/addons/execute/_addons/school_bus/_controller/school_bus/_action/index.html");
	}
		
	//判断是否为认证用户，返回学号或-1
	protected function getSchoolNumber(){
		$users = M('Member');
		$openId = get_openid();
		if($openId != -1){
			$school_number = $users->field('school_number')->where("openid ='$openId'" )->select();
			if(!empty($school_number[0]['school_number']) &&  $school_number[0]['school_number']!=''){
				return  $school_number[0]['school_number'];
			}
		}
		return -1;
	}
	
	protected function isChengxin(){
		$mmpap['openid'] = get_openid();
		
		$chengxin = M('SchoolbusOrder')->field('min(user_chengxin)')->where($mmpap)->find();
		$chengxin==$chengxin['min(user_chengxin)'];
		if($chengxin >=0){
			return true;
		}else
		 return false;
	}
}
