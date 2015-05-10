<?php
namespace Home\Controller;
use Think\Controller;
class CheatController extends Controller
{
	public function index()
	{
	
	}
	Public function viewCount()
	{
		$reply_news = M("Custom_reply_news");
		$news=$reply_news->select();
		
		foreach ($news as $n)
		{
		if($n[view_count]>1000) $n[view_count]=$n[view_count]+3;
		else $n[view_count]=$n[view_count]+rand(50,100);
		
		$reply_news->save($n);

		}	
		echo("Well,done.");
	}
}