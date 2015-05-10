<?php

namespace Home\Controller;

/**
 * 	测试模板接口
 * 主要获取和反馈微信平台的数据
 */
class QingjiaController extends HomeController {
    
	public function index(){
		
		$theUser = M("Member")->where('uid=1745')->find();
		$openid = $theUser["openid"];
		$ret = $this->qingjiaTemplate($openid);		
		echo $ret;
	}
	
	public function test(){
		$openid = "o8TQCj8ch3DuyerWWZjI8zsONdEA";//get_openid();
		echo $this->qingjiaTemplate($openid);
	}
	/*
	private function qingjiaTemplate($openid) {
		
		$GLOBALS ['user'] ['appid'] ='wx4c81bc4055e38cf5';
		$GLOBALS ['user'] ['secret']='4c469884a983d92a80e2967c9845bfef';
	
		header("Content-type: text/html; charset=utf-8");
		
		if (empty ( $GLOBALS ['user'] ['appid'] )) {
			return false;
		}
		
		$access_token = getAccessToken();
		
		$at ['access_token'] = $access_token;
		
		/*
		{{first.DATA}}

姓名：{{childName.DATA}}
请假时间：{{time.DATA}}
请假理由：{{reason.DATA}}
{{remark.DATA}}
		
		$data = "{
			\"touser\":\"".$openid."\",
			\"template_id\":\"mFwWgXZc-J-9cBPoo5Kb4pQeF80GvSlwjXIZj3b7My4\",
			\"url\":\"http://weixin.qq.com/download\",
			\"topcolor\":\"#FF0000\",
			\"data\":{
				\"first\":{\"value\":\"额滴神啊，有个娃请假咧~\",\"color\":\"#173177\"},
				\"childNmae\":{\"value\":\"MAKE\",\"color\":\"#173177\"},
				\"time\":{\"value\":\"2014年7月15日\",\"color\":\"#173177\"},
				\"reason\":{\"value\":\"make love\",\"color\":\"#173177\"},
				\"remark\":{\"value\":\"求公司审批\",\"color\":\"#173177\"}
			}
		}";
		
		$url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?'.http_build_query($at);
			
		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		$ret = curl_exec($ch); 
		
		return $ret;
	}*/
}

?>