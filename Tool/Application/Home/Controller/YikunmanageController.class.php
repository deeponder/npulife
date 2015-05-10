<?php

namespace Home\Controller;

use Think\Controller;
vendor("phpqrcode.qrlib");

class YikunmanageController extends Controller {

	/*首页*/
	public function index(){

		$this->checkActivityTime();		//超期活动检查
		$this->display("manage_login");

	}


/*=========================================================================
	 *@codeBlock:: 后台管理
   ****  *******    *        ******   *******
   *        *      *  *      *    *      *
   ****     *     ******     ******      *
      *     *    *      *    *  *        *
   ****     *   *        *   *    *      * 

=============================================================================*/
	public  function manageLogin(){
		session_start();	
		$password = I("password");
		if($password != "12345"){
			$this->assign("error","密码错误！");
			$this->display("manage_login");
		}else{
			session("manageLogin",1);
			redirect('/Tool/index.php/Home/Yikunmanage/manage');
		}

	}
	//各个管理页面显示
	public function manage($action=""){
		/*身份验证*/
		if(!($this->checkLogin())){
			$this->error("您没有登录！");
			return;
		}

		/*显示相应页面*/
		$activityModel = M('Activity','nl_','DB_CONFIG_NOW');
		$actPictureModel = M('ActivityPicture','nl_','DB_CONFIG_NOW');
		
		$userModel = M('Student','nl_','DB_CONFIG_NOW');

		if($action == ""){
			$this->display("manage_actpublish");
			return;
		}else{
			if(IS_AJAX){
				switch($action){
					case "member":
						//没有绑定的学员
						$map0['bind'] = 0;
						$studentNotBind = $userModel->where($map0)->order('college')->select();

						//有缺席记录的学员
						$map1['actabsent'] = array('gt',0);
						$map1['bind'] = 1;
						$studentBad = $userModel->where($map1)->order('college')->select();
						$stuNum = count($studentBad);
						for($i=0;$i<$stuNum;$i++){
							$studentBad[$i]['headerPic'] = get_headurl($studentBad[$i]['openid'],get_token());
						}
						//没有缺席记录的学员
						$map2['actabsent'] = 0;
						$map2['bind'] = 1;
						$studentGood = $userModel->where($map2)->order('college')->select();
						$stuNum = count($studentGood);
						for($i=0;$i<$stuNum;$i++){
							$studentGood[$i]['headerPic'] = get_headurl($studentGood[$i]['openid'],get_token());
						}
						
						$this->assign("studentNotBind",$studentNotBind);
						$this->assign('studentBad',$studentBad);
						$this->assign('studentGood',$studentGood);
						$this->display("manage_member");
						break;
					case "actmanage":
						//正在进行的活动
						$map1['state']  = array("neq", -1);
						$activityNow = $activityModel->where($map1)->order("deadline desc")->select();
						$actNum = count($activityNow);
						for($i = 0;$i<$actNum;$i++){
							$activityNow[$i]["content"] = $this->contentCut($activityNow[$i]["content"]);
							$activityNow[$i]["picture"] = $actPictureModel->where("actid='%s'",$activityNow[$i]["actid"])->getField("picid",true);
						}
						//已经结束的活动
						$map2['state']  = -1;
						$activityEnd = $activityModel->where($map2)->order("deadline desc")->select();
						$actNum = count($activityEnd);
						for($i = 0;$i<$actNum;$i++){
							$activityEnd[$i]["content"] = $this->contentCut($activityEnd[$i]["content"]);
							$activityEnd[$i]["picture"] = $actPictureModel->where("actid='%s'",$activityEnd[$i]["actid"])->getField("picid",true);
						}
						
						$this->assign("endNum",count($activityEnd));
						$this->assign('activityEnd',$activityEnd);
						$this->assign("activityNow",$activityNow);
						//dump($activityNow);
						$this->display("manage_activity");
						break;
					case "statistics":

						$this->display("manage_statistics");
						break;
					default:
						$this->error("错误的页面操作~:".$action);
						break;
							
				}
			}else{
				dump("清不要强制刷新页面~");
				//$this->display("manage_actpublish");
			}
		}
	}
	
