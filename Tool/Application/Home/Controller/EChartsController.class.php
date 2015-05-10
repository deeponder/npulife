<?php
namespace Home\Controller;
use Think\Controller;
class EChartsController extends Controller {

	public function index(){
		$answer = M('survey_answer','nl_','DB_CONFIG1');

		$users = $answer->group('uid')->select();

		$all = $answer->select();

		foreach ($all as $a) {
			dump('1');
		}
		

		// $this->display();

	}

	

		
}