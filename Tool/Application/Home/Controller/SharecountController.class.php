<?php
namespace Home\Controller;
use Think\Controller;

class SharecountController extends Controller 
{
    
    public function test(){
    	$this->display();
    }
	public function movie()
	{
		//当前用户的openid
		$openid=get_openid();
		$openid1=I('get.openid1');
		$openid2=I('get.viewerid');
		
		$share = M ( 'share_count','nl_','DB_CONFIG_NPULIFE_DATA');
		
		$record['forward_id']=$openid1;
		$record['owner_id']=$openid2;
		//$record['viewer_id']=$openid;
		
		$found = $share->where($record)->find();
		$number = count($found);
		
		
		$Num = M ( 'number_share','nl_','DB_CONFIG_NPULIFE_DATA'); 
		$map['openid']=$openid1;
		$count = $Num->where($map)->getfield('num');
	
		
		//避免浏览不转发记录的写入
		if($number==0)
		{
		$share->add($record);
		
		//转发次数的统计
		if($count==0)
		{
		$add['openid']=$openid1;
		$add['num']=1;
		$Num->add($add);
		}
		else
		{
		$add['num']=$count+1;
		$Num->save($add);
		}
		
		
		}
		
		$this->openid2=$openid2;
		$this->viewerid=$openid;
		
		//$share->add($record);
		$this->display();
		//dump($num);
		$click=$share->getfield('owner_id',true);
		$isClick = 'false';
		if(!in_array($openid,$click))
		{
		$isClick = 'true';
		}
		$this->isClick=$isClick;
	}

	public function movie1()
	{
		$openid=get_openid();
		$openid1=I('get.openid1');
		$openid2=I('get.openid2');
	
		$forward = M ( 'forward_log','nl_','DB_CONFIG_NPULIFE_DATA');
		//如果第二个参数和当前浏览用户的openid不同，重定向
		
		if($openid!=$openid2||get_openid()==-1)
		{	
			$openid=get_openid();
			if($openid!=-1)
			{			
			$record['forward_id']=$openid1;
			$record['current_id']=$openid2;
			$record['activity_tag']='movie';
			$record['viewer_id']=$openid;
			$forward->add($record);
			}
			
			$id1=urlencode($openid2);
			$id2=urlencode($openid);
			header('Location: http://wechat.npulife.com/tool/home/sharecount/movie1?openid1='.$id1.'&openid2='.$id2);
		}
		
		//当前用户的被转发量
		$select['forward_id']=$openid;
		$count = count($forward->Distinct(true)->field(array('forward_id','current_id'))->where($select)->select());
		//$count = count($forward->Distinct(true)->field(array('forward_id','current_id'))->select());
		echo($count);
		
		
		
		
	}
}