	//活动管理
	public function actmanage(){
		/*身份验证*/
		if(!($this->checkLogin())){
			$this->error("您没有登录！");
			return;
		}
		$activityModel = M('Activity','nl_','DB_CONFIG_NOW');
		$actPictureModel =M('ActivityPicture','nl_','DB_CONFIG_NOW');
		$registerModel = M('Register','nl_','DB_CONFIG_NOW');
		$userModel = M('Student','nl_','DB_CONFIG_NOW');
		
		$action = I('action');
		$actid = I('actid');
		switch($action){
			case 'show':
				//活动详情
				$activityShow = $activityModel->where("actid = '%s'",$actid)->find();
				$activityShow["picture"] = $actPictureModel->where("actid='%s'",$activityShow["actid"])->getField("picid",true);				//参与的人
				
				//活动进度、统计信息(报名的人、未报名的人、(完成活动)参加的人)
				$studentsComplete = NULL;
				if($activityShow['state'] == 0){
					$activityShow['state']="报名中...";
				}elseif($activityShow['state'] == 1){
					$studentsComplete = $userModel->join('nl_register ON nl_student.snumber = nl_register.snumber')
					->where('actid = %d AND state = %d',$actid,1)
					->order('nl_student.snumber')->select();
					$activityShow['state']="报名已截止~";
				}else{
					$studentsComplete = $userModel->join('nl_register ON nl_student.snumber = nl_register.snumber')
					->where('actid = %d AND state = %d',$actid,1)
					->order('nl_student.snumber')->select();
					$activityShow['state']="活动结束~";
				}
				
				$studentsAssign = $userModel->join('nl_register ON nl_student.snumber = nl_register.snumber')
				->where('actid = "%d"',$actid)
				->order('nl_student.snumber')->select();
				
				$studentsAssignId = $registerModel->where('actid = "%d"',$actid)->getField("snumber",true);
				if($studentsAssignId == NULL){
					$studentsAssignId = array("-1");
				}
				$map1['snumber'] = array("not in",$studentsAssignId);
				$studentsNotAssign = $userModel->where($map1)->select();
				
				$this->assign("studentsComplete",$studentsComplete);
				$this->assign("studentsNotAssign",$studentsNotAssign);
				$this->assign("studentsAssign",$studentsAssign);
				$this->assign("activityShow",$activityShow);
				$this->display("manage_activity_show");
				break;
			case 'createassign':
				$activity = $activityModel->where("actid = '%s'",$actid)->find();
				if($activity['state'] == 0){
					$this->error("活动还在报名中~");
					return;
				}else if($activity['state'] == -1){
					$this->error("活动已经结束~");
					return;
				}
				
				//所有报名学生
				$studentsAssign = $userModel->join('nl_register ON nl_student.snumber = nl_register.snumber')
				->where('actid = "%d"',$actid)
				->order('nl_student.snumber')->select();
				
				if($studentsAssign == NULL){
					$this->error("没有人报名~");
					return;
				}
				$this->assign("students",$studentsAssign);
				$this->display("manage_erweima");
				break;
			case 'createTassign':
				$touristModel = M('Tourist','nl_','DB_CONFIG_NOW');
				$touristRegisterModel = M('TouristRegister','nl_','DB_CONFIG_NOW');

				$activity = $activityModel->where("actid = '%s'",$actid)->find();
				
				if($activity['state'] == 0){
					$this->error("活动还在报名中~");
					return;
				}else if($activity['state'] == -1){
					$this->error("活动已经结束~");
					return;
				}
				
				//所有报名的游客
				$studentsAssign = $touristModel->join('nl_tourist_register ON openid = topenid')
				->where('actid = "%d"',$actid)->select();
				
				if($studentsAssign == NULL){
					$this->error("没有游客报名~");
					return;
				}
				//dump($studentsAssign);
				//return;
				$this->assign("isTourist",1);
				$this->assign("students",$studentsAssign);
				$this->display("manage_erweima");
				break;
				break;
			case 'end':
				$touristModel = M('Tourist','nl_','DB_CONFIG_NOW');
				$touristRegisterModel = M('TouristRegister','nl_','DB_CONFIG_NOW');

				$activity = $activityModel->where("actid = %d",$actid)->find();
				if($activity['state'] == 0){
					$this->error("活动还在报名中~");
					return;
				}else if($activity['state'] == -1){
					$this->error("活动已经结束~");
					return;
				}
				
				$activityModel->where('actid = %d',$actid)->setField('state',-1);
				
				//未完成任务的游客
				$map_nuassign_tourist['actid'] = $actid;
				$map_nuassign_tourist['state'] = 0;
				$tourist_openids = $touristRegisterModel->where($map_nuassign_tourist)->getField('topenid',true);
				if($tourist_openids != NULL){
					$dbCheck  = $touristRegisterModel->where($map_nuassign_tourist)->setField('state',-1);
					if(!$dbCheck){
						$this->error("数据库更新错误（未完成任务游客）！");
						return;
					}
					$map_unassign_tourist_register = array('in',$tourist_openids);
					$touristModel->where($map_unassign_tourist_register)->setInc('absent');
					$unassignTourist = $touristModel->where($map_unassign_tourist_register)->select();
					foreach ($unassignTourist as $tourist) {	//封杀缺席3次以上的游客
						if($tourist['absent'] >=3 ){
							$data_kill_tourist['state'] = -1;
							$data_kill_tourist['openid'] = $tourist['openid'];
							$touristModel->save($data_kill_tourist);
						}
					}
				}

				//学员
				$map['actid'] = $actid;
				$map['state'] = 0;
				$map['taskcomplete'] = 2;
				//完成任务的(这些操作在学员完成任务时已经进行)
				/*
				$stuNums = $registerModel->where($map)->getField("snumber",true);
				if($stuNums != NULL){
					$registerModel->where($map)->setField('state',1);

					$map1['snumber'] = array('in',$stuNums);
					$dbCheck = $userModel->where($map1)->setInc('actattend');
					if(!$dbCheck){
						$this->error("数据库更新错误1！");
						return;
					}
				}
				*/
				//未完成任务的学员()
				$map['taskcomplete'] = array('lt',2);
				$stuNums = $registerModel->where($map)->getField("snumber",true);
				if($stuNums != NULL){
					$dbCheck = $registerModel->where($map)->setField('state',-1);
					if(!$dbCheck){
						$this->error("数据库更新错误3！");
						return;
					}
					
					$map1['snumber'] = array('in',$stuNums);
					$this->absentAndCheck($map1);
				}
				
				$this->mySuccessManage("活动已经结束！共有".strval(count($stuNums))."名游客报名确没有签到！");
				break;
			case "edit":
				$activity = $activityModel->where("actid = %d",$actid)->find();
				if($activity == NULL){
					$thi->error("错误的活动ID");
					return;
				}
				$activity["deadline"] = str_replace(' ','T',$activity["deadline"]);
				$activity["picture"] = $actPictureModel->where("actid='%s'",$actid)->getField("picid",true);
				$this->assign("activity",$activity);
				$this->display("manage_actedit");
				break;
			default:
				$this->error("活动管理命令暂不支持！");
				return ;
		}
		
	}
	//活动发布
	public function actpublish(){
		
		$activityModel = M('Activity','nl_','DB_CONFIG_NOW');
		$actPictureModel = M('ActivityPicture','nl_','DB_CONFIG_NOW');

		/*在数据库中添加活动*/
		$data = $activityModel->create();
		if($data == NULL){
			$this->error("没有数据！");
			return ;
		}
		if($data['topic']==""||$data['content']==""||$data['deadline']==""||$data['maxnumber']==""||$data['teacher']==""){
			$this->error("数据不完整！");
			return ;
		}
		//空格和换行
		$data['content'] = str_replace(" ","&nbsp", $data['content']);
		$data['content'] = str_replace("\r\n","<br>", $data['content']);
		$data['sponsor'] = "研工部";

		$actId = $activityModel->add($data);
		if(!$actId){
			$this->error("数据库添加活动失败！");
			return ;
		}
		
		/*在数据库中添加活动图片*/
		$pictureNum =  intval(I('pictureNum'), 10);
		
		for($i = 0;$i<$pictureNum;$i++){
			$pictureName = 'picture'.$i;
			$pictureFile = $_FILES[$pictureName];//I('data.picture'.$i,'','',$_FILES);
			if($pictureFile == NULL){
				$this->error("获取图片失败~:".$pictureName);
				return ;
			}
			//dump("有图啦~");
			$dataPic['picture'] = fread(fopen($pictureFile['tmp_name'],'rb'), $pictureFile['size']);
			$dataPic['actid'] = $actId;
			$isSuccess = $actPictureModel->add($dataPic);
			if(!$isSuccess){
				$this->error("数据库添加图片失败！");
				return ;
			}
		}
		$this->success("发布成功！","manage");
		//dump($pictureNum);
	}

