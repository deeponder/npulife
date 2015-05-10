<?php
// 包括原创内容页面，也包括聚合内容页面，聚合内容从各个订阅号抓取而来。
// 1.0版不能私人定制，2.0版可以自选订阅号。
namespace Home\Controller;
class ContentHomeController extends HomeController {
    
	//原创内容页。
	public function myIndex(){
		$time = time();
		$Source = M('CustomReplyNews');
		$sMap['cTime'] = array('lt',$time);
		$articleList = $Source->field('id,cate_id,title,cTime,view_count')->where($sMap)->order('cTime desc')->limit(0,5)->select();
		$Cate = M('WeisiteCategory');
		for($i=0;$i<count($articleList);$i++)
		{
			$cMap['id'] = $articleList[$i]['cate_id'];
			$c = $Cate->where($cMap)->find();
			$articleList[$i]['cate_name'] = $c['title'];//date('Y-m-d G:i:s',$item['cTime']);
		}
		$this->articleList = $articleList;
		$this->time = $time;
		$this->display();
    }
	//聚合内容页。
	public function otherIndex() {
		$time = time();
		$Source = M('PaArticle','nl_','DB_CONFIG_NPULIFE_DATA');
		$sMap['pubdate'] = array('lt',$time);
		$articleList = $Source->field('id,wxname,title,pubdate,view_count,url')->where($sMap)->order('pubdate desc')->limit(0,10)->select();
		$this->articleList = $articleList;
		$this->time = $time;
		$this->display();
	}
	//原创通过AJAX加载更多。
	public function getMoreMyArticles() {
		$page = intval(I('get.page'));
		$time = intval(I('get.time'));
		$num = 5;
		$Source = M('CustomReplyNews');
		$sMap['cTime'] = array('lt',$time);
		$articleList = $Source->field('id,cate_id,title,cTime,view_count')->where($sMap)->order('cTime desc')->limit($page*$num,$num)->select();
		
		$Cate = M('WeisiteCategory');
		for($i=0;$i<count($articleList);$i++)
		{
			$cMap['id'] = $articleList[$i]['cate_id'];
			$c = $Cate->where($cMap)->find();
			$articleList[$i]['cate_name'] = $c['title'];
			$articleList[$i]['pubdate'] = date('Y-m-d G:i:s',$articleList[$i]['cTime']);
		}
		$result['status'] = 1;
		$result['data'] = $articleList;
		$this->ajaxReturn($result,'JSON');
	}
	//聚合通过AJAX加载更多。
	public function getMoreOtherArticles() {
		$page = intval(I('get.page'));
		$time = intval(I('get.time'));
		$num = 10;
		$Source = M('PaArticle','nl_','DB_CONFIG_NPULIFE_DATA');
		$sMap['pubdate'] = array('lt',$time);
		$articleList = $Source->field('id,wxname,title,pubdate,view_count,url')->where($sMap)->order('pubdate desc')->limit($page*$num,$num)->select();
		for($i=0;$i<count($articleList);$i++)
		{
			$articleList[$i]['pubdate'] = date('Y-m-d G:i:s',$articleList[$i]['pubdate']);
		}
		$result['status'] = 1;
		$result['data'] = $articleList;
		$this->ajaxReturn($result,'JSON');
	}
	
	public function myDetail()
	{
		$map['id'] = I('get.id');
		$CustomReplyNews = M("CustomReplyNews");
		$news = $CustomReplyNews->where($map)->find();
		
		$news['create_date'] = date('Y-m-d G:i:s',$news['cTime']);
		
		//新增点击量
		$news['view_count']++;
		$CustomReplyNews->save($news);
		
		$news['pic'] = get_cover_url ( $news ['cover'] );
		$this->assign("news",$news);
		
		$this->display();
	}
	
	public function otherDetail()
	{
		$map['id'] = I('get.id');
		$Source = M('PaArticle','nl_','DB_CONFIG_NPULIFE_DATA');
		$news = $Source->where($map)->find();
		
		//新增点击量
		$news['view_count']++;
		$Source->save($news);
		
		$this->assign("news",$news);
		
		$this->display();
	}
	
	public function search()
	{
		$keyword = I('post.keyword');
		
		$CustomReplyNews = M("CustomReplyNews");
		$map['title'] = array('like',"%".$keyword."%");		
		$newsList = $CustomReplyNews->where($map)->select();
		
		$this->assign("newsList",$newsList);
		
		//获得头像
		$token = get_token();
		$openid = get_openid();		
		$userinfo = getWeixinUserInfo($openid, $token);
		$this->assign("headPic",$userinfo['headimgurl']);
		
		$this->display();
	}
}