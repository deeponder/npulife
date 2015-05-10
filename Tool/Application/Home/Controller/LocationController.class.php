<?php
namespace Home\Controller;
use Think\Controller;
class LocationController extends Controller {
    
	//是否可以回复消息。
	public function isReply()
	{
		$openid = I('post.FromUserName');
		$latitude = I('post.Latitude');
		$longitude = I('post.Longitude');
		
		//记录下用户的地理位置
		$map['openid'] = $openid;
		$map['latitude'] = $latitude;
		$map['longitude'] = $longitude;
		$PersonLocation = M ( 'PersonLocation','nl_','DB_CONFIG_NPULIFE_DATA');
		$PersonLocation->add($map);
		
		//检测是否开启了畅游校园功能。
		if(0)
		{
			//如果不在某个区域，或者一定时间内停留在同一区域，则不推送消息。
			$point = array('x'=>$latitude,'y'=>$longitude);
			$area = $this->getLocationInfo($point);
			if($area['status'])
			{
				//判断是否在同一个区域。或者同一时间段内。
				$issame = $this->isSameArea($openid,$area);
				if(!$issame)
				{				
					$this->sendNews($openid,$area);
				}
			}		
		}
	}
	
	private function isSameArea($openid,$area){
		//查找上次发送时所在的地点，如果不同则返回0，如果相同，则不发送。
		$map['openid'] = $openid;
		$map['locationid'] = $area['id'];
		
		$LbsSend = M ( 'LbsSend','nl_','DB_CONFIG_NPULIFE_DATA');
		
		$sendRecord = $LbsSend->where($map)->order('createdate desc')->find();
		if($sendRecord)
		{
			return 1;
		}
		return 0;
	}
	
	private function sendNews($openid,$area){
		$articles[0] = array(
				'Title'=>"LBS畅游校园:".$area['name'],
				'Description'=>$area['description'],
				'PicUrl'=>"",
				'Url'=>""
			);
		$touser = $openid;//"o8TQCj8ch3DuyerWWZjI8zsONdEA";//
		$content = $articles;
		$msgtype = "news";
		$token = "535ca7e3cde42";
		customSend($touser, $token, $content, $msgtype);
		
		//写一个发送记录。
		$map['openid'] = $openid;
		$map['locationid'] = $area['id'];
		$LbsSend = M ( 'LbsSend','nl_','DB_CONFIG_NPULIFE_DATA');
		$LbsSend->add($map);
	}
	
	//得到区域信息并返回。
	public function getLocationInfo($point){
		
		$areaList = M('lbs_changyou','nl_','DB_CONFIG_NPULIFE_DATA')->select();
		
		$area = $this->getWhere($point,$areaList);
		
		if($area)
		{
			$area['status'] = 1;//1;			
		}
		else
		{
			$area['status'] = 0;
		}
		
		return $area;
    }
	
	//判断在哪个区域内
	private function getWhere($point,$areaList){
		
		foreach($areaList as $area)
		{
			$isin = $this->isIn($point,$area);
			if($isin)
			{
				return $area;
			}
		}
		return 0;
	}
	
	//判断是否在这个区域内
	private function isIn($point,$area){
		$minx = $area['minx'];
		$miny = $area['miny'];
		$maxx = $area['maxx'];
		$maxy = $area['maxy'];
		
		$x = $point['x'];
		$y = $point['y'];
		
		//先检测x是不是在，再检测y是不是在。
		if(($x<=$maxx)
			&&($x>=$minx)
			&&($y<=$maxy)
			&&($y>=$miny))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
}