	//活动编辑
	public function actedit(){

		$activityModel = M('Activity','nl_','DB_CONFIG_NOW');
		$actPictureModel = M('ActivityPicture','nl_','DB_CONFIG_NOW');

		$actid = I('actid');
		if($actid == NULL){
			$this->error("错误的活动ID！");
			return ;
		}
		/*跟新活动内容*/
		$data = $activityModel->create();
		if($data == NULL){
			$this->error("没有数据！");
			return ;
		}

		if($data['topic']==""||$data['content']==""||$data['deadline']==""||$data['maxnumber']==""||$data['teacher']==""){
			$this->error("数据不完整！");
			return ;
		}
		//空格和换行
		$data['content'] = str_replace(" ","&nbsp", $data['content']);
		$data['content'] = str_replace("\r\n","<br>", $data['content']);
		$data['sponsor'] = "研工部";

		$dbCheck = $activityModel->where("actid = %d",$actid)->save($data);
		//dump($dbCheck);
		//return;

		/*在数据库中添加活动图片*/
		$pictureNum =  intval(I('pictureNum'), 10);

		if($dbCheck == false && $pictureNum == 0){
			$this->error("没有更改！");
			return ;
		}
		
		for($i = 0;$i<$pictureNum;$i++){
			$pictureName = 'picture'.$i;
			$pictureFile = $_FILES[$pictureName];//I('data.picture'.$i,'','',$_FILES);
			if($pictureFile == NULL){
				$this->error("获取图片失败~:".$pictureName);
				return ;
			}
			//dump("有图啦~");
			$dataPic['picture'] = fread(fopen($pictureFile['tmp_name'],'rb'), $pictureFile['size']);
			$dataPic['actid'] = $actid;
			$isSuccess = $actPictureModel->add($dataPic);
			if(!$isSuccess){
				$this->error("数据库添加图片失败！");
				return ;
			}
		}
		$this->success("更新成功！","manage");
		//dump($pictureNum);
	}
	//人员管理
	public function memberManage(){
		
	}
	//通知管理
	public function noticeManage(){
		//身份验证

		$notificationModel = M('Notification','nl_','DB_CONFIG_NOW');
		$action = I("action");

		switch($action){
			case "delete":

				break;
			case "publish":
				$noticeData = $notificationModel->create();
				$dbCheck = $notificationModel->add();
				if(!$dbCheck){
					$this->error("通知数据库更新错误！");
					return;
				}
				break;
			default:
				return;
		}
	}

