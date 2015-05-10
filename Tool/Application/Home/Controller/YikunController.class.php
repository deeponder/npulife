<?php

namespace Home\Controller;

use Think\Controller;
vendor("phpqrcode.qrlib");

class YikunController extends Controller {

	/*首页*/
	public function index(){

		//用户身份、用户信息
		$openid = get_openid();
		$token = get_token();



		if($openid == -1 || $openid == NULL){
			$this->error('请从微生活中进入~');
			return;
		}

		$nick_name = get_nickname1($openid,$token);
		if($nick_name == NULL){
			$this->error('请从微生活中进入~');
			return;
		}

		//dump($openid);
		//return;
		//网站升级
		//if($openid != "o8TQCj2Z2JO1kRLifVJnrk2GI6yM" &&$openid !=-1){
		//	$this->error('网站维护中~');
		//	return;
		//}
		


		//数据库模型创建
		$userModel = M('Student','nl_','DB_CONFIG_NOW');
		$activityModel = M('Activity','nl_','DB_CONFIG_NOW');
		$actPictureModel = M('ActivityPicture','nl_','DB_CONFIG_NOW');
		//$articleModel = M('Article','nl_','DB_CONFIG_NOW');
		//$articlePictureModel = M('ArticlePicture','nl_','DB_CONFIG_NOW');
		$ArticleDB = M("CustomReplyNews",'nl_','DB_CONFIG1');//自己的文章

		session_start();			//开启session
		$this->checkActivityTime();		//超期活动检查\


		$student = $userModel->where("openid = '%s'",$openid)->find();

		if($student == NULL){
			session("user",null);
			session("snumber",null);
			$this->assign("popUserinfo",0);
			$this->assign("userinfo","游客: ".get_nickname1($openid,$token));
		}else{
			session("user",1);
			session("snumber",$student['snumber']);
			$this->assign("popUserinfo",1);
			$this->assign("userinfo","学员:".$student['name']);
		}

		//推荐文章
		$xuetangMap["cate_id"] = array("in","3");
		$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit(10)->select();
		$articleNum = count($myArticleList);
		for($i=0;$i<$articleNum;$i++)
		{
			$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
			$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
			$myArticleList[$i]["friendlyDate"] = date("Y-m-d",$myArticleList[$i]["cTime"]);
			$myArticleList[$i]["category"] = "翼鲲";
		}
		//热门活动
		$activityHot = $activityModel->where("state = 0")->order("peoplenumber desc")->limit(10)->select();
		$actNum = count($activityHot);
		for($i = 0;$i<$actNum;$i++){
			//显示活动列表时对活动的详细内容进行截取
			$n = strpos($activityHot[$i]['content'],"<br>"); 
			if($n == false){
				$n = 100;
			}
			$activityHot[$i]['content'] = substr($activityHot[$i]['content'],0,$n);
			$activityHot[$i]["picture"] = $actPictureModel->field("picid")->where("actid='%s'",$activityHot[$i]["actid"])->select();
		}
		$this->assign("articleList",$myArticleList);
		$this->assign("activityHot",$activityHot);
		
		$this->display();
	}
	
	public function member(){
		$userModel = M('Student','nl_','DB_CONFIG_NOW');
		$registerModel = M('Register','nl_','DB_CONFIG_NOW');
		
		$students = $userModel->where('bind = 1')->order('actattend desc')->limit(10)->select();
		$stuNum = count($students);
		for($i=0;$i<$stuNum;$i++){
			$students[$i]['headerPic'] = get_headurl($students[$i]['openid'],get_token());
		}
		$this->assign("students",$students);
		$this->display("front_member");
	}
	
