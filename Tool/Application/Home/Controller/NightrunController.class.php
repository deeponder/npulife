<?php
namespace Home\Controller;
use Think\Controller;
class NightrunController extends Controller {

	public function index(){

		$this->display();
	}
    
	public function introduce(){
		$this->display();
	}

    //夜跑排名页面
	public function rank(){
		// dump($listall);
	$runtable = M('nightrun','nl_','DB_CONFIG1');

		
		//personal info
	$openid = get_openid();	
	$omap['openid'] = $openid;
	$myinfor = $runtable->where($omap)->select();
// dump($myinfor[0]['num']);
			$myrank = $myinfor[0]['num'];
			$myhead = $myinfor[0]['headurl'];
	 		$mynickname = $myinfor[0]['nickname'];
			$myrankstate = $myinfor[0]['rankstate'];
			$mycredit = $myinfor[0]['credit'];
	


	$list = $runtable->order('totallong DESC')->limit('10')->select();


		// $token = get_token();
	
	// $mymap['openid']=$openid;
	// dump($list);
	// $myrank = $list->where($mymap)->select();
	$this->myrank = $myrank;
	$this ->myhead = $myhead;
	$this->mynickname = $mynickname;
	$this->myrankstate = $myrankstate;
	$this->mycredit = $mycredit;
	// dump($myhead);
	$this->list = $list;

	// dump($myrank);
	$this->display();
    }

//ajax 动态加载
    public function getmorerank(){
    	$page = I('get.page');
// $page =1;
    	// dump($page);
		$num = 10;
		$runman = M('nightrun','nl_','DB_CONFIG1');	
		$result = $runman->order('num')->limit($page*$num,$num)->select();
// dump($result);
		$this->ajaxReturn($result,'JSON');


    }
function test2(){
		$touser = "o8TQCj__PqrhgTrww9PfJq1HTatY";
				$token = "535ca7e3cde42";
				$msgtype = "text";
				$content = "恭喜你在我们的夜跑活动中中奖了，请发送你的联系方式（电话）到 15399407020，以方便我们联系你，派发奖品";
				customSend($touser, $token, $content, $msgtype);
}
    //通知中奖信息
    function send(){
			$runtable = M('nightrun','nl_','DB_CONFIG1');
			// $list = $runtable->order('credit DESC')->limit('10')->select();
			$list = $runtable->select();
			$num =count($list);
			for($i=0;$i<$num;$i++){			
				$touser = $list[$i]['openid'];
				$token = "535ca7e3cde42";
				$msgtype = "text";
				// $rank = (string)($i+1);
				$content = "所有参与夜跑活动的小伙伴注意了，只要你的积分高于100均可兑换我们的奖品，详情请关注瓜大生活圈订阅号（npulife2）,查看历史消息中的夜跑发奖通知。请于本周14号到16号中午12:30~13:30到学舟楼404领取哦，有任何问题请联系我们15596898316";
					// $content = "恭喜你在我们的夜跑活动跑了第了，请发送你的联系方式（电话）和昵称到 15399407020，以方便我们联系你，派发奖品";
				// dump($content);
				customSend($touser, $token, $content, $msgtype);
// 
				// dump($openid);
			}
			echo "done!";
			// dump($list[0]['openid']);

}

//auto do getruntable()
    public function test(){
    	//自动更新，failed~~
  		ignore_user_abort(); //即使Client断开(如关掉浏览器)，PHP脚本也可以继续执行.
	set_time_limit(0); // 执行时间为无限制，php默认执行时间是30秒，可以让程序无限制的执行下去
	$interval=3600; // 每隔运行一次
	do{

		$this->getruntable();
    	sleep($interval); // 按设置的时间等待一小时循环执行
   		// $sql="update blog set time=now()";
    	// dump("dkf");

	}while(true);
    	
    	// ignore_user_abort();
    	// set_time_limit(0);
    	// $interval = 5;

    	// // dump("dfd");
    // 	dump(time());
    // 	sleep(1);
    // dump(time());
    	// for(;;){


    	// 	$this->test2();
    	// 	sleep(2);
    	// // 	dump(time());
    	// }
   	


}



//build the table we want
	public function getruntable(){
		//实时更新跑步人员信息表
			$qrmember=M('ErweimaMember','nl_','DB_CONFIG1');
		$runtable = M('nightrun','nl_','DB_CONFIG1');
		$runman = $qrmember->group('openid')->select();
		// $data['openid'] = $qrmember->

		$num = count($runman);
		$state = 1;
		
		// dump($num);
		//build the table nightrun based on erweimamember
		 for($i=0;$i<$num;$i++){
		 	$totalintime=0;
			$totalouttime=0;
		 	$pmap['openid'] = $runman[$i]['openid'];
		 	$vmap['openid'] = $runman[$i]['openid'];
		 	$vmap['eventkey'] = 101;
		 	$mmap['openid'] = $runman[$i]['openid'];
		 	$mmap['eventkey'] = 100;

		 	$outtime = $qrmember->where($vmap)->getField('createdate',true);
		 	$intime = $qrmember->where($mmap)->getField('createdate',true);
		 	$onum = count($outtime);
		 	$inum = count($intime);
		 	if($onum==$inum){
		 	for($j=0;$j<$inum;$j++){
		 		
		 		$myintime = strtotime($intime[$j]);
		 	// dump($myintime);
		 	// dump("ininininiininininin");
		 		$totalintime+=$myintime;
		 	

		 	}
		 	for($j=0;$j<$onum;$j++){
		 		$myouttime = strtotime($outtime[$j]);
		 		// dump($myouttime);
		 		// 		 	dump("ooooooooooooooooooooooooo");

		 		$totalouttime +=$myouttime; 

		 	}
		 	// dump($totalouttime);
		 	// dump($totalintime);
		 	$totaltime = ($totalouttime-$totalintime);
		 	
			$runtime = intval($totaltime/60);
		 	//判断夜跑列表是否有更新
		 	$oldtime = $runtable->where($pmap)->getField('totallong',true);
		 		// dump($oldtime);
		 		// dump($totaltime);
		 		// dump('------------------');
		 	if($oldtime[0]!=$runtime){
		 		$state = 0;
		 	}
// 		 	dump($outtime);
// 		 			 	dump($intime);
// // 		 			 			 	dump($totalouttime);
// 		 			 			 			 	dump($runman[$i]['openid']);

// dump('------------------');



		 	//获取个人信息
			$token = get_token();
			$userinfo = getWeixinUserInfo($runman[$i]['openid'], $token);
			// dump($runman[$i]['openid']);
			$data['headurl'] = $userinfo['headimgurl'];
			$data['nickname'] = $userinfo['nickname'];
			$data['openid'] = $runman[$i]['openid'];

			$runtime = intval($totaltime/60);
			$credit  = $runtime + $onum*5;
			$data['totallong'] = $runtime;

			$imap['openid'] = $runman[$i]['openid'];
			$theUser = M('Member','nl_','DB_CONFIG1')->where($imap)->find();
			$data['uid'] = $theUser['uid'];
			$data['count'] = $onum;
			$data['credit'] = $credit;

			// $data1['totallong'] = $runtime;
			// $data1['openid'] = $runman[$i]['openid'];
			// $data1['count'] = $onum;

			// dump($data);
			$wesee = $runtable->where($imap)->select();
			if(!$wesee){
// M('runinfo','nl_','DB_CONFIG1')->add($data1);
				$runtable->where($imap)->add($data);
			}else {
				// M('runinfo','nl_','DB_CONFIG1')->where($imap)->save($data1);

				$runtable->where($imap)->save($data);
			}
			
			// dump($totaltime);
		 }
	}

// dump($state);

	$listall = $runtable->order('totallong DESC')->select();
	$rnum = count($listall);

// dump($listall);
// dump($rnum);
// dump($state);

	//更新用户的排名信息
	if(!$state){
	for($i=0;$i<$rnum;$i++){
		$oldnum = $listall[$i]['num'];
		// dump($oldnum);
		$listall[$i]['num'] = $i+1;
		if($oldnum>$listall[$i]['num']||$oldnum==0){
			$listall[$i]['rankstate']="up";
		}elseif ($oldnum<$listall[$i]['num']) {
			$listall[$i]['rankstate']="down";
		}else{
			$listall[$i]['rankstate']="nc";
		}
		// dump('dfd');
		$data1['num'] = $listall[$i]['num']; 
		$data1['rankstate'] = $listall[$i]['rankstate'];
		$umap['openid'] = $listall[$i]['openid'];
		$runtable->where($umap)->save($data1);

	}
}

}
	
	
}