<?php
namespace Home\Controller;
use Think\Controller;
class SchoolController extends Controller {
    
	public function index(){
		
			$this->display();
    }
	
	public function updatehandle(){
			$openId = get_openid();
		$oMap['openid']=$openId;
		
			$mem=D('Member');
			if(!$mem->create()){
				$this->error($mem->getError());
			}else{
			 $mem->where($oMap)->save();
			}
			redirect(U('index'));

		}
	
	public function addadvice(){
	$openid = get_openid();
	$advice=M('advice');
	$content=I('post.advice');
	// dump($content);
	$data['openid']=$openid;
	$data['contents']=$content;
	$ad=$advice->add($data);
	if($ad){
	$this->success('操作成功！');
	}else{
			$this->error('操作失败！');
		}
	
redirect(U('index'));
	}
	public function updateInfo(){
	$this->display();
	}
	
	public function test(){
	dump('dfd');
	}

	
}