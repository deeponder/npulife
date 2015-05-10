<?php

namespace Home\Controller;
use Think\Controller;
class ErweimaController extends Controller {
    
	public function createErweima(){
		header('Content-Type:image/jpg'); 
		
		for($i=0;$i<10000;$i++)
		{
			$sceneid = $i+10001;
			$accessToken = getAccessToken();
			$qcode = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$sceneid.'}}}';
			$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$accessToken;
			$result = $this->https_post($url,$qcode);
			$jsoninfo = json_decode($result,true);
			$ticket = $jsoninfo["ticket"];
			$qcodePicUrl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket);
			//$qcodePicUrl = "http://jebe.xnimg.cn/20140911/14/8b036c30-3c8b-45a0-bd32-98a08c7ff822.jpg";
			
			$filename = $sceneid.".jpg";
			$this->savePic($qcodePicUrl,$filename,"Erweima/");
			
			$imageInfo = $this->downloadImageFromWeixin($qcodePicUrl);
			
		}
		
		echo "OK";
    }

    public function createOneErweima($sceneid){
    	header('Content-Type:image/jpg'); 		
		
		$accessToken = getAccessToken();
		$qcode = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$sceneid.'}}}';
		$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$accessToken;
		$result = $this->https_post($url,$qcode);
		$jsoninfo = json_decode($result,true);
		$ticket = $jsoninfo["ticket"];
		$qcodePicUrl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket);
		//$qcodePicUrl = "http://jebe.xnimg.cn/20140911/14/8b036c30-3c8b-45a0-bd32-98a08c7ff822.jpg";
			
		$filename = $sceneid.".jpg";
		$this->savePic($qcodePicUrl,$filename,"Erweima/");
			
		$imageInfo = $this->downloadImageFromWeixin($qcodePicUrl);
				
		echo "OK";
    }

    private function https_post($url,$data=null)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if(!empty($data))
		{
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	private function downloadImageFromWeixin($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$package = curl_exec($ch);
		$httpinfo = curl_getinfo($ch);
		curl_close($ch);
		return array_merge(array('body'=>$package),array('header'=>$httpinfo));
	}
	private function savePic($url,$filename='',$savefile='')   
	{     
	    $imgArr = array('gif','bmp','png','ico','jpg','jepg');  
	  
	    if(!$url) return false;  
	    
	    if(!$filename) {     
	      $ext=strtolower(end(explode('.',$url)));     
	      if(!in_array($ext,$imgArr)) return false;  
	      $filename=date("dMYHis").'.'.$ext;     
	    }     
	  
	    if(!is_dir($savefile)) mkdir($savefile, 0777);  
	    if(!is_readable($savefile)) chmod($savefile, 0777);  
	      
	    $filename = $savefile.$filename;  
	  
	    ob_start();     
	    readfile($url);     
	    $img = ob_get_contents();     
	    ob_end_clean();     
	    $size = strlen($img);     
	    
	    $fp2=@fopen($filename, "a");     
	    fwrite($fp2,$img);     
	    fclose($fp2);     
	    
	    return $filename;     
	}

	public function doCreateTips(){

		$SmartDorm = M("SmartDorm","nl_","DB_CONFIG_LIFE");
		$dormList = $SmartDorm->select();

		for($i=0;$i<count($dormList);$i++){
		//for($i=0;$i<1;$i++){
			if(mb_strlen($dormList[$i]['dorm_no'])==3)
			{
				$fontSize = 168;
				if($dormList[$i]['sex']=="女"){
					$source = "./Tip/001.jpg";
					$fx = 1405;
					$fy = 460;
					$ex = 2165;
					$ey = 620;
					$data = $dormList[$i];
				}				
				if($dormList[$i]['sex']=="男"){
					$source = "./Tip/002.jpg";
					$fx = 1424;
					$fy = 430;
					$ex = 2236;
					$ey = 570;
					$data = $dormList[$i];
				}
			}
			if(mb_strlen($dormList[$i]['dorm_no'])==4)
			{
				$fontSize = 128;
				if($dormList[$i]['sex']=="女"){
					$source = "./Tip/001.jpg";
					$fx = 1365;
					$fy = 460;
					$ex = 2165;
					$ey = 620;
					$data = $dormList[$i];
				}
				if($dormList[$i]['sex']=="男"){
					$source = "./Tip/002.jpg";
					$fx = 1384;
					$fy = 430;
					$ex = 2236;
					$ey = 570;
					$data = $dormList[$i];
				}
			}
			
			$font = "./Public/font/SourceCodePro-Bold.ttf";
			$this->createTip($source,$data,$font,$fontsize,$fx,$fy,$ex,$ey);
		}
		echo date('G:i:s');
	}

	private function createTip($source, $data, $font, $fontsize, $fx, $fy, $ex, $ey){

		//$source：底图的路径

		$text1 = $data['dorm_no'];
		$text2 = "No.".$data['scene_id'];
		$scene_id = $data['scene_id'];

	    //生成图片名称：sceneid_dormid.jpg
	    $img = $scene_id.'_'.$data['dorm_no'].'.jpg';
	    if (file_exists ( './Tip/' . $img )) {  
	        return $img;
	    }
	    
	    $main = imagecreatefromjpeg ( $source );  
	    
	    $width = imagesx ( $main );  
	    $height = imagesy ( $main );  
	      
	    $target = imagecreatetruecolor ( $width, $height );  
	      
	    $white = imagecolorallocate ( $target, 255, 255, 255 );  
	    imagefill ( $target, 0, 0, $white );  
	      
	    imagecopyresampled ( $target, $main, 0, 0, 0, 0, $width, $height, $width, $height );  
	    	    
	    $fontColor = imagecolorallocate ( $target, 0, 92, 139 );//字体的RGB颜色  
	      
	    $fontWidth = imagefontwidth ( $fontSize );
	    $fontHeight = imagefontheight ( $fontSize );  
	    
	    $textWidth = $fontWidth * mb_strlen ( $text1 );  
	   
	    //$fx = ceil($fx - ($textWidth/2));

	    imagettftext ( $target, $fontSize, 0, $fx, $fy, $fontColor, $font, $text1 );  
	    imagettftext ( $target, 30, 0, 10, 40, $fontColor, $font, $text2 );  
	    
	    //imageantialias($target, true);//抗锯齿，有些PHP版本有问题，谨慎使用  
	    /*  
	    imagefilledpolygon ( $target, array (10 + 0, 0 + 142, 0, 12 + 142, 20 + 0, 12 + 142), 3, $fontColor );//画三角形  
	    imageline($target, 100, 200, 20, 142, $fontColor);//画线      
	    imagefilledrectangle ( $target, 50, 100, 250, 150, $fontColor );//画矩形  
	     */
	    //bof of 合成图片,加入二维码  
	    $ErweimaSource = "./Erweima/".$scene_id.".jpg";
	    $child1 = imagecreatefromjpeg ( $ErweimaSource );  
	    imagecopymerge ( $target, $child1, $ex, $ey, 0, 0, imagesx($child1), imagesy($child1), 100 );  
	    //eof of 合成图片  
	    
	    @mkdir ( './' . "Tip2" );
	    imagejpeg ( $target, './Tip2/' . $img, 95 );  
	    
	    imagedestroy ( $main );  
	    imagedestroy ( $target );  
	    imagedestroy ( $child1 );  
	    return $img;
	}
}