<?php
namespace Home\Controller;
// namespace Org\Util;
use Think\Controller;
class PbackController extends Controller {
    public function index(){
	}
	public function message1(){
	$list=M('follows','nl_','DB_CONFIG1');
	// import('ORG.Util.Page');
	// $count		=$list->count();
	// $Page		=new \Org\Util\Page($count,10);
	// $show		=$Page->show();
	$message=$list->order('Id desc')->select();
	// $this->assign('page',$show);
	$this->assign('message',$message);
	$this->display();
	
	}
	public function examine(){
		$data['id']=I('get.id');
		$data['examine']=I('get.examine');
		$exam=M('follows','nl_','DB_CONFIG1');
		$examinenum=$exam->save($data);
		if($examinenum){
			$this->success('操作成功！');
		}else{
			$this->error('操作失败！');
		}
	
	}
	public function del(){
		$id=I('get.id');
		$delete=M('follows','nl_','DB_CONFIG1');
			$num=$delete->delete($id);
			if($num){
				$this->success('操作成功！');
			}else{
				$this->error('操作失败！');
			}
		
		}
	
	
		
}