<?php

// 地图类
namespace Home\Controller;

class MapController extends HomeController {
    public function add() {
		$this->display();
	}
	
	public function addLocationPoint() {
		//http://wechat.npulife.com/index.php/Home/Map/addLocationPoint
		$name=I("post.name");
		$keyword = I('post.keyword');
		$type=I("post.type");
		$description=I("post.description");
		$latitude=I("post.latitude");
		$longitude("post.longitude");
		
		//echo 'name'.$name.'!!';
		
		$LocationPoint = M('LocationPoint','nl_','DB_CONFIG_NPULIFE_DATA');
		
		$LocationPoint->name=$name;
		$LocationPoint->keyword=$keyword;
		$LocationPoint->type=$type;
		$LocationPoint->latitude=$latitude;
		$LocationPoint->longitude=$longitude;
		$LocationPoint->description=$description;
		
		$result=$LocationPoint->add();
		
		$result['status'] = 1;
		$this->ajaxReturn($result,"JSON");
	}
	
	public function index(){
		
		//先找到用户的当前位置，确定地图中心。
		$PersonLocation = M ( 'PersonLocation','nl_','DB_CONFIG_NPULIFE_DATA');
		$pMap['openid'] = get_openid();
		$lastLocation = $PersonLocation->order('createdate desc')->where($pMap)->find();
				
		$this->openid = get_openid();
		$this->userX = $lastLocation['latitude'];
		$this->userY = $lastLocation['longitude'];
		
		$this->display();
	}
	
	
	public function doSearch(){
		//判断找人、找地方、事务
		
		$keyword = I('post.keyword');
		$nowLatitude = I('post.nowLatitude');
		$nowLongitude = I('post.nowLongitude');
		
		$type = $this->isKeyType($keyword);
		
		$LocationPoint = M('LocationPoint','nl_','DB_CONFIG_NPULIFE_DATA');
		
		switch($type)
		{
			case "address":
				$aMap['keyword'] = array("like", "%".$keyword."%");
				$locationList = $LocationPoint->where($aMap)->select();
				if($locationList)
				{
					$data['status'] = 1;//是否找到结果
					$data['type'] = "address";
					$data['list'] = $locationList;//返回地址数组信息
				}
				else
				{
					$data['status'] = 0;
				}
				break;
			case "to":
				$fromto = explode("到",$keyword,2);
				$from = $this->trimall($fromto[0]);
				$to = $this->trimall($fromto[1]);
				if($from)
				{
					
				}
				$tMap['name'] = $to;
				$locationList = $LocationPoint->where($tMap)->select();
				$data['status'] = 1;//是否找到结果
				$data['type'] = "to";
				$data['list'] = $locationList;//返回地址数组信息
				break;
			case "userid":
				//找到ID对应的Openid，然后从上报的地理位置中找到坐标。
				
				break;
			case "task":
				
				break;
		}
		
		//应该返回什么？
		
		
		$this->ajaxReturn($data,'JSON');		
	}

	//public function 
	
	private function isKeyType($keyword){
		//如果是数字
		if(is_numeric($keyword))
		{
			return "userid";
		}
		elseif(strstr($keyword,"到"))
		{			
			return "to";
		}
		else{
			return "address";
		}
	}
	private function trimall($str)//删除空格
	{
		$qian=array(" ","　","\t","\n","\r");$hou=array("","","","","");
		return str_replace($qian,$hou,$str);    
	}
}