<?php

namespace Addons\WeiSite\Controller;

use Addons\WeiSite\Controller\BaseController;

class WeiSiteController extends BaseController {
	function config() {
		// 使用提示
		$normal_tips = '在微信里回复“微官网”即可以查看效果，也可点击<a target="_blank" href="' . U ( 'index' ) . '">这里</a>在预览';
		$this->assign ( 'normal_tips', $normal_tips );
		
		if (IS_POST) {
			$flag = D ( 'Common/AddonConfig' )->set ( _ADDONS, $_POST ['config'] );
			
			if ($flag !== false) {
				$this->success ( '保存成功', Cookie ( '__forward__' ) );
			} else {
				$this->error ( '保存失败' );
			}
			exit ();
		}
		
		parent::config ();
	}
	// 首页
	function index() {
		$map ['token'] = get_token ();
		$map ['is_show'] = 1;
		
		// 幻灯片
		$slideshow = M ( 'weisite_slideshow' )->where ( $map )->order ( 'sort asc, id desc' )->select ();
		foreach ( $slideshow as &$vo ) {
			$vo ['img'] = get_cover_url ( $vo ['img'] );
		}
		$this->assign ( 'slideshow', $slideshow );
		// dump($slideshow);
		
		// 分类
		$category = M ( 'weisite_category' )->where ( $map )->order ( 'sort asc, id desc' )->select ();
		foreach ( $category as &$vo ) {
			$vo ['icon'] = get_cover_url ( $vo ['icon'] );
		}
		$this->assign ( 'category', $category );
		// dump($category);
		
		$this->_footer ();
		$this->display ( ONETHINK_ADDON_PATH . 'WeiSite/View/default/TemplateIndex/' . $this->config ['template_index'] . '/index.html' );
	}
	// 分类列表
	function lists() {
		$map ['token'] = get_token ();
		if (isset ( $_REQUEST ['cate_id'] )) {
			$map ['cate_id'] = intval ( $_REQUEST ['cate_id'] );
		}
		
		$page = I ( 'p', 1, 'intval' );
		$row = isset ( $_REQUEST ['list_row'] ) ? intval ( $_REQUEST ['list_row'] ) : 20;
		
		$data = M ( 'custom_reply_news' )->where ( $map )->order ( 'sort asc, id DESC' )->page ( $page, $row )->select ();
		/* 查询记录总数 */
		$count = M ( 'custom_reply_news' )->where ( $map )->count ();
		$list_data ['list_data'] = $data;
		
		// 分页
		if ($count > $row) {
			$page = new \Think\Page ( $count, $row );
			$page->setConfig ( 'theme', '%FIRST%  %UP_PAGE%  %LINK_PAGE%  %DOWN_PAGE%  %END% %HEADER%' );
			$list_data ['_page'] = $page->show ();
		}
		
		// 修改登录栏目时间
		$vMap['openid'] = get_openid();
		$vMap['category_id'] = $map ['cate_id'];
		$CategoryViewDate = M('CategoryViewDate');
		$viewDate = $CategoryViewDate->where($vMap)->find();
		if($viewDate)
		{
			$viewDate['openid'] = get_openid();
			$viewDate['category_id'] = $map ['cate_id'];
			$viewDate['last_view_date'] = date("Y-m-d G:i:s");
			$CategoryViewDate->save($viewDate);
		}
		else
		{
			$viewDate['openid'] = get_openid();
			$viewDate['category_id'] = $map ['cate_id'];
			$viewDate['last_view_date'] = date("Y-m-d G:i:s");
			$CategoryViewDate->add($viewDate);
		}
		
		$this->assign ( $list_data );
		// dump ( $list_data );
		
		$this->_footer ();
		$this->display ( ONETHINK_ADDON_PATH . 'WeiSite/View/default/TemplateLists/' . $this->config ['template_lists'] . '/lists.html' );
	}
	
	function splists() {//xuhuang 特殊列表，排行榜 
		$map ['token'] = get_token ();		
		
		$page = I ( 'p', 1, 'intval' );
		$row = isset ( $_REQUEST ['list_row'] ) ? intval ( $_REQUEST ['list_row'] ) : 10;
		
		$data = M ( 'custom_reply_news' )->limit(10)->where ( $map )->order ( 'view_count DESC' )->page ( $page, $row )->select ();
		/* 查询记录总数 */
		$count = 10;
		$list_data ['list_data'] = $data;
		
		// 分页
		if ($count > $row) {
			$page = new \Think\Page ( $count, $row );
			$page->setConfig ( 'theme', '%FIRST%  %UP_PAGE%  %LINK_PAGE%  %DOWN_PAGE%  %END% %HEADER%' );
			$list_data ['_page'] = $page->show ();
		}
		
		$this->assign ( $list_data );
		// dump ( $list_data );
		
		$this->_footer ();
		$this->display ( ONETHINK_ADDON_PATH . 'WeiSite/View/default/TemplateLists/' . $this->config ['template_lists'] . '/lists.html' );
	}
	// 详情
	function detail() {
		$id = I ( 'get.id', 0, 'intval' );
		$map ['id'] = $id;
		$info = M ( 'custom_reply_news' )->where ( $map )->find ();
		$this->assign ( 'info', $info );
		
		$mData['openid'] = get_openid();
		
		$theUser = M('Member')->where($mData)->find();
		if($theUser){
			$uid = $theUser['uid'];
			action_log('read_article','member',$uid,$uid);
		}
		
		//如果是优惠抢先文章，就……领取优惠券（您将收到一条微信消息）
		if($info['cate_id']==15)
		{
			$openid = get_openid();
			$url = "/Home/SendTool/sendYouhui?openid=".$openid."&sjid=".$info['id'];
			$youhui = "<a href='".$url."'>戳这！领取优惠券~您将收到一条微信消息作为优惠券，凭券获得优惠</a>";
			$this->assign('youhui',$youhui);
		}
		if($info['cTime'] >1400577256 ) 
		   $this->assign('diLogo','<img alt="" src="/Uploads/Editor/2014-05-20/537af89ad9eee.jpg" /> ');
		else $this->assign('diLogo',false);
		M ( 'custom_reply_news' )->where ( $map )->setInc ( 'view_count' );
		
		$this->_footer ();
		C('WEB_SITE_TITLE',$info['title']);
		
		//$this->display ( ONETHINK_ADDON_PATH . 'WeiSite/View/default/TemplateDetail/' . $this->config ['template_detail'] . '/detail.html' );
		redirect('/index.php/Home/NewSchoolHome/detail/id/'.$id);
	}
	
	// 3G页面底部导航
	function _footer() {
		$list = D ( 'Addons://WeiSite/Footer' )->get_list ();
		
		// 取一级菜单
		foreach ( $list as $k => $vo ) {
			if ($vo ['pid'] != 0)
				continue;
			
			$one_arr [$vo ['id']] = $vo;
			unset ( $list [$k] );
		}
		
		foreach ( $one_arr as &$p ) {
			$two_arr = array ();
			foreach ( $list as $key => $l ) {
				if ($l ['pid'] != $p ['id'])
					continue;
				
				$two_arr [] = $l;
				unset ( $list [$key] );
			}
			
			$p ['child'] = $two_arr;
		}
		
		$this->assign ( 'footer', $one_arr );
		$html = $this->fetch ( ONETHINK_ADDON_PATH . 'WeiSite/View/default/TemplateFooter/' . $this->config ['template_footer'] . '/footer.html' );
		
		$this->assign ( 'footer_html', $html );
	}
}
