<?php
namespace Home\Controller;
// namespace Org\Util;
use Think\Controller;
class FeedbackController extends Controller {
    public function index(){
    	Session_start();
    	$this->display();
	}


	public function feedback(){
			$pw = I('post.pass');

			if($pw == '1008611'||$pw = md5('1008611')){
				$info = M('advice','nl_','DB_CONFIG1')->order('cTime desc')->select();
				$this->info = $info;
				$this->display('feedback');
			}elseif(!$pw){
					redirect(U('index'));
			}else{
				$this->error('密码错误了哦~~~');
			}
			
	}

	public function del(){
			$id=I('get.id');
			$num=M('advice','nl_','DB_CONFIG1')->delete($id);
			if($num){
				$result[0] = "删除成功了哦，么么哒~~";
			
			}else{
				$result[0] = "删除失败啦，请稍后重试咯~~";
			}
			$para = '?pass='.md5('1008611');
			$result[1] = $para;
			$this->ajaxReturn($result,'JSON');
		
		}
	

	public function wenjuan(){
		$list = M('survey_answer','nl_','DB_CONFIG1')->group('openid')->select();
		$num = count($list);
		$token = get_token();
		echo $num.'\n';
		echo "\n";
		for($i=0;$i<$num;$i++){
			$openid = $list[$i]['openid'];
			$map['openid'] = $openid;
			$pe = M('survey_answer','nl_','DB_CONFIG1')->group('openid')->where($map)->select();
	
			if(!$pe[0]['nickname']){
			$userinfo = getWeixinUserInfo($openid, $token);
			$data['nickname'] = $userinfo['nickname'];
		
			M('survey_answer','nl_','DB_CONFIG1')->where($map)->save($data);
			}
		
			$pe2 = M('survey_answer','nl_','DB_CONFIG1')->group('openid')->where($map)->select();

			echo $pe2[0]['nickname'].',   ';
		} 
					

	}
	
		
}