	public function center(){
		$userModel = M('Student','nl_','DB_CONFIG_NOW');
		
		$action = I("action");
		if($action == "bind"){
			$map['name'] = I("name");
			$map['snumber'] = I("snumber");
			if($map['name'] ==''||$map['snumber']==''){
				$this->error("数据不完整~");
				return;
			}
			$student = $userModel->where($map)->find();
			if($student == NULL){
				$this->error("没有姓名为<strong>".$map['name']."</strong>,学号为<strong>".$map['snumber']."</strong>的翼鲲班学员！");
				return;
			}
			if($student['bind'] == 1){
				$this->error("该学号已经被绑定！如果不是您本人绑定的请与翼鲲班指导老师联系！");
				return;
			}
			session("bind",1);
			session("snumber",$student['snumber']);
			$this->assign("student",$student);
			$this->display("front_bindcheck");
		}elseif($action=="bindcheck"){
			if(session("bind") == 1){
				$data['openid'] = get_openid();
				$data['snumber'] = session("snumber");
				$data['bind'] = 1;
				$dbcheck = $userModel->save($data);
				if(!$dbcheck){
					$this->error("数据库跟新错误！");
					return;
				}
				$this->success("绑定成功！","index");
			}else{
				$this->error("不要直接通过连接进来！");
				return;
			}
		}else{
			if(session("user") == 1){
				$this->display("front_center");
			}else{
				$this->display("front_bind");
			}			
		}
	}
	
	public function actList(){
		$activityModel = M('Activity','nl_','DB_CONFIG_NOW');
		$actPictureModel = M('ActivityPicture','nl_','DB_CONFIG_NOW');
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

		$this->display("front_actlist");

	}

	public function notification(){
		//身份检查
		//通知数据获取(个人通知、公共通知)

		//赋值和显示
		$this->display("front_notification");

	}

