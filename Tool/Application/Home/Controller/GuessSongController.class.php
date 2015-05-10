<?php

namespace Home\Controller;
use Think\Controller;
class GuessSongController extends Controller {
    
	public function play(){
		
		$this->display();
    }
	
	public function ajaxSubmitAnswer()
	{
		$Voice = M ( 'Voice','nl_','DB_CONFIG_NPULIFE_DATA');
		$vMap['status'] = 1;
		$voiceList = $Voice->where($vMap)->select();
		
		$theSongId = rand(0,count($voiceList)-1);
		$theSong = $voiceList[$theSongId];
		
		$result = $theSong;
		$this->ajaxReturn($result,"JSON");
	}
}