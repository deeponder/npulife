<?php

namespace Home\Controller;
use Think\Controller;

class QuestionnaireController extends Controller{
	public function index(){
		$surveyModel = M('SurveyAnswer','nl_','DB_CONFIG');
		//问题1
		$undergraduate = $surveyModel->where('question_id = %d AND answer ="%s"',1,'s:1:"0";')->count();
		$graduate = $surveyModel->where('question_id = %d AND answer ="%s"',1,'s:1:"1";')->count();
		//问题2
		$num2_0 = $surveyModel->where('question_id = %d AND answer ="%s"',2,'s:1:"0";')->count();
		$num2_1 = $surveyModel->where('question_id = %d AND answer ="%s"',2,'s:1:"1";')->count();
		$num2_2 = $surveyModel->where('question_id = %d AND answer ="%s"',2,'s:1:"2";')->count();
		$num2_3 = $surveyModel->where('question_id = %d AND answer ="%s"',2,'s:1:"3";')->count();
		
		//问题4
		$answer4s = $surveyModel->where('question_id = %d',4)->select();
		$num4_0 = 0;
		$num4_1 = 0;
		$num4_2 = 0;
		$num4_3 = 0;
		$num4_4 = 0;
		$num4_5 = 0;
		$num4_6 = 0;
		
		foreach ($answer4s as $answer4){
			
			$answer = unserialize($answer4['answer']);
			foreach($answer as $item){
				switch($item){
					case 0:
						$num4_0++;
						break;
					case 1:
						$num4_1++;
						break;
					case 2:
						$num4_2++;
						break;
					case 3:
						$num4_3++;
						break;
					case 4:
						$num4_4++;
						break;
					case 5:
						$num4_5++;
						break;
					case 6:
						$num4_6++;
						break;
				}
			}
		}
		
		
		//1
		$this->assign("undergraduate",$undergraduate);
		$this->assign("graduate",$graduate);
		//2
		$this->assign("num2_0",$num2_0);
		$this->assign("num2_1",$num2_1);
		$this->assign("num2_2",$num2_2);
		$this->assign("num2_3",$num2_3);
		//4
		$this->assign("num4_0",$num4_0);
		$this->assign("num4_1",$num4_1);
		$this->assign("num4_2",$num4_2);
		$this->assign("num4_3",$num4_3);
		$this->assign("num4_4",$num4_4);
		$this->assign("num4_5",$num4_5);
		$this->assign("num4_6",$num4_6);
		
		$this->display();
		
	}
	
}

?>