<?php
// 本类由系统自动生成，仅供测试用途
namespace Home\Controller;
use Think\Controller;
class NewsController extends Controller {
    public function index(){
	$this->assign('title',"新闻关注");
	$this->display();
    }

	public function newscatogory($cate){
	
			$NewsInfo = D('NewsInfo');
				switch ($cate)
			{
			case 0:			  
			  $this->assign('title',"工大要闻");
			  break;
			case 1:
			  $this->assign('title',"校园新闻");
			  break;
			case 2:
			   $this->assign('title',"摄影报道");
			  break;
			  case 3:
			   $this->assign('title',"特约评论");
			  break;
			  case 4:
			   $this->assign('title',"通知公告");
			  break;
			  case 5:
			   $this->assign('title',"真情约稿");
			  break;
			  case 6:
			   $this->assign('title',"校园风光");
			  break;
			 case 7:
			   $this->assign('title',"我与视窗");
			  break;
			default:
			  echo "Error";
			}
			$res = $NewsInfo->where("new_column=$cate")->field('id,news_title')->order('id desc')->select();
			$map['news_first_img']  = array('like',"%nwpu%");
			$map['new_column']  = array('neq',4);//通知公告不显示图片
			$resImg = $NewsInfo->where('new_column=%d',$cate)->where($map)->limit(3)->field('id,news_title,news_first_img')->order('id desc')->select();
			//print_r($res);
			$this->assign("newstitles",$res);
			$this->assign("newimages",$resImg);
			$this->assign("cate",$cate);
			$this->display('list');
			
			
	}
	
	public function newslist($newslistCate,$newslistid){				
				$NewsInfo = D('NewsInfo');
			  $res = $NewsInfo->where('id=%d',$newslistid)->select();
			//print_r($res);
			$this->assign("newsinfo",$res);
			$this->display('content');
			
			
	}
}