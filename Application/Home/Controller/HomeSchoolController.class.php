<?php
namespace Home\Controller;
use Think\Controller;
class HomeSchoolController extends Controller
{
   public function index()
	{
	  $news_table = M('checked_info','nl_','DB_CONFIG1'); 
	  $time = time();
	 $sMap['ctime'] = array('lt',$time);
	  $articleList =$news_table->order('ctime desc')->limit(0,3)->select();
	  $this->articleList = $articleList;
	  $this->time = $time;
	 // dump($articleList);
	 $NewsTzgg = M('news_tzgg','nl_','DB_CONFIG1');
	$res = $NewsTzgg->order('id desc')->limit(0,3)->select();
	$this->assign("tzggList",$res); 
	$NewsMtgd = M('NewsMtgd','nl_','DB_CONFIG1');
	//$res = $NewsMtgd->field('id,news_title,news_source')->select();
	$res = $NewsMtgd->order('id desc')->limit(0,3)->select();
	$this->assign("mtgdList",$res);
	  $this->display();
    }
  public function newslist1()
  {				$newslistid = intval(I('get.newslistid'));
				$NewsInfo = M('checked_info','nl_','DB_CONFIG1');
			  $res = $NewsInfo->where('id=%d',$newslistid)->select();
			//print_r($res);
			$this->assign("newsinfo",$res);
			$this->display('content');	
	}
	 public function newslist2()
  {				$newslistid = intval(I('get.newslistid'));
				$NewsInfo = M('NewsTzgg','nl_','DB_CONFIG1');
			  $res = $NewsInfo->where('id=%d',$newslistid)->select();
			//print_r($res);
			$this->assign("newstzgg",$res);
			$this->display('tzcontent');	
	}
	public function getMoreMyArticles() {
	$c=$_GET['ischecked'];   //二者中间有“,”隔开
	$str=array();
	$str = explode(";",$c);
	$a=$str[0];
	$b=$str[1];
    $page = intval($a);
	$time = intval($b);
		$num = 3;
	 $news_table = M('checked_info','nl_','DB_CONFIG1'); 
	 $time = time();
	// $sMap['ctime'] = array('lt',$time);
	  $articleList =$news_table->order('ctime desc')->limit($page*$num,3)->select();
	  $this->articleList = $articleList;
	  $this->time = $time;
		$result['status'] = 1;
		$result['data'] = $articleList;
		$this->ajaxReturn($result,'JSON');
	}
	public function getMoreTZ() {
	$c=$_GET['ischecked'];   //二者中间有“,”隔开
	$str=array();
	$str = explode(";",$c);
	$a=$str[0];
	$b=$str[1];
    $page = intval($a);
	$time = intval($b);
		$num = 3;
	 $NewsTzgg = M('NewsTzgg','nl_','DB_CONFIG1'); 
	 $time = time();
	// $sMap['ctime'] = array('lt',$time);
	  $articleList =$NewsTzgg->order('id desc')->limit($page*$num,3)->select();
	  //$this->articleList = $articleList;
	  $this->time = $time;
		//$result['status'] = 1;
		$result['data'] = $articleList;
		$this->ajaxReturn($result,'JSON');
	}
	public function getMoreMT() {
	$c=$_GET['ischecked'];   //二者中间有“,”隔开
	$str=array();
	$str = explode(";",$c);
	$a=$str[0];
	$b=$str[1];
    $page = intval($a);
	$time = intval($b);
		$num = 3;
	$NewsMtgd = M('NewsMtgd','nl_','DB_CONFIG1'); 
	 $time = time();
	// $sMap['ctime'] = array('lt',$time);
	  $articleList =$NewsMtgd->order('id desc')->limit($page*$num,3)->select();
	  //$this->articleList = $articleList;
	  $this->time = $time;
		//$result['status'] = 1;
		$result['data'] = $articleList;
		$this->ajaxReturn($result,'JSON');
	}
	public function search()
	{
		$keyword = I('post.keyword');
		
		$CustomReplyNews = M("CustomReplyNews");
		$map['title'] = array('like',"%".$keyword."%");		
		$newsList = $CustomReplyNews->where($map)->select();
		
		$this->assign("newsList",$newsList);
		
		// //获得头像
		// $token = get_token();
		// $openid = get_openid();		
		// $userinfo = getWeixinUserInfo($openid, $token);
		// $this->assign("headPic",$userinfo['headimgurl']);
		
		$this->display();
	}
}
?>