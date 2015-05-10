<?php

namespace Home\Controller;
use Think\Controller;

class GraduateController extends Controller{
	public function index(){
		
		$model = M('graduate','nl_','DB_CONFIG_NPULIFE_DATA');
		$articleList = $model->limit(4)->select();
		$this->articleList = $articleList;
		$this->display();
		
	}

	public function namelist(){
		$this->display();
	}

	public function gift(){
		$this->display();
	}

	public function getMore(){
		$page = I('page');
		$onePageNum = 4;

		$model = M('graduate','nl_','DB_CONFIG_NPULIFE_DATA');
		$articleList = $model->limit($page*$onePageNum,$onePageNum)->select();

		$this->ajaxReturn($articleList);
	}	
					
}

?>