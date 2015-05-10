<?php
namespace Home\Controller;
use Think\Controller;
class PwallController extends Controller {

	public function test(){
		$mark = M('benkeluqu','nl_','DB_CONFIG1');
		$line = $mark->getField('fujian',true);
		dump($line);

	}

	public function wall(){
		$this->display();
	
	}
	
    public function wallmessage(){
	$wall=M('follows','nl_','DB_CONFIG1');
	$start=I('get.lastid');
	$lastid=$start+1;
	$time = date('Y-m-d H:i:s',time());
	$info=$wall->where('examine=1')->order('id Asc')->limit($start.',1')->select();
	
	$data[$lastid]['id'] = $info[0]['id'];
		// $data[$lastid]['fakeid'] = $info[0]['fake_id'];
		$data[$lastid]['num'] = $lastid;
		$data[$lastid]['content'] = $info[0]['contents'];
		$data[$lastid]['nickname'] = $info[0]['nickname'];
		$data[$lastid]['id'] = $info[0]['id'];
		$data[$lastid]['headimge'] = $info[0]['headimge'];
		if(count($info)==0){	
			$data2=array('data'=>$data,'ret'=>0);
			$this->ajaxReturn($data2,'JSON');
		}
		else{
			$data2=array('data'=>$data,'ret'=>1);
			$this->ajaxReturn($data2,'JSON');
		}
	}
	 public function prize(){
      $this->display();
    }

    // //作弊抽奖
    // public function cheat(){

    // 	$map['openid']="o8TQCj__PqrhgTrww9PfJq1HTatY";
    // 	$map['examine'] = 1;
    // 	     $dog=M('follows','nl_','DB_CONFIG1')->where($map)->select();
    //     $this->ajaxReturn($dog,'JSON');
    //     dump($dog);

    // }


    public function prizedata(){
    	//判断是否停止
     	 $action=I('get.action');
  
	 	 //读取参与活动的人员
      	$info=M('follows','nl_','DB_CONFIG1')->group('openid')->where('examine=1')->order('id ASC')->select();
		//若停止，返回一，输出中奖结果
        if ($action=='ok'){
    	echo"1";
    	}else{
    		//无数据
      		if(count($info)==0){
    		 echo"null";
      	}else{
      		//往前台传参与者信息
        	$this->ajaxReturn($info,'JSON');
     		}
    } 

    }
	public function draw(){
		$this->display();
	}

	public function drawdata(){
		//从前台获取奖品的种类和数量
		$num=I('post.draw');
		$cate=I('post.cate');
		$follow=M('follows','nl_','DB_CONFIG1');
	
		//从参与者的数据表中随机获取相应数目的参与者
		$list=$follow->group('openid')->where('status=0')->order('rand()')->limit($num)->select();

		for($i=0;$i<$num;$i++){
		//dump($list[$i]);
		$openid = $list[$i]['openid'];
	    
	    //以下到下条数据存储获奖用户所获的奖品，不是必须
		$vmap['openid'] =$openid;
		$openidArr = $follow->where($vmap)->getField('id',true);
		$idNum = count($openidArr);
		for($j = 0;$j<$idNum;$j++)
		{
		$aMap['id'] = $openidArr[$j];
		$status = $follow->where($aMap)->find();
		
		$status['status']=1;
		$status['cate']=$cate;
		$follow->save($status);
		}

		//给获奖用户发中奖信息
		$token = 'wx4c81bc4055e38cf5';
		$msgtype = 'text';
		$content = "恭喜你中了".$cate.",请准备好此条中奖信息，会后到工作人员处领取你的奖品";
		customSend($openid,$token,$content,$msgtype);
		}
		// $data['status']=1;
		$this->list=$list;
		// dump($list);
		// $data['status']=1;
		// M('winner','nl_','DB_CONFIG1')->save($data);
		// $follow->where()->save($data);
		//dump($list);


		
		$this->display('draw');
		
	}
    

		
}