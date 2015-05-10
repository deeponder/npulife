<?php

namespace Home\Controller;

/**
 * 私人定制
 * 主要获取和反馈微信平台的数据
 */
class PersonalController extends HomeController {
    public function index(){
		$openId = get_openid();
		
		if($openId != -1){
			$aamap['token'] = get_token ();
			$aamap['is_show'] = 1;
			$slideshow = M ( 'weisite_slideshow' )->where ( $aamap )->order ( 'sort asc, id desc' )->limit(4)->select ();
			foreach ( $slideshow as &$vo ) {
				$vo ['img'] = get_cover_url ( $vo ['img'] );
			}
			$this->assign ( 'slideshow', $slideshow );
			
			$map['openid'] = $openId;
			$prefered = D('Personalization')->order ( 'likes desc' )->where($map)->select();
			$items = array();
			$titles = array();
			if(!empty($prefered)){
				
				foreach($prefered as $aItem){
					$curID =  $aItem['category_id'];
					$items[] = $curID;
					$curTitle = D("WeisiteCategory")->field("title")->where("id=$curID")->find();
					$titles[] =$curTitle['title'];
				}
				$this->assign('items',$items);
				$this->assign('titles',$titles);
			}
		}

		$this->display();
	}
	
	public function addItem(){
		$openId = get_openid();
		if($openId != -1){
					
			$personalization = D('Personalization');
			$map['openid'] = $openId;
			$prefered = $personalization->field("category_id")->order ( 'category_id' )->where($map)->select();
			$preSelectedItems= array();
			foreach($prefered as $apref){
				$preSelectedItems[]=$apref['category_id'];
			}
			
			$preRes = $_POST;
			
			$dataToInsert = array();
			$dataToDelete = array();
			for($i=1 ;$i<=count($preRes); $i++){
				
				if($preRes[$i] == 'on'  ){
					if(!in_array($i,$preSelectedItems)){
						$tempArray = array();
						$tempArray['openid']=$openId;
						$tempArray['category_id']=$i;
						$tempArray['likes']=5;
						$tempArray['update_time']=0;
						$dataToInsert[]=$tempArray;
					}
				}else{
					if(in_array($i,$preSelectedItems)){
						$tempArray = array();
						$tempArray['openid']=$openId;
						$tempArray['category_id']=$i;
						$dataToDelete[]=$tempArray;
					}
				}
			}
			foreach($dataToInsert as $newdata){
				
				$res =$personalization->add($newdata);
	
			}
			foreach($dataToDelete as $delData){
				
				$personalization->where($delData)->delete();
			}
			
			redirect(U('index'));
		}
	}
	
	public function addList(){
		$openId = get_openid();
		if($openId != -1){
		
			$map['openid'] = $openId;
			$prefered = D('Personalization')->field("category_id")->order ( 'likes desc' )->where($map)->select();
			$selectedItems= array();
			foreach($prefered as $apref){
				$selectedItems[]=$apref['category_id'];
			}
			
			$allItems = D("WeisiteCategory")->field("id,title")->select();
			$itemsRes=array();
			
			
			foreach($allItems as $aItem){
			//	echo $aItem['id']." ".$aItem['title'];
				$aItem['selected']= in_array($aItem['id'],$selectedItems);
				$itemsRes[] = $aItem;
			}
			$this->assign('itemsRes',$itemsRes);
			
		}
		$this->display();
	}
}

?>