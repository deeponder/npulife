<?php

namespace Home\Controller;
use Think\Controller;

class SayController extends Controller {
	
	/*
    public function showWall(){
   
    	$User = M('User');
    	$Saysomething = M('Saying');
    	$Saygood = M('Saygood');
    	$Sayreply = M('Sayreply');
    	$sayingList = array(array());
    	
    	$allSaying = $Saysomething
    	->field('np_saying.id,userid,text,goodnum,saytime,name')
    	->join('np_user ON np_saying.userid = np_user.id')->order('saytime')
    	->select();
    	
    	$arrLength=count($allSaying);
		for($i=0;$i<$arrLength;$i++) {
		  $sayingList[$i]['saying'] = $allSaying[$i];
		  $sayingList[$i]['goodList'] = $Saygood
		  ->where("say_id=%d",$allSaying[$i]['id'])
		  ->select();
		  $sayingList[$i]['replyList'] = $Sayreply
		  ->where("say_id=%d",$allSaying[$i]['id'])
		  ->select();
		}
    	$this->assign('sayingList',$sayingList);
    	//
    	$this->display();
    }
    */
	//protected  $crrentuid;
	public function index(){
		Session_start();
		$this->redirect('Say/say');
	}

    public function say(){
    	
   		$orderType = I('get.otype');
   		$_SESSION['otype'] = $orderType;
   		
   		$token = get_token();
   		$crrentuid = get_openid();
   		$Saysomething = M('Saying');
   		$Saygood = M('Saygood');
   		$Sayreply = M('Sayreply');

      $temp_Search_Result = $Saysomething->group('userid')->select();
      $attend_Numof_Person = count($temp_Search_Result);

   		$sayingList = array(array());
   		if($_SESSION['otype'] == '1'){
   			$allSaying = $Saysomething->order('goodnum desc')->limit(0,10)->select();
   		}else{
   			$allSaying = $Saysomething->order('saytime desc')->limit(0,10)->select();
   		}
   		
   		if($allSaying != NULL){
   			$arrLength=count($allSaying);
   			for($i=0;$i<$arrLength;$i++) {
   				//昵称和头像
   				//$allSaying[$i]['nickname'] = get_nickname1($allSaying[$i]['userid'], $token);
   			//	$allSaying[$i]['headurl'] = get_headurl($allSaying[$i]['userid'], $token);
   				//处理时间显示
   				$timesay = strtotime($allSaying[$i]['saytime']);
   				$newyear=strtotime ('2014-12-31 24:00:00');  //过年时间
   				if($timesay < $newyear){
   					$days = ceil(($newyear-$timesay)/86400);
   					$allSaying[$i]['saytime'] = '跨年前'.$days.'天';
   				}else{
   					$allSaying[$i]['saytime'] = date('m月d日',$timesay);
   				}
   				//处理点赞按钮显示
   				$isgood =  $Saygood
   				->where("say_id=%d and userid='%s'",$allSaying[$i]['id'],$crrentuid)
   				->select();
   				if($isgood != NULL){
   					$allSaying[$i]['isgood'] = 'disabled';
   				}else{
   					$allSaying[$i]['isgood'] = '';
   				}
   				$sayingList[$i]['saying'] = $allSaying[$i];
   				//点赞列表&回复列表
   				$sayingList[$i]['goodList'] = $Saygood
   				->where("say_id=%d",$allSaying[$i]['id'])
   				->select();
   				$sayingList[$i]['replyList'] = $Sayreply
   				->where("say_id=%d",$allSaying[$i]['id'])
   				->select();
   			}
   		}
      $this->assign('attendnum',$attend_Numof_Person);
   		$this->assign('sayingList',$sayingList);
   		$this->assign('looktype',$orderType);
    	$this->display();
    }
    //处理post过来的信息
    public function saying(){
   		/*插入数据*/
    	//$text = I('post.text');
    	$Saysomething = M('Saying');
      $niming = I('post.niming');
      $nmName = I('post.nmName');
    	$saydata = $Saysomething->create();
    	$saydata['userid'] = get_openid();
      if($saydata['userid'] != '-1'){
                //昵称和头像
        if(!empty($niming)){
            $saydata['nickname'] = $nmName;
            $saydata['headurl'] = "/Tool/Public/default.png"; 
        }else{
              $saydata['nickname'] = get_nickname1($saydata['userid'], $token);
              $saydata['headurl'] = get_headurl($saydata['userid'], $token);
        }

        //dump($saydata);
        //dump($nmName);
        //dump($niming);
        //$saydata['text'] = $text;
        //dump($saydata);
        
        $result = $Saysomething->add($saydata);
        /*重定向*/
        if($result){
          $this->redirect('Say/say');
        }else{
          $this->redirect('Say/say');
        }

      }else{
        echo "不支持浏览器发表~！";
      }
      

    	
    }
	//点赞处理函数
    public function dianZan(){
    
    	$sayid = I('get.sayid');
    	$Saysomething = M('Saying');
    	$Saygood = M('Saygood');
    	
    	$data['say_id'] = $sayid;
    	$data['userid'] = get_openid();

 //判断该用户是否已经对该留言点了赞
    	$isgood =  $Saygood
      ->where("say_id=%d and userid='%s'",$sayid,$data['userid'])
      ->select();
      if($isgood != NULL){
          $return['check'] = 'false';
          $this->ajaxReturn($return,'JSON');
      }else{
        $result1 = $Saysomething->where("id=%d",$sayid)->select();
        $data1['goodnum'] =$result1[0]['goodnum']+1;
        
        $result = $Saygood->add($data);
        if($result){
          $result = $Saysomething->where("id=%d",$sayid)->setField($data1);
          if($result) {
            $return['check'] = 'success';
            $this->ajaxReturn($return,'JSON');
          }else{
            $return['check'] = 'false';
            $this->ajaxReturn($return,'JSON');
          }
        }else{
          $return['check'] = 'success';
          $this->ajaxReturn($return,'JSON');
        }
      }
    	
    	

    }
    