	public function centerShow(){
		
		$action = I("action");
		$snumber = session("snumber");
		switch($action){
			case "person":
				$userModel = M('Student','nl_','DB_CONFIG_NOW');
				$student = $userModel->where('snumber = "%s"',$snumber)->find();
				$student['wxnickname'] = get_nickname1(get_openid(), get_token());
				$this->assign("student",$student);
				$this->display("front_center_person");
				break;
			case "article";
				//$articleModel = M('Article','nl_','DB_CONFIG_NOW');
				//待审核文章
				
				//审核通过文章(优秀)
				
				//未通过审核的文章
				
				//$articles = $articleModel->where('snumber= "%s"',$snumber)->select();
				
				break;
			case "activity":
				$activityModel = M('Activity','nl_','DB_CONFIG_NOW');
				$actPictureModel = M('ActivityPicture','nl_','DB_CONFIG_NOW');
				$registerModel = M('Register','nl_','DB_CONFIG_NOW');
				
				//参加的活动
				$map1['snumber'] = $snumber;
				$map1['state'] = 1;
				
				$activitysAttend = $registerModel->where($map1)->order("attendtime desc")->limit(5)->select();
				$actNum = count($activitysAttend);
				for($i = 0;$i<$actNum;$i++){
					$activitysAttend[$i]['year'] = date("y/m/d",strtotime($activitysAttend[$i]['attendtime']));
					$activitysAttend[$i]['hour'] = date("h:i",strtotime($activitysAttend[$i]['attendtime']));
					$activitysAttend[$i]["picture"] = $actPictureModel->where("actid='%s'",$activitysAttend[$i]["actid"])->getField("picid",true);
				}
				$this->assign("activityAttend",$activitysAttend);
				$this->display("front_center_activity");
				break;
			case "myactivity":
				$activityModel = M('Activity','nl_','DB_CONFIG_NOW');
				$actPictureModel = M('ActivityPicture','nl_','DB_CONFIG_NOW');
				$registerModel = M('Register','nl_','DB_CONFIG_NOW');
				//报名的、未结束的活动
				$map1['snumber'] = $snumber;
				$map1['nl_register.state'] = 0;
				$activitysAssign = $registerModel->join('nl_activity on nl_register.actid = nl_activity.actid')->where($map1)->order('taskcomplete')->select();
				$actNum = count($activitysAssign);
				for($i = 0;$i<$actNum;$i++){
					if($activitysAssign[$i]['state'] ==0){
						$activitysAssign[$i]['mystate'] = '活动还在报名中~';
					}else{
						if($activitysAssign[$i]['taskcomplete'] == 0){
							$activitysAssign[$i]['mystate'] = '还未参加~';
 						}else if($activitysAssign[$i]['taskcomplete'] == 1){
 							$activitysAssign[$i]['mystate'] = '等待完成问卷~';
 						}else{
 							$activitysAssign[$i]['mystate'] = '已成功参与！';
  						}
					}
					$activitysAssign[$i]["content"] = $this->contentCut($activitysAssign[$i]["content"]);
					$activitysAssign[$i]["picture"] = $actPictureModel->where("actid='%s'",$activitysAssign[$i]["actid"])->getField("picid",true);
				}
				//未报名的还在报名的活动
				$actids = $registerModel->where('snumber = "%s"',$snumber)->getField('actid',true);
				if($actids == NULL){
					$actids = array("-1");
				}
				$map2['actid'] = array('not in',$actids);
				$map2['state'] = 0;
				$activitysNotAssign = $activityModel->where($map2)->select();
				$actNum = count($activitysNotAssign);
				for($i = 0;$i<$actNum;$i++){
					$activitysNotAssign[$i]['mystate'] = '点入去报名';
					$activitysNotAssign[$i]["content"] = $this->contentCut($activitysNotAssign[$i]["content"]);
					$activitysNotAssign[$i]["picture"] = $actPictureModel->where("actid='%s'",$activitysNotAssign[$i]["actid"])->getField("picid",true);
				}
				//缺席的活动
				//1. 未报名的
				$actids = $registerModel->where('snumber = "%s"',$snumber)->getField('actid',true);
				if($actids == NULL){
					$actids = array("-1");
				}
				$map3['actid'] = array('not in',$actids);
				$map3['state'] = array('neq',0);
				$activitysNotAssign1 = $activityModel->where($map3)->select();
				$actNum = count($activitysNotAssign1);
				for($i = 0;$i<$actNum;$i++){
					$activitysNotAssign1[$i]['mystate'] = '未报名';
					$activitysNotAssign1[$i]["content"] = $this->contentCut($activitysNotAssign1[$i]["content"]);
					$activitysNotAssign1[$i]["picture"] = $actPictureModel->where("actid='%s'",$activitysNotAssign1[$i]["actid"])->getField("picid",true);
				}
				//2. 未参加的
				$map4['snumber'] = $snumber;
				$map4['nl_register.state'] = -1;
				$activitysAbsent = $registerModel->join('nl_activity on nl_register.actid = nl_activity.actid')->where($map4)->order('taskcomplete')->select();
				$actNum = count($activitysAbsent);
				for($i = 0;$i<$actNum;$i++){
					$activitysAbsent[$i]['mystate'] = "未参加";
					$activitysAbsent[$i]["content"] = $this->contentCut($activitysAbsent[$i]["content"]);
					$activitysAbsent[$i]["picture"] = $actPictureModel->where("actid='%s'",$activitysAbsent[$i]["actid"])->getField("picid",true);
				}
				
				
				$this->assign("activityNotAssign1",$activitysNotAssign1);
				$this->assign("activityAbsent",$activitysAbsent);
				$this->assign("activityNotAssign",$activitysNotAssign);
				$this->assign("activityAssign",$activitysAssign);
				$this->display("front_center_myacitvity");
				break;
			case "statistics":
				$activityModel = M('Activity','nl_','DB_CONFIG_NOW');
				$registerModel = M('Register','nl_','DB_CONFIG_NOW');
				$questionnaireModel = M('Questionnaire','nl_','DB_CONFIG_NOW');
				//报名的还未结束的活动
				$map['snumber'] = $snumber;
				$map['nl_register.state'] = 0;
				$map['nl_activity.state'] = array('neq',-1);
				$activitysAssign = $registerModel->join('nl_activity on nl_register.actid = nl_activity.actid')->where($map)->select();
				$actNow = count($activitysAssign);
				//未开始、未报名的活动
				$actids = $registerModel->where('snumber = "%s"',$snumber)->getField('actid',true);
				if($actids == NULL){
					$actids = array("-1");
				}
				$map2['actid'] = array('not in',$actids);
				$map2['state'] = 0;
				$activitysNotAssign1 = $activityModel->where($map2)->select();
				$notAssign = count($activitysNotAssign1);
				//缺席的（未参加和未报名的）
				$absent = count($registerModel->where('snumber = "%s" AND state = -1',$snumber)->select());

				$actids = $registerModel->where('snumber = "%s"',$snumber)->getField('actid',true);
				if($actids == NULL){
					$actids = array("-1");
				}
				$map1['actid'] = array('not in',$actids);
				$map1['state'] = array('neq',0);
				$activitysNotAssign = $activityModel->where($map1)->select();
				$absent += count($activitysNotAssign);
				//参加的活动
				$attend = count($registerModel->where('snumber = "%s" AND state = 1',$snumber)->select());
				
				/**/
				$satisfaction = $questionnaireModel->field('value1,teacher,count(value1) as num')->group('value1,teacher')->select();
				$satisNum = count($satisfaction);
				$string = "";
				for($i =0;$i <$satisNum;$i++){
					$string = $string.(100-($satisfaction[$i]['value1']-1)*20)."分	".$satisfaction[$i]['teacher']."	".$satisfaction[$i]['num']."
";
				}
				//dump($string);
				//return;
				$this->assign("actNow",$actNow);
				$this->assign("absent",$absent);
				$this->assign("notAssign",$notAssign);
				$this->assign("attend",$attend);
				
				$this->assign("string",$string);
				$this->display("front_center_statistics");
				break;
			default:
				$this->error("错误的请求！");
				return;
		}	
	}
	
