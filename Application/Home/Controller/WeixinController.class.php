<?php

namespace Home\Controller;

/**
 * 微信交互控制器
 * 主要获取和反馈微信平台的数据
 */
class WeixinController extends HomeController {
	var $token;
		
	public function index() {
		$this->token = get_token ();
		$weixin = D ( 'Weixin' );
		// 获取数据
		$data = $weixin->getData ();
		
		if (! empty ( $data ['FromUserName'] )) {
			session ( 'openid', $data ['FromUserName'] );
		}
		// 记录日志
		addWeixinLog ( $data, $GLOBALS ['HTTP_RAW_POST_DATA'] );		
		
		// 判断有没有完成新手导引功能，没有的话给他推送一条消息。
		//$this->xinshoudaoyin($data);
		
		// 回复数据
		$this->reply ( $data, $weixin );
		
		// 结束程序。防止oneThink框架的调试信息输出
		exit ();
	}
	/*
	private function xinshoudaoyin($data)
	{
			
		$MemberXinshoudaoyin = M("MemberXinshoudaoyin");
		$mMap['openid'] = $data['FromUserName'];
		$userlog = $MemberXinshoudaoyin->where($mMap)->find();
		if($userlog)
		{
			
		}
		//status: 0-已发送;1-已使用;
		
		
		$config = getAddonConfig ( 'Wecome' );
			
		$articles [0] = array (
					'Title' => $config ['title'],
					'Description' => $config ['description'],
					'PicUrl' => $config ['pic_url'],
					'Url' => $config ['url'] 
			);
		$touser = "",//"o8TQCj8ch3DuyerWWZjI8zsONdEA";//$data['FromUserName'];
		$msgtype = "news";
		$content = $articles;
		$token = get_token();
		
		if($action)
		{
			//customSend($touser, $token, $content, $msgtype);//return true;
		}
		else
		{
			customSend($touser, $token, $content, $msgtype);
		}
	}
	*/
	