	protected function checkLogin(){
		if(session("manageLogin") == 1) return true;
		return false;
	}

/*===============================================================
 @codeBlock:: 后台管理
    *******    *       *   * * *
    *          * *     *   *     *
    *******    *   *   *   *      *
    *          *     * *   *     *
    *******    *       *   * * *
================================================================*/



/*=========================================================================
	 *@codeBlock:: 公共功能
   ****  *******    *        ******   *******
   *        *      *  *      *    *      *
   ****     *     ******     ******      *
      *     *    *      *    *  *        *
   ****     *   *        *   *    *      * 

=============================================================================*/
	public function test(){
		$token = get_token();
		$openid = get_openid();

		$result = customSend($openid,$token,"canilinhe你好","text");
	
		dump($result);
		/*
		$testModel = M('test','nl_','DB_CONFIG_NOW');
		$map['test3'] = 5;
		//$dbcheck = $testModel->where($map)->setInc('test2');
		//$testModel->where($map)->setInc('test3');
		$dbcheck = $testModel->where($map)->getField('test2');
		$dbcheck--;
		if(!$dbcheck){
			$this->error("操作失败！");
		}
		$this->mySuccess("操作成功！".$dbcheck);
		*/
	}
	

	protected function absentAndCheck(&$map){
		$userModel = M('Student','nl_','DB_CONFIG_NOW');
		$userModel->where($map)->setInc("actabsent");
		$userModel->where($map)->setInc("absent");
		//检查是否连续缺席超过三次
		$absentStudens = $userModel->where($map)->select();
		foreach($absentStudens as $absentItem){
			if($absentItem['absent'] >=3){
				//添加三次缺席后要做的事
			}
		}
	}

