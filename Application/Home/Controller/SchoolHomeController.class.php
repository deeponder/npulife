<?php
// 本类由系统自动生成，仅供测试用途
namespace Home\Controller;
class SchoolHomeController extends HomeController {
    public function index(){
		$openId = get_openid();
		if($openId != -1){
			$aamap['token'] = get_token ();
			$aamap['is_show'] = 1;
			$slideshow = M ( 'weisite_slideshow' )->where ( $aamap )->order ( 'sort desc, id desc' )->limit(3)->select ();
			foreach ( $slideshow as &$vo ) {
				$vo ['img'] = get_cover_url ( $vo ['img'] );
			}
			$this->assign ( 'slideshow', $slideshow );		
			// SELECT cate_id, max( cTime ) ct FROM `nl_custom_reply_news`  GROUP BY cate_id
			$readStar = M('CustomReplyNews')->field('cate_id, max( cTime ) ct')->group(cate_id)->select();//得到各栏目更新时间
			
			$CategoryViewDate = M('CategoryViewDate');
			
			$starIn = array();

			foreach($readStar as $astar)
			{
				$sname = $astar['cate_id'];//栏目ID，然后应该用栏目ID和用户登录该栏目的最后时间做比较
				
				$bbMap['openid'] = get_openid();
				$bbMap['category_id'] = $sname;
				$lastLogin = $CategoryViewDate->where($bbMap)->find();
				
				if($lastLogin)
				{
					$lastViewTime = strtotime($lastLogin['last_view_date']);
					
					if($lastViewTime<$astar["ct"]) 
					{
						$starIn[$sname] = true;
					}
					else 
					{
						$starIn[$sname] = false;
					}
				}
				else
				{
					$starIn[$sname] = true;
				}
						
			}
			
			//$rstar = "<font color='red' >SSS</font>";
			
			$this->assign ( 'starIn', $starIn);	
			$this->assign('rstar','<font color="red">·</font>');
		}
	
		$this->display();
    }
	
	
}

