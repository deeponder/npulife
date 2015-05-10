<?php 
namespace Home\Controller;
use Think\Controller;
class CheckinfoController extends Controller{
public function index()
{
$info = M('news_info','nl_','DB_CONFIG1');
$articleList = $info ->order('id desc')->select();
$sMap['status']=1;
$is_check = $info->where($sMap)->getField('id',true);
$this->assign("is_checked",$is_check);
$this->assign("newstitles",$articleList);
$this->display();
}
public function check()
{
    $c = $_GET['data'];
    $str=array();
	$str = explode(";",$c);
	$a=$str[0];
	$b=$str[1];
	$id = intval($a);
	$is_checked = intval($b);
	$info = M('news_info','nl_','DB_CONFIG1');
	$checked = M('checked_info','nl_','DB_CONFIG1');
	if($is_checked=='1')
	{
	$sMap['id'] = $id;
	$news = $info->where($sMap)->find();
	$news['status']=1;
	$info->save($news);
	$add['title'] = $news['news_title'];
	$add['content'] = $news['news_content'];
	$add['news_first_img'] = $news['news_first_img'];
	$add['news_origin'] = $news['news_origin'];
	$add['source_url'] = $news['news_source'];
	$add['old_id'] = $news['id'];
	$time = time();
	//$s="success"
	$add['ctime'] = $time;
	$checked->add($add);
	//dump($s);
	//echo $news['news_title'];
	}
	else
	{
	$sMap['id'] = $id;
	$news = $info->where($aMap)->find();
	$news['status']=0;
	$info->save($news);
	$vMap['old_id'] = $id;
	$checked->where($vMap)->delete();
	}
}
public function newslist($newslistid)
  {				
				$NewsInfo = M('news_info','nl_','DB_CONFIG1');
			  $res = $NewsInfo->where('id=%d',$newslistid)->select();
			//print_r($res);
			$this->assign("newsinfo",$res);
			$this->display('content');	
	}
}
?>