	protected function messageSend($openid,$message){

	}

	protected function error($error=""){
		$this->assign("error",$error);
		$this->display("error");
		return ;
	}
	protected function mySuccess($success=""){
		$this->assign("success",$success);
		$this->display("success");
		return ;
	}

	protected function mySuccessManage($success=""){
		$this->assign("success",$success);
		$this->display("success_manage");
		return ;
	}
	
	protected function contentCut($content){
		//显示活动列表时对活动的详细内容进行截取
		$n = strpos($content,"<br>");
		if($n == false){
			$n = 100;
		}
		return substr($content,0,$n);
		
	}
	
	public function pictureShow(){

		$actPictureModel =M('ActivityPicture','nl_','DB_CONFIG_NOW');
		$articlePictureModel =  M('ArticlePicture','nl_','DB_CONFIG_NOW');
		
		$action = I('action');
		$picId = I('picid');
		
		if($action == NULL ){
			return;
		}
		switch($action){
			case 'article':
				$data = $articlePictureModel->where('picid = "%s"',$picId)->find();
				echo($data['picture']);
				break;
			case 'activity':
				$data = $actPictureModel->where('picid = "%s"',$picId)->find();
				echo($data['picture']);
				break;
			case 'erweima':
				$id = I('id');
				$actid = I('actid');
				echo \QRcode::png("http://wechat.npulife.com/tool/home/Yikun/actAssign?id=".$id."&actid=".$actid,false,'Q',4,4);
				break;
			default:
				return;
		}
	}
	
	protected function checkActivityTime(){
		date_default_timezone_set('PRC');
		$activityModel = M('Activity','nl_','DB_CONFIG_NOW');
		//$timetestModel =  M('TestTime','nl_','DB_CONFIG_NOW');
		$userModel = M('Student','nl_','DB_CONFIG_NOW');
		$registerModel = M('Register','nl_','DB_CONFIG_NOW');
		
		$timeNow = date('y-m-d H:i:s',time());
		//报名时间截止但状态没有更改的活动
		$map['deadline'] = array("lt",$timeNow);
		$map['state'] = 0;
		$activitys = $activityModel->where($map)->select();
		if($activitys == NULL){
			return;
		}
		$outDateActNumber = count($activitys);
		for($i=0;$i<$outDateActNumber;$i++){
			$actid = $activitys[$i]['actid'];
			$snumbers = $registerModel->where("actid = '%d'",$actid)->getField('snumber', true);
			if($snumbers ==NULL){
				$snumbers = array('-1');
			}

			//检查是否为空
			$map1['snumber'] = array("not in",$snumbers);
			$this->absentAndCheck($map1);
			$data['actid'] = $actid;
			$data['state'] = 1;
			$activityModel->save($data);
		}
		//dump($activitys);
	}
}
/*===============================================================
 @codeBlock::公共功能
    *******    *       *   * * *
    *          * *     *   *     *
    *******    *   *   *   *      *
    *          *     * *   *     *
    *******    *       *   * * *
================================================================*/