	public function getMore(){
		$activityModel = M('Activity','nl_','DB_CONFIG_NOW');
		$actPictureModel = M('ActivityPicture','nl_','DB_CONFIG_NOW');
		$registerModel = M('Register','nl_','DB_CONFIG_NOW');
		$userModel = M('Student','nl_','DB_CONFIG_NOW');
	
		$action = I("action");
		$page = intval(I("page"),10);
		switch($action){
			case "foot":
				//参加的活动
				$onepageNum = 5;
				$map1['snumber'] = session("snumber");
				$map1['state'] = 1;
				$activitysAttend = $registerModel->where($map1)->order("attendtime desc")->limit($onepageNum*$page,$onepageNum)->select();
				$actNum = count($activitysAttend);
				for($i = 0;$i<$actNum;$i++){
					$activitysAttend[$i]['year'] = date("y/m/d",strtotime($activitysAttend[$i]['attendtime']));
					$activitysAttend[$i]['hour'] = date("h:i",strtotime($activitysAttend[$i]['attendtime']));
					$activitysAttend[$i]["picture"] = $actPictureModel->where("actid='%s'",$activitysAttend[$i]["actid"])->getField("picid",true);
				}
				$this->ajaxReturn($activitysAttend);
				break;
			case "member":
				$onepageNum = 10;
				$students = $userModel->where('bind = 1')->order('actattend desc')->limit($onepageNum*$page,$onepageNum)->select();
				$stuNum = count($students);
				for($i=0;$i<$stuNum;$i++){
					$students[$i]['headerPic'] = get_headurl($students[$i]['openid'],get_token());
				}
				$this->ajaxReturn($students);
				break;
			case "notice":
				$onepageNum = 10;
				$notices = NULL;
				//waiting for coding ~ ~ ~
				break;
			default:
				return;
		}
	}
	
