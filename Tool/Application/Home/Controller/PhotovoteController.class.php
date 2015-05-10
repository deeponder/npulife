<?php

namespace Home\Controller;
use Think\Controller;

class PhotovoteController extends Controller{
	public function index(){
		//浏览器检查
		session_start();
		$openid = get_openid();
		
		
		if($openid == -1){
			$this->myError("请从瓜大生活圈进入~");
			return;
		}
/*
		//网站升级
		if($openid != "o8TQCj2Z2JO1kRLifVJnrk2GI6yM" &&$openid !=-1){
			$this->myError('网站维护中~马上开始第二轮展示~');
			return;
		}
	*/
		session("openid",$openid);
		$this->redirect("show?action=people");
	}
	
	public function show(){
		//身份检查
		$openid = session("openid");
		if($openid == NULL){
			$this->myError("请从瓜大生活圈进入~");
			return;
		}
		//获取
		$photoModel = M("Photography","nl_","DB_CONFIG_NPULIFE_DATA");
		$action = I("action");
		$type = I("order");		//排序类型
		$this->assign("order",$type);
		switch ($action){
			case "people":
				$pictures = NULL;
				if($type == "1"){
					$pictures = $photoModel->where('type = %d',10)->order('addnum desc')->limit(5)->select();
						
				}else{
					$pictures = $photoModel->where('type = %d',10)->order('level')->limit(5)->select();
				}
				$this->assign('type',"people");
				$this->assign('pictures',$pictures);
				break;
			case "fj":
				$pictures = NULL;
				if($type == "1"){
					$pictures = $photoModel->where('type = %d',20)->order('addnum desc')->limit(5)->select();
						
				}else{
					$pictures = $photoModel->where('type = %d',20)->order('level')->limit(5)->select();
				}				
				$this->assign('type',"fj");
				$this->assign('pictures',$pictures);
				break;
			case "hz":
				$pictures = NULL;
				if($type == "1"){
					$pictures = $photoModel->where('type = %d',30)->order('addnum desc')->limit(5)->select();
						
				}else{
					$pictures = $photoModel->where('type = %d',30)->order('level')->limit(5)->select();
				}				
				$this->assign('type',"hz");
				$this->assign('pictures',$pictures);
				break;
			case "yx":	//优秀
				$pictures = NULL;
				if($type == "1"){
					$pictures = $photoModel->where('type = %d',4)->order('addnum desc')->limit(5)->select();
						
				}else{
					$pictures = $photoModel->where('type = %d',4)->order('level')->limit(5)->select();
				}				
				$this->assign('type',"yx");
				$this->assign('pictures',$pictures);
				break;
			default:
				return ; 
		}
		$this->display("index");
	}
	
	
	public function getMore(){
		if(IS_AJAX){
			$photoModel = M("Photography","nl_","DB_CONFIG_NPULIFE_DATA");
				
			$onepageNum = 5;
			
			$action = I("action");
			$order = I("order");
			$page = I("page");
			
			switch ($action){
				case "people":
					$pictures = NULL;
					if($order == "1"){
						$pictures = $photoModel->where('type = %d',10)->order('addnum desc')->limit($onepageNum*$page,$onepageNum)->select();
			
					}else{
						$pictures = $photoModel->where('type = %d',10)->order('level')->limit($onepageNum*$page,$onepageNum)->select();
					}
					$this->ajaxReturn($pictures);
					break;
				case "fj":
					$pictures = NULL;
					if($order == "1"){
						$pictures = $photoModel->where('type = %d',20)->order('addnum desc')->limit($onepageNum*$page,$onepageNum)->select();
			
					}else{
						$pictures = $photoModel->where('type = %d',20)->order('level')->limit($onepageNum*$page,$onepageNum)->select();
					}
					$this->ajaxReturn($pictures);
					break;
				case "hz":
					$pictures = NULL;
					if($order == "1"){
						$pictures = $photoModel->where('type = %d',30)->order('addnum desc')->limit($onepageNum*$page,$onepageNum)->select();
			
					}else{
						$pictures = $photoModel->where('type = %d',30)->order('level')->limit($onepageNum*$page,$onepageNum)->select();
					}
					$this->ajaxReturn($pictures);
					break;
				case "yx":
					$pictures = NULL;
					if($order == "1"){
						$pictures = $photoModel->where('type = %d',4)->order('addnum desc')->limit($onepageNum*$page,$onepageNum)->select();
			
					}else{
						$pictures = $photoModel->where('type = %d',4)->order('level')->limit($onepageNum*$page,$onepageNum)->select();
					}
					$this->ajaxReturn($pictures);
					break;
				default:
					return ;
			}
		}
	}
	public function dianzan(){
		//身份检查(防止直接访问)
		$openid = session("openid");
		if($openid == NULL){
			dump("请从瓜大生活圈进入~");
			return;
		}
		if(IS_AJAX){
			$photovoteMolde = M("Photovote","nl_","DB_CONFIG_NPULIFE_DATA");
			$photoModel = M("Photography","nl_","DB_CONFIG_NPULIFE_DATA");
			
			$pictureId = I("id");
			$isAdd = $photovoteMolde->where("openid = '%s' AND pid=%d",$openid,$pictureId)->find();
			if($isAdd != NULL){
				$dataBack = 0;
				$this->ajaxReturn($dataBack);
				return;
			}
			$data['openid'] =$openid;
			$data['pid'] = $pictureId;
			$photovoteMolde->add($data);
			$photoModel->where('photoid = %d',$pictureId)->setInc("addnum");
			
			$dataBack = 1;
			$this->ajaxReturn($dataBack);
 		}
	}
	
	public function upShow(){
		//dump("ef");
		$this->display("upload");
	}
	public function upload(){
		$photoModel = M("Photography","nl_","DB_CONFIG_NPULIFE_DATA");
		
		$data = $photoModel->create();
		
		//存图片
		$pictureFile = $_FILES["picture"];
		if($pictureFile == NULL){
			$this->myError("获取图片失败~:".$pictureFile["name"]);
			return ;
		}
		if(($pictureFile["type"] == "image/gif")
				|| ($pictureFile["type"] == "image/jpeg")
				|| ($pictureFile["type"] == "image/pjpeg")){
			
			if ($pictureFile["error"] > 0){
				$this->myError("Return Code: " . $pictureFile["error"] . "<br />");
				return;
			}
			else{
				if (file_exists("./Public/Photovote/img/photo/" . $pictureFile["name"]))
				{
					$this->myError($pictureFile["name"] . " already exists. ");
					return;
				}
				else
				{
					//跟新数据库并存储图片
					$isExist = $photoModel->where('title = "%s"',$data['title'])->find();
					if($isExist != NULL){
						$this->Myerror("已经上传！");
						return;
					}
					$pictureId = $photoModel->add($data);
					if(!$pictureId){
						$this->Myerror("数据库更新错误！");
						return;
					}
					move_uploaded_file($pictureFile["tmp_name"],
					"./Public/Photovote/img/photo/".$pictureId.".jpg");
					//图片压缩
					$image = imagecreatefromjpeg ("./Public/Photovote/img/photo/".$pictureId.".jpg");
					imagejpeg($image,"./Public/Photovote/img/photo/".$pictureId."small.jpg",30); /*压缩等级0-9，压缩后9最小，1最大*/
					// 释放内存
					imagedestroy($image);
					$this->success("图片".$pictureFile["name"]."上传成功！","upShow");
				}
			}
		}
		else{
			$this->myError("格式错误！");
			return;
		}
	}
	
	protected function myError($error){
		$this->assign("error",$error);
		$this->display("error");
	}
}

?>