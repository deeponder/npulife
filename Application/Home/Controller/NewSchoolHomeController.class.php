<?php
// 本类由系统自动生成，仅供测试用途
namespace Home\Controller;
class NewSchoolHomeController extends HomeController {
    public function index(){
		
		//读取滚动图片
		
		$gundongtupian = M ( 'weisite_slideshow' )->where ( $aamap )->order ( 'sort desc, id desc' )->limit(3)->select ();
		foreach($gundongtupian as &$vo){
			$vo['img'] = get_cover_url($vo ['img']);
		}
		$this->assign("gundongtupian",$gundongtupian);
		
		/*
		//先读取频道名，再读取频道相关的栏目名，再读取频道下混合的栏目新闻
		$ChannelList = M("SchoolhomeChannel")->select();
		$WebsiteCategory = M("WebsiteCategory");
		$CustomReplyNews = M("CustomReplyNews");
		
		foreach($ChannelList as $channel)
		{
			$cMap['channel_id'] = $channel['channel_id'];
			$channel['categoryList'] = $WebsiteCategory->where($cMap)->select();
			
			$channel['newsList'] = array();
			
			foreach($channel['categoryList'] as $category)
			{
				$chMap['cate_id'] = $category['id'];//$channel['channel_id'];
				$cList = $CustomReplyNews->where($chMap)->order('cTime desc')->limit(3)->select();
				$channel['newsList'] = array_merge($channel['newsList'],$cList);
			}
		}
		
		$this->assign("channelList",$channelList);
		*/
		
		$CustomReplyNews = M("CustomReplyNews");
		
		$map1['cate_id'] = array('in','1,2,3,4,5,6,7,8,9');
		$zaozhidao = $CustomReplyNews->where($map1)->order('cTime desc')->limit(6)->select();
		foreach($zaozhidao as $row)
		{
			$row['title'] = substr($row['title'],0,10)."..";
		}
		/*
		$map2['cate_id'] = array('in','1,5,6,7');
		$youshendu = $CustomReplyNews->where($map2)->order('cTime desc')->limit(3)->select();
		foreach($youshendu as $row)
		{
			$row['title'] = substr($row['title'],0,10)."..";
		}
		
		$map3['cate_id'] = array('in','11,12,13');
		$aishenghuo = $CustomReplyNews->where($map3)->order('cTime desc')->limit(3)->select();
		foreach($aishenghuo as $row)
		{
			$row['title'] = substr($row['title'],0,10)."..";
		}
		
		$map4['cate_id'] = array('in','8');
		$benqiancheng = $CustomReplyNews->where($map4)->order('cTime desc')->limit(3)->select();
		foreach($benqiancheng as $row)
		{
			$row['title'] = substr($row['title'],0,10)."..";
		}
		
		$map5['cate_id'] = array('in','3,15');
		$quanyouhui = $CustomReplyNews->where($map5)->order('cTime desc')->limit(3)->select();
		foreach($quanyouhui as $row)
		{
			$row['title'] = substr($row['title'],0,10)."..";
		}
		*/
		//获得头像
		$token = get_token();
		$openid = get_openid();		
		$userinfo = getWeixinUserInfo($openid, $token);
		$this->assign("headPic",$userinfo['headimgurl']);
		
		
		$this->assign("zaozhidao",$zaozhidao);
		$this->assign("youshendu",$youshendu);
		$this->assign("aishenghuo",$aishenghuo);
		$this->assign("benqiancheng",$benqiancheng);
		$this->assign("quanyouhui",$quanyouhui);
		
		$this->display();
    }
	
	public function listpage()
	{
		$id = intval(I('get.id'));
		$SchoolhomeCategory = M("SchoolhomeCategory");
		$sMap['id'] = $id;
		$category = $SchoolhomeCategory->where($sMap)->find();
		$this->webTitle = $category['name'];
		
		$this->assign("categoryName",$category['name']);
		
		$CustomReplyNews = M("CustomReplyNews");
		$nMap['cate_id'] = array('in',$category['cate_list']);
		$newsList = $CustomReplyNews->where($nMap)->order('cTime desc')->select();
		$this->assign("newsList",$newsList);
		
		//获得头像
		$token = get_token();
		$openid = get_openid();		
		$userinfo = getWeixinUserInfo($openid, $token);
		$this->assign("headPic",$userinfo['headimgurl']);
		
		$this->display();
	}
	
	public function detail()
	{
		$map['id'] = I('get.id');
		$CustomReplyNews = M("CustomReplyNews");
		$news = $CustomReplyNews->where($map)->find();
		
		$news['create_date'] = date('Y-m-d G:i:s',$news['cTime']);
		
		//新增点击量
		($news['view_count']<800)?($news['view_count']+=rand(1,3)):($news['view_count']+=rand(1,2));
		$CustomReplyNews->save($news);
		
		$news['pic'] = get_cover_url ( $news ['cover'] );
		$this->assign("news",$news);
		

        //给对应类别加1
        $open_id = get_openid();	
        $category_cate = I('get.cate');
        $LabelRecord = M("CustomLabelRecord");//记录点击数   
        switch ($category_cate) {
        	case 'tongzhi':
        	case 'tz_num':
        	    //$this->assign("category_cate",$category_cate);
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 1;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['tz_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set tz_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'huodong':
        	case 'hd_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 1;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['hd_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set hd_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'xueshu':
        	case 'xs_num':
        	    $result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 1;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['xs_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set xs_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'jingsai':
        	case 'js_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 1;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['js_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set js_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'jiuye':
        	case 'jy_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 1;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['jy_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set jy_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'xigongda':
        	case 'xgd_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 1;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['xgd_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set xgd_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'sanhang':
        	case 'sanh_num':
        		if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 1;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['sanh_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set sanh_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'quan':
        	case 'quan_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 1;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['quan_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set quan_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'yangongzhisheng':
        	case 'ygzs_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 1;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['ygzs_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set ygzs_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'xiaohua':                      
        	case 'xh_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 1;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['xh_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set xh_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'xuetang':         
        	case 'xt_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 1;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['xt_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set xt_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'renwu':
        	case 'rw_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 1;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['rw_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set rw_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'yikun':
        	case 'yk_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 1;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['yk_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set yk_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'jiaoliu':
        	case 'jl_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 1;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['jl_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set jl_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'xuesheng':
        	case 'xues_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 1;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['xues_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set xues_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'yingyu':
        	case 'yy_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 1;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['yy_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set yy_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'shenghuo':
        	case 'sh_num':
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 1;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['sh_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set sh_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'zhinan':
        	case 'zn_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 1;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['zn_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set zn_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'baida':
        	case 'bd_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 1;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['bd_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set bd_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'gongda':
        	case 'gd_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 1;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['gd_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set gd_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'youhui':
        	case 'yh_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 1;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['yh_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set yh_num = '$value' where openid = '$open_id'");
				}
        		break;
        	case 'xiaogua':
        	case 'xg_num':
        		$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 1;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['xg_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set xg_num = '$value' where openid = '$open_id'");
				}
        		break;
        	default:      		
        		break;
        }


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