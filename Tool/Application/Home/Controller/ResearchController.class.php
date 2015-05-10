<?php
namespace Home\Controller;
use Think\Controller;
 class ResearchController extends Controller {
 	public function research()
	{
	 $openid =get_openid();
	//get_openid()
	if($openid!=-1)
	{
	$ac_id = 1;
	//找单选和多选的数量
	$survey_pro=M('survey_problem','nl_','DB_CONFIG1');
	  $smap['ac_id']=$ac_id;
	  $smap['is_radio']=1;
	  $RadioSel=$survey_pro->Distinct(true)->field('pro_num')->where($smap)->select();
	 // dump($RadioSel);
	  $cmap['ac_id']=$ac_id;
	  //$cmap['is_radio']=false;
	  $TotalSel=$survey_pro->Distinct(true)->field('pro_num')->where($cmap)->select();
	  //dump($TotalSel);
	  $radio_num=count($RadioSel);
	  $total_num=count($TotalSel);
	  
	  
	
	// $survey_pro = M('survey_count','nl_','DB_CONFIG1');
	//dump($total_num);
	//dump($radio_num);
	$select_radio=array();
	$select_check=array();
	for($i=1;$i<$radio_num+1;$i++)
	{
	$mmap['ac_id']=$ac_id;
	$mmap['pro_num']=$i;
	$selected = $survey_pro->where($mmap)->select();
    array_push($select_radio,$selected);
	}
	for($j=$radio_num+1;$j<=$total_num;$j++)
	{
	$mmap['ac_id']=$ac_id;
	$mmap['pro_num']=$i;
	$selected = $survey_pro->where($mmap)->select();
    array_push($select_check,$selected);
	}
	$Survey = M('surveydetail','nl_','DB_CONFIG1');
	$vmap['openid']=$openid;
	$vmap['ac_id'] = $ac_id;
	$isSeclect = $Survey->where($vmap)->getField('openid',true);
	//dump($isSeclect);
	// $num = count($iSeclect);in_array($openid,$isSelect)
	//dump($openid);                                                                                                                                                                                  
	if($isSeclect) {$a="yes";}
     else{ $a="no";}
  //dump($a);
  $this->assign("b",$a);
	 $this->assign("selectRadio",$select_radio);
	 $this->assign("selectCheck",$select_check);
	 $this->display('research');
   
   
     
  
 }
  
  

}
  
  
   //往后台添加相应的选择内容
  
  
  
 public function submit()
 {
   $a="yes";
   $this->assign("b",$a);
  $openid =get_openid();
	//get_openid()
	if($openid!=-1)
	{
	$ac_id = 1;
	$survey_pro=M('survey_problem','nl_','DB_CONFIG1');
	  $smap['ac_id']=$ac_id;
	  $smap['is_radio']=1;
	  $RadioSel=$survey_pro->Distinct(true)->field('pro_num')->where($smap)->select();
	 // dump($RadioSel);
	  $cmap['ac_id']=$ac_id;
	  //$cmap['is_radio']=false;
	  $TotalSel=$survey_pro->Distinct(true)->field('pro_num')->where($cmap)->select();
	  //dump($TotalSel);
	  $radio_num=count($RadioSel);
	  $total_num=count($TotalSel);
	  
	  
	
	// $survey_pro = M('survey_count','nl_','DB_CONFIG1');
	$select_radio=array();
	$select_check=array();
	for($i=1;$i<$radio_num+1;$i++)
	{
	$mmap['ac_id']=$ac_id;
	$mmap['pro_num']=$i;
	$selected = $survey_pro->where($mmap)->select();
    array_push($select_radio,$selected);
	}
	for($j=$radio_num+1;$j<=$total_num;$j++)
	{
	$mmap['ac_id']=$ac_id;
	$mmap['pro_num']=$i;
	$selected = $survey_pro->where($mmap)->select();
    array_push($select_check,$selected);
	}
	
	
	$Survey = M('surveydetail','nl_','DB_CONFIG1');
	$vmap['ac_id'] = $ac_id;
	$isSeclect = $Survey->where($vmap)->getField('openid',true);
	 
			 
			  
  
   //往数据中插入调查的原始数据
	   for($i = 1;$i<=$radio_num;$i++)
	  {
	  
	  //向最原始的投票数据添加
	  $key = 'problem'.$i;
	  $detail_selection = $_GET[$key];
	  //dump($detail_selection);
	  $detail['ac_id']=$ac_id;
	  $detail['openid']=$openid;
	  $detail['pro_num'] = $i;
	  $detail['selection']=$detail_selection;
	  $Survey->add($detail);
	  //动态更改每个选项的选择人数
	   $radio['ac_id']=$ac_id;
	   $radio['pro_num']=$i;
	   $radio['selContent']=$detail_selection;
	  // dump($radio);
	   $pre=$survey_pro->where($radio)->find();
	  // dump($pre);
	  $pre['count']++;
	  $survey_pro->save($pre);
	  
	  }
	  
	  for($i=$radio_num+1; $i<=$total_num;$i++)
	  {
	  
	  //向最原始的投票数据添加
	  $check_key='problem'.$i;
	  $detail_sel=$_GET[$check_key];
	 // dump($detail_sel);
	  $num = count($detail_sel);
	  for($j=0;$j<$num;$j++)
	  {
	  $check['ac_id']=$ac_id;
	  $check['openid']=$openid;
	  $check['pro_num']=$i;
	  $check['selection']=$detail_sel[$j];
	  $Survey->add($check);
	  
	  //动态更改每个选项的选择人数
	   $checkcou['ac_id']=$ac_id;
	   $checkcou['pro_num']=$i;
	   $checkcou['selContent']=$detail_sel[$j];
	   $pre=$survey_pro->where($checkcou)->find();
	  $pre['count']++;
	  $survey_pro->save($pre);
	  }
	  }
	  
	 
		   
	$this->assign("selectRadio",$select_radio);
	 $this->assign("selectCheck",$select_check);
	
	// dump($select_radio);
	//dump($select_check);
		 // if($a=="no"){
	
   
   
     
  
 
  
}$this->display("research");
}
}

?>