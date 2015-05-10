<?php

namespace Home\Controller;

/**
 * 私人定制
 * 主要获取和反馈微信平台的数据
 */
class FiController extends HomeController {
    public function index(){
	     
	     $test = D('Member')->limit(5)->select();
		 $this->assign("qaz",$test);
		 $this->display();
		}
}

?>