	private function reply($data, $weixin) {
		$weixin = D('Weixin');	
		//$openid = get_openid();
		//$weixin->replyText($data);
		//检查是否投票
		//if($data['MsgType']=="text")
		//{ 
			// if(strstr($data['Content'],"投票"))
			// {
				// $liandui = intval(end(explode("投票",$data['Content'])));
				
				// $Junxun = M ( 'Junxun','nl_','DB_CONFIG_NPULIFE_DATA');
				// $jMap['id'] = $liandui;
				// $jxld = $Junxun->where($jMap)->find();
				// if($jxld)
				// {
					// $jxld['view_count']++;
					// $Junxun->save($jxld);
					// $weixin->replyText($liandui."投票成功！");
				// }
				// else
				// {
					// $weixin->replyText("投票失败，请检查输入是否正确。例: vote17");
				// }
			// }
			// if(strstr($data['Content'],"瓜大电台"))
			// {
				// $guadadiantai = end(explode("瓜大电台",$data['Content']));
				// if($guadadiantai)
				// {
					// $weixin->replyText("瓜大电台留言成功！");
				// }
				// else
				// {
					// $weixin->replyText("点击按钮：点燃生活-瓜大电台，就可以收听啦！");
				// }
			// }
			//*******************************上墙代码*****************************************
			// $mem=M('member');
			
			// $oMap['openid']=$data['FromUserName'];
			// //获取用户的当前的状态
			// $w_status=$mem->where($oMap)->getField('status');
		
			// //三个判断进行用户的上墙信息表的建立
			// //判断当前的用户状态，看是否处于上墙模式，如果满足则存储该消息
			// if(!$w_status&&$data['Content']!="退出"){
			// 	$follows=M('follows');
			// 	$data['contents']=$data['Content'];
			// 	$data['openid']=$data['FromUserName'];
			// 	$ctime=date('Y-m-d H:i:s',$data['CreateTime']);
			// 	$data['ctime']=$ctime;
			// 	//获取用户信息，相关函数属于自己写的封装函数，可根据自己的情况获取
			// 	$token = get_token();
			// 	$openid = get_openid();		
			// 	$userinfo = getWeixinUserInfo($openid, $token);
			// 	$headPic = $userinfo['headimgurl'];
			// 	$data['headimge']=$headPic;
			// 	$data['nickname']=$userinfo['nickname'];
			// 	$follows->add($data);
			// 	$weixin->replyText("您的信息我们已经收到，待审核后即可上墙，回复【退出】离开上墙模式");

			// }

			// if(preg_match('/我要上墙/i',$data['Content'])){
			// 	// preg_match('/\#微唱\#(.+)/i',$data['Content'], $matchs);
			// 	// $oMap['openid']=$data['FromUserName'];

			// 	$data['status']=0;
			// 	$mem->where($oMap)->save($data);
				
			// 	$weixin->replyText("进入上墙模式，回复【退出】，离开上墙模式");
			// }
			
			// if(preg_match('/退出/i',$data['Content'])){
			// 	// $mem=M('member');
			// 	// $oMap['openid']=$data['FromUserName'];
			// 	$data['status']=1;
			// 	$mem->where($oMap)->save($data);
				
			// 	$weixin->replyText("已成功退出上墙模式，回复【我要上墙】，进入上墙模式");
			// }
			
			
			//微信上墙回退方案-------------------------------------------------------------------------
			// $mem=M('member');
			// $follow=M('follows');
	
			// $oMap['openid']=$data['FromUserName'];
			// $f_status=$follow->where($oMap)->getField('status');
			// $w_status=$mem->where($oMap)->getField('status');
			// $pstatus=$mem->where($oMap)->getField('pstatus');
			// $pname=$mem->where($oMap)->getField('pname');
			// $cate=$follow->where($oMap)->getField('cate');
			// // $ws=strval($w_status);
			// // if(preg_match('/风味酸牛奶/i',$data['Content'])){
			// // $weixin->replyText($ws);
			// // }
			// if(preg_match('/我要中奖/i',$data['Content'])){
			// if(!$f_status){
			// $weixin->replyText("很遗憾，没中奖哦~~");
			// }else{
			// // $add="dfdf";
			// $weixin->replyText($cate);

			// }
			// }
			// if(!$pstatus){
			// $data['pname']=$data['Content'];
			// $data['pstatus']=1;
			// $mem->where($oMap)->save($data);
			// $weixin->replyText("设置成功，请重新回复【我要上墙】");

			// }
			// if(!$w_status&&$data['Content']!="退出"&&$data['Content']!="我要中奖"&&$data['Content']!="wesing"){
			// 	$follows=M('follows');
			// 	$data['contents']=$data['Content'];
			// 	$data['openid']=$data['FromUserName'];
			// 	$ctime=date('Y-m-d H:i:s',$data['CreateTime']);
			// 	$data['ctime']=$ctime;
			// 	//获取用户信息
			// 	// $token = get_token();
			// 	// $openid = get_openid();		
			// 	// $userinfo = getWeixinUserInfo($openid, $token);
			// 	// $headPic = $userinfo['headimgurl'];
			// 	// $data['headimge']=$headPic;
			// 	// $data['nickname']=$userinfo['nickname'];
			// 	$data['nickname']=$pname;
			// 	$follows->add($data);
			// 	$weixin->replyText("您的信息我们已经收到，待审核后即可上墙，回复【退出】离开上墙模式");

			// }

			// if(preg_match('/我要上墙/i',$data['Content'])){
			// 	// preg_match('/\#微唱\#(.+)/i',$data['Content'], $matchs);
			// 	// $oMap['openid']=$data['FromUserName'];
			// 	if($pname==null){
			// 	$data['pstatus']=0;
			// 	$weixin->replyText("请先回复设置您的昵称，再重新回复【我要上墙】");
			// 	$mem->where($oMap)->save($data);

			// 	}else{
			// 	$data['status']=0;
			// 	$mem->where($oMap)->save($data);
			// 	$articles[0] = array(
			// 		"Title" => "进入上墙模式，回复【退出】，离开上墙模式",
			// 		"Description" => "小伙伴们嗨起来，让全场听到你的声音，更有神秘大奖哦~~不要忘记回复【wesing】给选手们投票哦",
			// 		"PicUrl" => "http://wechat.npulife.com/tool/Public/Voice/playVoice_round2/images/head3.jpg",
			// 		"Url" => "",
			// 	);
				
			// 	$weixin->replyNews($articles);
			// 	}
			// }
			
			// if(preg_match('/退出/i',$data['Content'])){
			// 	// $mem=M('member');
			// 	// $oMap['openid']=$data['FromUserName'];
			// 	$data['status']=1;
			// 	$mem->where($oMap)->save($data);
			// 	$articles[0] = array(
			// 		"Title" => "已成功退出上墙模式，回复【我要上墙】，进入上墙模式",
			// 		"Description" => "欢迎再次加入我们的讨论哦！！~~",
			// 		"PicUrl" => "http://wechat.npulife.com/tool/Public/Voice/playVoice_round2/images/head3.jpg",
			// 		"Url" => "",
			// 	);
			// 	$weixin->replyNews($articles);
			// }
		//}
		
		
		$key = $data ['Content'];
		$keywordArr = array ();
		
		// 插件权限控制
		$token_status = D ( 'Common/AddonStatus' )->getList ();
		foreach ( $token_status as $a => $s ) {
			$s == 1 || $forbit_addon [$a] = $a;
		}
		
		// 所有安装过的微信插件
		$addon_list = ( array ) D ( 'Addons' )->getWeixinList ( false, $token_status );
		
		/**
		 * 通过微信事件来定位处理的插件
		 * event可能的值：
		 * subscribe : 关注公众号
		 * unsubscribe : 取消关注公众号
		 * scan : 扫描带参数二维码事件
		 * location : 上报地理位置事件
		 * click : 自定义菜单事件
		 */
		if ($data ['MsgType'] == 'event') {
			$event = strtolower ( $data ['Event'] );
			foreach ( $addon_list as $vo ) {
				require_once ONETHINK_ADDON_PATH . $vo ['name'] . '/Model/WeixinAddonModel.class.php';
				$model = D ( 'Addons://' . $vo ['name'] . '/WeixinAddon' );
				! method_exists ( $model, $event ) || $model->$event ( $data );
			}
			if ($event == 'click' && ! empty ( $data ['EventKey'] )) {
				$key = $data ['EventKey'];
			} else {
				return true;
			}
		}
		
		if ($data ['MsgType'] == 'voice') {
			
			$openid = $data['FromUserName'];
			$media_id = $data['MediaId'];
			
			//$access_token = getAccessToken();
			//$voiceurl = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$media_id";
			//$fileInfo = $this->downloadWeixinFile($voiceurl);
			
			/*//如果声音少于40秒，则提示不能参赛。/////////////////////////////////
			if(!empty($data ['Recognition'])) {
				$key = $data ['Recognition'];
				//$VoiceWeixin = D('Weixin');
				//$VoiceWeixin->replyText($key);
			}
			/*/////////////////////////////////////////////////////////////////////


			//微唱海选作品提交
			// $articles[0] = array(
			// 		"Title" => "欢迎参加\"微唱\"校园歌手大赛海选！点击此处欣赏所有作品~",
			// 		"Description" => "点击查看所有作品",
			// 		"PicUrl" => "http://wechat.npulife.com/Public/news/wesing.jpg",
			// 		"Url" => "http://content.npulife.com/Tool/index.php/Home/Voice/listVoice",
			// 	);
			
			// //查重，不能重复提交作品。
			// $Voice = M ( 'Voice','nl_','DB_CONFIG_NPULIFE_DATA');
			// $oMap['openid'] = $openid;
			// $oMap['status'] = array('in','0,1');
			// $theSong = $Voice->where($oMap)->find();
			
			// if(!$theSong)
			// {
			// 	$articles[1] = array(
			// 		"Title" => "【作品提交】点击上面的语音试听，如果不满意直接重新录音即可；如果感觉效果不错，点击此处提交作品。每人只有一次作品提交机会哦~",
			// 		"Description" => "",
			// 		"PicUrl" => "",
			// 		"Url" => "http://content.npulife.com/Tool/index.php/Home/Voice/submitVoice?openid=$openid&media_id=$media_id",
			// 	);
			// }
			// else
			// {
			// 	$articles[1] = array(
			// 		"Title" => "【您已提交】您已上传过作品啦，每人只有一次作品提交机会哦~",
			// 		"Description" => "",
			// 		"PicUrl" => "",
			// 		"Url" => "http://content.npulife.com/Tool/index.php/Home/Voice/listVoice?openid=$openid",
			// 	);
			// }
			

			//瓜大星主播
			$articles[0] = array(
					"Title" => "欢迎参加\"瓜大星主播\"网络主播海选！点击此处欣赏所有作品~",
					"Description" => "点击查看所有作品",
					"PicUrl" => "http://wechat.npulife.com/tool/Public/radio/radio.jpg",
					"Url" => "http://wechat.npulife.com/Tool/home/radio/listRadio",
				);
			
			//查重，不能重复提交作品。
			$Voice = M ( 'Radio');
			$oMap['openid'] = $openid;
			$oMap['status'] = array('in','0,1');
			$theSong = $Voice->where($oMap)->find();
			
			if(!$theSong)
			{
				$articles[1] = array(
					"Title" => "【作品提交】点击上面的语音试听，如果不满意直接重新录音即可；如果感觉效果不错，点击此处提交作品。每人只有一次作品提交机会哦~",
					"Description" => "",
					"PicUrl" => "",
					"Url" => "http://wechat.npulife.com/Tool/home/radio/submitRadio?openid=$openid&media_id=$media_id",
				);
			}
			else
			{
				$articles[1] = array(
					"Title" => "【您已提交】您已上传过作品啦，每人只有一次作品提交机会哦~",
					"Description" => "",
					"PicUrl" => "",
					"Url" => "http://wechat.npulife.com/Tool/home/radio/listRadio",
				);
			}
			


			$touser = $openid;
			$token = "535ca7e3cde42";
			$msgtype = "news";
			$content = $articles;
			//customSend($touser,$token,$content,$msgtype);
			
			$weixin->replyNews($articles);			
			exit;
			//////////////////////////////////////////////////////////////////////*/
		}

		// 通过获取上次缓存的用户状态来定位处理的插件
		$uid = intval ( $this->mid );
		$user_status = S ( 'user_status_' . $uid );
		if (! isset ( $addons [$key] ) && $user_status) {
			$addons [$key] = $user_status ['addon'];
			$keywordArr = $user_status ['keywordArr'];
			S ( 'user_status_' . $uid, null );
		}
		
		// 通过插件标识名和插件名来定位处理的插件
		if (! isset ( $addons [$key] )) {
			foreach ( $addon_list as $k => $vo ) {
				$addons [$vo ['name']] = $k;
				$addons [$vo ['title']] = $k;
			}
		}
		
		// 通过精准关键词来定位处理的插件 token=0是插件安装时初始化的模糊关键词，所有公众号都可以用
		if (! empty ( $forbit_addon )) {
			$like ['addon'] = array (
					'not in',
					$forbit_addon 
			);
		}
		$like ['token'] = array (
				'exp',
				"=0 or token='{$this->token}'" 
		);
		if (! isset ( $addons [$key] )) {
			$like ['keyword'] = $key;
			$like ['keyword_type'] = 0;
			$keywordArr = M ( 'keyword' )->where ( $like )->order ( 'id desc' )->find ();
			$addons [$key] = $keywordArr ['addon'];
		}
		// 通过模糊关键词来定位处理的插件
		if (! isset ( $addons [$key] )) {
			unset ( $like ['keyword'] );
			$like ['keyword_type'] = array (
					'gt',
					0 
			);
			$list = M ( 'keyword' )->where ( $like )->order ( 'keyword_lenght desc, id desc' )->select ();
			
			foreach ( $list as $keywordInfo ) {
				$this->_contain_keyword ( $keywordInfo, $key, $addons, $keywordArr );
			}
		}

         if($data ['MsgType'] != 'event')
         {
         	$key =$data ['Content']= trim($key);//去除空格
         	if(!strcmp("数据更新",trim($key)))
         	{
         	    $addons [$key]='DataAccquisiton';
         	}
         	//$res = D('Weixin')->replyText($key);
             //return $res;

         }
      
		
		// 以上都无法定位插件时，如果开启了智能聊天，则默认使用智能聊天插件
		if (! isset ( $addons [$key] ) && isset ( $addon_list ['Chat'] )) {
			
				$addons [$key] = 'Chat';	
			
		}

		// 最终也无法定位到插件，终止操作
		if (! isset ( $addons [$key] ) || ! file_exists ( ONETHINK_ADDON_PATH . $addons [$key] . '/Model/WeixinAddonModel.class.php' )) {
			$addons [$key]='Binding';
		}
		
		// 加载相应的插件来处理并反馈信息
		require_once ONETHINK_ADDON_PATH . $addons [$key] . '/Model/WeixinAddonModel.class.php';
		$model = D ( 'Addons://' . $addons [$key] . '/WeixinAddon' );
		$model->reply ( $data, $keywordArr );
	}
		
