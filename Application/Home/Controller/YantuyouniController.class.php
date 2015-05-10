<?php
// 本类由系统自动生成，仅供测试用途
namespace Home\Controller;
use Think\Controller;
class YantuyouniController extends Controller {
    public function index(){
		$openId = get_openid();
		if($openId != -1){
			$aamap['token'] = get_token ();
			$aamap['is_show'] = 1;
			$slideshow = M ( 'weisite_slideshow' )->where ( $aamap )->order ( 'sort asc, id desc' )->limit(4)->select ();
			foreach ( $slideshow as &$vo ) {
				$vo ['img'] = get_cover_url ( $vo ['img'] );
			}
			$this->assign ( 'slideshow', $slideshow );		
			
		}
	
		$this->display();
    }
	public function newscatogory($cate,$all){
	
			$WeixinNews = D('WeixinNews');
			$NewsInfo = D('NewsInfo');
			$resGf;
				switch ($cate)
			{
			case "重要通知":			  
			  $this->assign('title',"重要通知");
			  $resGf = $NewsInfo->where('new_column=%d',$cate)->field('id,news_title')->select();
			  break;
			default:
			  $this->assign('title',$cate);
			}
			$res;
			if($all==0){
			$res = $WeixinNews->where("Subject='%s'",$cate)->field('news_id,title,url')->order('Createdate desc')->limit(10)->select();
			}
			else
			{
			$res = $WeixinNews->where("Subject='%s'",$cate)->field('news_id,title,url')->order('Createdate desc')->select();
			}
			
			//print_r($res);
			//$map['news_first_img']  = array('like',"%nwpu%");
			//$map['new_column']  = array('neq',4);//通知公告不显示图片
			//$resImg = $NewsInfo->where('new_column=%d',$cate)->where($map)->limit(3)->field('id,news_title,news_first_img')->select();
			//print_r($res);
			$this->assign("newstitles",$res);
			//$this->assign("newimages",$resImg);
			$this->assign("cate",$cate);
			$this->assign("newstitlesGf",$resGf);
			$this->assign("allinfo",$all);
			$this->display('list');
	}
	
}

