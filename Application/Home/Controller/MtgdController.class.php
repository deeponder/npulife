<?php
// 本类由系统自动生成，仅供测试用途
namespace Home\Controller;
use Think\Controller;
class MtgdController extends Controller {

    public function index(){
	$this->assign('title',"媒体工大");
	$NewsMtgd = D('NewsMtgd');
	//$res = $NewsMtgd->field('id,news_title,news_source')->select();
	$res = $NewsMtgd->limit(0,20)->order('id desc')->select();
	$this->assign("mtgdList",$res);
	$this->display();
    }	
}
?>