	public function activityShow(){
		$activityModel = M('Activity','nl_','DB_CONFIG_NOW');
		$actPictureModel = M('ActivityPicture','nl_','DB_CONFIG_NOW');
		$questionnaireModel = M("Questionnaire","nl_","DB_CONFIG_NOW");
		$registerModel = M('Register','nl_','DB_CONFIG_NOW');
		
		$actid = I("actid");
		$activityShow = $activityModel->where("actid = '%s'",$actid)->find();
		$activityShow["picture"] = $actPictureModel->field("picid")->where("actid='%s'",$activityShow["actid"])->select();
		//是否已经报名
		$data['snumber'] = session("snumber");
		if($data['snumber'] != NULL){
			$data['actid'] = $actid;
			
			$register = $registerModel->where($data)->find();
			if($register != NULL){
				$this->assign("isApply",1);
			}else{
				$this->assign("isApply",0);
			}			
		}else{ //用户的身份是游客
			$this->assign("isApply",-1);
		}
		//活动已经结束
		if($activityShow['state'] == -1){
			$statistics = $registerModel->join('nl_questionnaire on question_id = qid')->where('actid=%d',$actid)->select();
			//活动评价统计数据
			$statis['content'] = 0;
			$statis['teacher'] =0;
			$statis['recommend'] = 0;
			if($statistics != NULL){
				$num = count($statistics);
				for($i=0;$i<$num;$i++){
					$statis['content'] += (100-($statistics[$i]['value1']-1)*20);
					$statis['teacher'] += (100-($statistics[$i]['value2']-1)*20);
					$statis['recommend'] += (100-($statistics[$i]['value3']-1)*20);
				}
				$statis['content'] = $statis['content']/$num;
				$statis['teacher'] =$statis['teacher']/$num;
				$statis['recommend'] = $statis['recommend']/$num;
			}
			$this->assign("content",$statis['content']);
			$this->assign("teacher",$statis['teacher']);
			$this->assign("recommend",$statis['recommend']);
			//活动印象
			$impress = $registerModel->join('nl_questionnaire on question_id = qid')->where('actid=%d',$actid)->limit(10)->getField("impress",true);
			$this->assign("impress",$impress);
		}
		
		$this->assign("activityShow",$activityShow);
		$this->display("front_activity");
	}
	
	public function activityApply(){
		
		//获取活动ID并进行注册
		$registerModel = M('Register','nl_','DB_CONFIG_NOW');
		$userModel = M('Student','nl_','DB_CONFIG_NOW');
		$activityModel = M('Activity','nl_','DB_CONFIG_NOW');
		
		$data['actid'] = I("actid");
		$data['snumber'] = session("snumber");
		
		//身份为游客
		if(session("user")!=1){
			$touristModel = M('Tourist','nl_','DB_CONFIG_NOW');
			$touristRegisterModel = M('TouristRegister','nl_','DB_CONFIG_NOW');
			//是否已经报名
			$openid = get_openid();
			$map['actid'] = $data['actid'];
			$map['topenid'] = $openid;
			$t_register = $touristRegisterModel->where($map)->find();
			if($t_register != NULL){
				$this->error("您已经报名此活动！");
				return;
			}
			$activity = $activityModel->where("actid = '%d'",$data['actid'])->find();
			if($activity['state'] !=0){
				$this->error("该活动已经过了报名的截止日期~");
				return;
			}
			if($activity['maxnumber'] <= $activity['peoplenumber']){
				$this->error("对不起~报名的人数已达上限！");
				return;
			}
			//是否被封杀
			$tourist = $touristModel->where('openid = "%s"',$openid)->find();
			if($tourist != NULL && $tourist['state'] == -1 ){
				$this->error("由于您多次报名却未参加，您已经被禁止报名活动~");
				return;
			}
			//添加个人信息和报名信息
			if($tourist == NULL){
				$data1['openid'] = $openid;
				$data1['name'] = get_nickname1($openid,get_token());
				$data1['headurl'] = get_headurl($openid,get_token());
				 $dbCheck = $touristModel->add($data1);
				 if(!$dbCheck){
					$this->error("数据库更新失败！");
					return;
				}
			}

			$dbCheck = $touristRegisterModel->add($map);
			if(!$dbCheck){
					$this->error("数据库更新失败！");
					return;
			}
			$activityModel->where("actid = '%d'",$data['actid'])->setInc("peoplenumber");

			$this->Mysuccess("您已经成功报名<strong>".$activity['topic']."</strong>");
			return;
		}
		//身份为翼鲲班成员
		$register = $registerModel->where($data)->find();
		if($register != NULL){
			$this->error("您已经报名此活动！");
			return;
		}
		$activity = $activityModel->where("actid = '%d'",$data['actid'])->find();
		if($activity['state'] !=0){
			$this->error("该活动已经过了报名的截止日期~");
			return;
		}
		if($activity['maxnumber'] <= $activity['peoplenumber']){
			$this->error("对不起~报名的人数已达上限！");
			return;
		}
		$data['state'] = 0;
		$data['topic'] = $activity['topic'];

		$dbcheck = $registerModel->add($data);
		if(!$dbcheck){
			$this->error("数据库更新失败！");
			return;
		}
		$dbcheck = $userModel->where('snumber = "%s"',$data['snumber'])->setInc('actapply');
		if(!$dbcheck){
			$this->error("数据库更新失败！");
			return;
		}
		$dbcheck = $activityModel->where("actid = '%d'",$data['actid'])->setInc("peoplenumber");
		if(!$dbcheck){
			$this->error("数据库更新失败！");
			return;
		}
		$this->mySuccess("您已经成功报名<strong>".$activity['topic']."</strong>");
	}
	