	// 处理关键词包含的算法
	private function _contain_keyword($keywordInfo, $key, &$addons, &$keywordArr) {
		if (isset ( $addons [$key] ))
			return false;
		
		$arr = explode ( $keywordInfo ['keyword'], $key );
		if (count ( $arr ) > 1) {
			// 在关键词不相等的情况下进行左右匹配判断，否则相等的情况肯定都匹配
			if ($keywordInfo ['keyword'] != $key) {
				// 左边匹配
				if ($keywordInfo ['keyword_type'] == 1 && ! empty ( $arr [0] ))
					return false;
					
					// 右边 匹配
				if ($keywordInfo ['keyword_type'] == 2 && ! empty ( $arr [1] ))
					return false;
			}
			
			$addons [$key] = $keywordInfo ['addon'];
			
			$keywordArr = $keywordInfo;
			$keywordArr ['prefix'] = trim ( $arr [0] ); // 关键词前缀，即包含关键词的前面部分
			$keywordArr ['suffix'] = trim ( $arr [1] ); // 关键词后缀，即包含关键词的后面部分
		}
	}
	
	//语音处理函数
	private function downloadWeixinFile($voiceurl) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);    
		curl_setopt($ch, CURLOPT_NOBODY, 0);    //只取body头
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$package = curl_exec($ch);
		$httpinfo = curl_getinfo($ch);
		curl_close($ch);
		$imageAll = array_merge(array('header' => $httpinfo), array('body' => $package)); 
		return $imageAll;
	}
}
?>