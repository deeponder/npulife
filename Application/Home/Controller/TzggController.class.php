<?php
// 本类由系统自动生成，仅供测试用途
namespace Home\Controller;
use Think\Controller;
class TzggController extends Controller {

    public function index(){
	$this->assign('title',"通知公告");
	$NewsTzgg = D('NewsTzgg');
	//$res = $NewsTzgg->field('id,news_title')->select();
	$res = $NewsTzgg->limit(0,20)->order('id desc')->select();
	$this->assign("tzggList",$res);
	$this->display();
    }
	
	public function newslist($newslistCate,$newslistid){				
				$NewsTzgg = D('NewsTzgg');
			  $res = $NewsTzgg->where('id=%d',$newslistid)->select();
			//print_r($res);
			$this->assign("newstzgg",$res);
			$this->display('content');

	}
}