	public function actAssign(){
		$userModel = M('Student','nl_','DB_CONFIG_NOW');
		$registerModel = M('Register','nl_','DB_CONFIG_NOW');
		
		$touristModel = M('Tourist','nl_','DB_CONFIG_NOW');
		$touristRegisterModel = M('TouristRegister','nl_','DB_CONFIG_NOW');

		$openid = get_openid();
		if($openid == -1){
			$this->error("请使用微信的扫一扫！");
			return;
		}
		
		$actid = I('actid');
		$id = I('id');
		if($id != $openid){
			$this->error("不是本人的签到二维码！");
			return;
		}
		$student = $userModel->where("openid = '%s'",$openid)->find();
		if($student == NULL){
			$tourist = $touristModel->where("openid = '%s'",$openid)->find();
			if($tourist == NULL){
				$this->error("你是谁？");
				return;
			}else{ 	//游客签到（增加参加活动次数、将参与状态变为1）
				$t_register = $touristRegisterModel->where('topenid = "%s" AND actid = %d',$openid,$actid)->find();
				if($t_register['state'] == 1){
					$this->error("你已经签过了~");
					return;
				}
				$touristModel->where('openid = "%s"',$openid)->setInc('attend');
				$touristRegisterModel->where('topenid = "%s" AND actid = %d',$openid,$actid)->setField('state',1);

				$this->Mysuccess("本次活动签到成功!");
			}
			
		}
		$register = $registerModel->where('actid = %d AND snumber = "%s"',$actid,$student['snumber'])->find();
		if($register['taskcomplete']>=1){
			$this->error("你已经签过了~");
			return;
		}

		$data['taskcomplete'] = 1;
		$data['attendtime'] =  date('Y-m-d H:i:s',time());
		
		$dbcheck = $registerModel->where('snumber = "%s" AND actid = %d',$student['snumber'],$actid)->save($data);
		if(!$dbcheck){
			$this->error("数据库连接错误！");
			return;
		}
		$this->Mysuccess("本次活动签到成功，活动结束后记得去个 人中心-我的活动完成问卷哦！");
	}
	