    public function getMore(){
    	$page = I('get.page');
    	$token = get_token();
    	$crrentuid = get_openid();
    	$Saysomething = M('Saying');
    	$Saygood = M('Saygood');
    	$Sayreply = M('Sayreply');
    	$sayingList = array(array());
    	 
    	//$allSaying = $Saysomething->order('saytime desc')->limit(10*$page,10*$page+9)->select();
    	if($_SESSION['otype'] == '1'){
    		$allSaying = $Saysomething->order('goodnum desc')->limit(10*$page,10)->select();
    	}else{
    		$allSaying = $Saysomething->order('saytime desc')->limit(10*$page,10)->select();
    	}
    	if($allSaying == null){
    		$this->ajaxReturn(null,'JSON');
    	}
    	
    	$arrLength=count($allSaying);
    	for($i=0;$i<$arrLength;$i++) {
    		//$allSaying[$i]['nickname'] = get_nickname1($allSaying[$i]['userid'], $token);
    		//$allSaying[$i]['headurl'] = get_headurl($allSaying[$i]['userid'], $token);
    		$timesay = strtotime($allSaying[$i]['saytime']);
   				$newyear=strtotime ('2014-12-31 24:00:00');  //过年时间
   				if($timesay < $newyear){
   					$days = ceil(($newyear-$timesay)/86400);
   					$allSaying[$i]['saytime'] = '跨年前'.$days.'天';
   				}else{
   					$allSaying[$i]['saytime'] = date('m月d日',$timesay);
   				}
    		$isgood =  $Saygood
   				->where("say_id=%d and userid='%s'",$allSaying[$i]['id'],$crrentuid)
   				->select();
   			if($isgood != NULL){
   				$allSaying[$i]['isgood'] = 'disabled';
   			}else{
   				$allSaying[$i]['isgood'] = '';
   			}
    		$sayingList[$i]['saying'] = $allSaying[$i];
    		$sayingList[$i]['goodList'] = $Saygood
    		->where("say_id=%d",$allSaying[$i]['id'])
    		->select();
    		$sayingList[$i]['replyList'] = $Sayreply
    		->where("say_id=%d",$allSaying[$i]['id'])
    		->select();
    	}
    	
    	$this->ajaxReturn($sayingList,'JSON');
    }
    
}

?>