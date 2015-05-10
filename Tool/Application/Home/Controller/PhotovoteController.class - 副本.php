<?php

namespace Home\Controller;
use Think\Controller;

class PhotosmallController extends Controller{
	public function index(){
		
/*
		for($pictureId = 77;$pictureId<=130;$pictureId++){
			ini_set( 'memory_limit', '500M' );
			$image = imagecreatefromjpeg ("./Public/Photovote/img/photo/".$pictureId.".jpg");
			imagejpeg($image,"./Public/Photovote/img/photo/".$pictureId."small.jpg",30); //压缩
			// 释放内存
			imagedestroy($image);
			dump("picture：".$pictureId.".jpg"."become smaller");
		}
*/
		
	}

					
}

?>