	public function questionnaire(){
		$action = I('action');
		$actid = I('actid');
		$snumber = session('snumber');
		$registerModel = M('Register','nl_','DB_CONFIG_NOW');
		$activityModel = M('Activity','nl_','DB_CONFIG_NOW');

		if($actid ==NULL){
			$this->error("错误的操作！");
			return;
		}
		switch($action){
			case "show":
				//权限检查
				$map['snumber'] = $snumber;
				$map['actid'] = $actid;
				$register = $registerModel->where($map)->find();
				$activity = $activityModel->where('actid = %d',$actid)->find();
				if($activity == NULL || $activity == false){
					$this->error("错误的操作！");
					return;
				} 
				if($activity['state'] == 0){
					$this->error("该活动暂不能做问卷！");
					return;
				}else if($activity['state'] == -1){
					$this->error("管理员已经结束活动！");
					return;
				}
				if($register['taskcomplete'] == 0){
					$this->error("还没有签到！");
					return;
				}
				if($register['state'] == 1){
					$this->error("已经完成问卷！");
					return;
				}
				$this->assign("actid",$actid);
				$this->display('front_questionnaire');
				break;
			case "submit":
				$questionnaireModel = M("Questionnaire","nl_","DB_CONFIG_NOW");
				$userModel = M('Student','nl_','DB_CONFIG_NOW');
				$activity = $activityModel->where('actid = %d',$actid)->find();
				
				//权限检查（签到了没）
				$map['snumber'] = $snumber;
				$map['actid'] = $actid;
				$register = $registerModel->where($map)->find();
				if($register['state'] == 1){
					$this->error("已经完成问卷！");
					return;
				}
				//接受问卷参数、记录问卷答案
				$data['impress'] = I('question_impress');
				if($data['impress']== ''||$data['impress'] == NULL){
					$this->error("请填写您的印象！");
					return;
				}
				$data['value1'] = I('choice-1');
				$data['value2'] = I('choice-2');
				$data['value3'] = I('choice-3');
				$data['teacher'] = $activity['teacher'];
				$questionId  = $questionnaireModel->add($data);
				if($questionId == false){
					$this->error("更新数据库错误！");
					return;
				}
				//问卷答案关联相应的活动
				$data1['question_id'] = $questionId;
				$data1['state'] = 1;
				$data1['taskcomplete']  = 2;
				$dbcheck = $registerModel->where($map)->save($data1);
				if(!$dbcheck){
					$this->error("更新数据库错误2！");
					return;
				}
				//更新参加活动次数和连续缺席活动次数
				$userModel->where('snumber = "%s"',$snumber)->setInc('actattend');
				$userModel->where('snumber = "%s"',$snumber)->setField('absent',0);
				//填写自己的心得
				$this->MyExperience($actid);
				
				break;
			default:
				$this->error("呵呵，错误的请求~");
				return ;
		}
	
	}

	public function experience(){
		$experience = I('experience');
		$actid = I('actid');

		if($experience == NULL ||$experience== ''){
			$this->error("没有内容！");
			return;
		}
		if($actid == NULL ||$actid== ''){
			$this->error("没有活动ID");
			return;
		}
		$registerModel = M('Register','nl_','DB_CONFIG_NOW');
		$snumber = session('snumber');
		$map['actid'] = $actid;
		$map['snumber'] = $snumber;
		$data['experience'] = $experience;
		$registerModel->where($map)->save($data);

		$this->mySuccess("心得保存成功！");
	}

	protected function MyExperience($actid){
		$this->assign("actid",$actid);
		$this->display("front_experience");
	}




/*=========================================================================
	 *@codeBlock:: 后台管理
   ****  *******    *        ******   *******
   *        *      *  *      *    *      *
   ****     *     ******     ******      *
      *     *    *      *    *  *        *
   ****     *   *        *   *    *      * 

=============================================================================
	public function manageIndex(){
		$this->checkActivityTime();		//超期活动检查
		$this->display("manage_login");
	}
	public  function manageLogin(){
		session_start();	
		$password = I("password");
		if($password != "12345"){
			$this->assign("error","密码错误！");
			$this->display("manage_login");
		}else{
			session("manageLogin",1);
			redirect('/Tool/index.php/Home/Yikun/manage');
		}

	}
	//各个管理页面显示
	public function manage($action=""){
			//身份验证
		if(!($this->checkLogin())){
			$this->error("您没有登录！");
			return;
		}

			//显示相应页面
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
		//身份验证
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
				//完成任务的(需要跟新的数据在学员完成任务时已经进行)
				
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

		//在数据库中添加活动
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
		
		//在数据库中添加活动图片
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
		//跟新活动内容
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

		//在数据库中添加活动图片
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

===============================================================
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