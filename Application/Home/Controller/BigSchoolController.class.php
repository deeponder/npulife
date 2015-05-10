<?php
namespace Home\Controller;
use Think\Controller;
class BigSchoolController extends Controller
{

	public function test(){
		// dump('fuck!');

		$paarticle = M("PaArticle","nl_",'DB_CONFIG_NPULIFE_DATA');

		$ma = $paarticle->limit(10)->select();
		dump($ma);
	}

	//爬公众号

	public function papublice(){
		header("Content-Type: text/html; charset=utf-8");
		$openidList = M('Dingyuehao','nl_','DB_CONFIG_NPULIFE_DATA')->field('openid,cate_id')->select();
		$page = 1;
		$time_stamp = time();
		
		//	遍历所有订阅号，获取首页文章存入数据库    //
		$pubic_num = count($openidList);
		for($i = 0;$i<$pubic_num;$i++){
			//$openid = $openidList[$i];
			$openid = $openidList[$i]['openid'];
			$data['openid'] = $openid;
			$data['cate_id'] = $openidList[$i]['cate_id'];
			
			//获取文章信息
			$url = $this->create_url($openid,$page,$time_stamp);
			$content = file_get_contents($url);
			if($content == false) continue;

			//对获取的内容进行【JSON】格式化，并转化成数组 //
			$json_content = $this->get_json($content);
			$json_arr = json_decode($json_content,true);
			if($json_arr == NULL) continue;
			
			//将XML格式的文章信息解析成【数组】格式,并写入数据库
			$num = count($json_arr['items']);
			for($j = 0;$j< $num;$j++){
			
				$xml_string = $json_arr['items'][$j];
				$xml_string = str_replace('gbk','UTF-8',$xml_string);
				$xml = simplexml_load_string($xml_string,NULL,LIBXML_NOCDATA);
				$json = json_encode($xml);
				$artical_arr = json_decode($json,true);
			
				//dump($artical_arr);//['item']['display']['headimage']
				$this->put_into_db($data, $artical_arr);
			}
		}

	}


	private function create_url($openid,$page,$time){
		$base_url = "http://weixin.sogou.com/gzhjs?cb=sogou.weixin.gzhcb&openid=%s&page=%d&t=%s";
		return sprintf($base_url,$openid,$page,$time);
	}
	private function get_json($content){
		preg_match('/{+.*}/', $content,$json_string);
		return $json_string[0];
		
	}
	private function put_into_db($data,$artical_arr){
		$PaAtical = M('PaArticle','nl_','DB_CONFIG_NPULIFE_DATA');
		if($artical_arr == NULL || $data == NULL) return false;
		
		$item = $artical_arr['item']['display'];
		$data['docid'] = $item['docid'];
		/*查重*/
		$artical = $PaAtical->where('docid = "%s"',$data['docid'])->select();
		if($artical != NULL){

			// dump('此文章已存在！\n');
			return false;
		}
		$data['wxname'] = $item['sourcename'];
		$data['title'] = $item['title']; 
		$data['description'] = $item['content'];
		$data['picurl'] = $item['imglink'];
		$data['pudate'] = (int)$item['lastModified'];
		$data['url'] = $item['url'];
		
		//dump($data);
		$PaAtical->add($data);
		
	}
	
	/*首页*/
	public function index($channel="__all__"){

	    
        /*获得订阅的栏目,存储于数据库,用户此时进行了订阅修订*/
        $Lab = I('post.lab');
        $open_id=get_openid();
       // $this->assign("hah",$Lab);
       if(!empty($Lab) && ($Lab != "undefined"))
        {

		   
        	//$labs = explode(',',$Lab); //标签数组,存储起来
			//存储到数据库
			$Subscribe = M("CustomSubscribe"); 
			$chck =  $Subscribe->query("select * from nl_custom_subscribe where open_id = '$open_id'");	
        	if(empty($chck))
			{
				//添加用户的订阅记录
				//插入一条记录
				$datas['open_id'] = $open_id;
				$datas['subscribe_item'] = $Lab;
				$Subscribe -> data($datas) -> add();
			}
			else
			{
				//更改用户的订阅记录
				$Subscribe->execute("update nl_custom_subscribe set subscribe_item = '$Lab' where open_id = '$open_id'");
			}
			$this->assign('dingyue',$Lab);
       }
       else
       {
            
           //查对应的标签数据库，还原用户的订阅行为
           $Subscribe = M("CustomSubscribe"); 
		   $chck =  $Subscribe->query("select * from nl_custom_subscribe where open_id = '$open_id'");	
		   if(!empty($chck))
			{
				$this->assign('dingyue',$chck[0]['subscribe_item']);
				//$this->assign('hhh',$chck[0]['subscribe_item']);
				//$lstorage = $chck[0]['subscribe_item'];
				echo "<script>";
				//echo "localStorage['menuDefaults'] = ".$lstorage.";";
				echo "</script>";
			}
			else
			{
				$this->assign('dingyue','');
				//$this->assign('hhh','__all__,tongzhi,huodong,xueshu,jingsai,jiuye');
			}
	
       	  // $labs=["__all__","huodong","xueshu","jingsai"]; 
       	  //$labs_name=['推荐','活动','学术','竞赛'];
       	  // $this->assign("channelCategoryName",$labs_name);
       	  // $this->assign("channelCategory",$labs);
           //channelDefaultCount;
        	//$this->assign('lab',$Lab);
           //$this->display('getLab');
      }


		$limitNum = 10;

		$ArticleDB = M("CustomReplyNews");//自己的文章
		$paarticle = M("PaArticle","nl_",'DB_CONFIG_NPULIFE_DATA');
		$Member = M('Member','nl_','DB_CONFIG1');
        
        $LabelRecord = M("CustomLabelRecord");//记录点击数   20150304


		switch($channel)
		{
			case "":
			case "__all__":	//还需要改进，最好是把今天的文章全部罗列出来，然后全部按照时间来排

            /*查用户点击标签的记录表，根据openid选择该用户点击标签频率值最大的3项，并从这3项中选择一部分文章进行推荐*(推荐的策略后面可以在完善，选择的标签数目，每个标签下文章选择的数目)*/	
			/*1.选择该用户3个最大值的标签(SQL好像不能按列比较大小，最笨就是把每列都读出来，存到数组里，之后选择最大的几个)*/
			/*2.根据标签去每个类别下选取$limitNum/2个文章*/
			/*3.将选择的$limitNum/2*3篇文章组合成$articleList，返回给用户*/
			$open_id=get_openid();
			$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");	

			if(empty($result)){
			//按照之前的方式推荐文章
			   $this->papublice();

				$tag = "__all__";

				$myArticleList = $ArticleDB->order("cTime desc")->limit($limitNum/2)->select();	
				$paArticleList = $paarticle->order("pudate desc")->limit($limitNum/2)->select();
				//把没有的参数补充完整，这是因为两个数据表设计的不统一
				for($i=0;$i<$limitNum/2;$i++)
				{
					$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
					$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
					$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
					$paArticleList[$i]["view_count"] = "*";
				}
				for($i=0;$i<$limitNum/2;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/quan_num";
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "瓜大生活圈";
				}
				//把两个混合在一起；
				for($i=0;$i<$limitNum/2;$i++)
				{
					$articleList[$i*2] = $myArticleList[$i];
					$articleList[$i*2+1] = $paArticleList[$i];
				}		
				// $articleList =$myArticleList;
			}
			else{ //按照类别频率推荐文章
				//定义数组$triMax存放result[0]中频率最高的3项
			    $tag = "__all__";
				$label = array('tz_num', 'xs_num', 'xh_num', 'xt_num', 'js_num', 'hd_num', 'rw_num', 'yk_num', 'jl_num', 'xues_num', 'jy_num', 'yy_num', 'sh_num', 'zn_num', 'bd_num', 'gd_info', 'yh_num', 'xg_num', 'xgd_num', 'sanh_num', 'quan_num','ygzs_num');
				$triMax[0] = $label[0];$triMax[1] = $label[1];$triMax[2] = $label[2];

				for($i = 0;$i<2;$i++)
					for($j = $i+1;$j<=2;$j++)
					{
						if($result[0][$triMax[$i]] <$result[0][$triMax[$j]])
						{
							$temp=$triMax[$j];
							$triMax[$j]=$triMax[$i];
							$triMax[$i]=$temp;
						}
					}
				
				//for循环得到出现频率最高的三项
				//for($x=3; $x<4; $x++)
				for($x=3; $x<count($label);$x++)
				{
					for($i=0;$i<3;$i++)
					{
						if($result[0][$label[$x]] > $result[0][$triMax[$i]])
						{
							for($j = 2;$j >= $i; $j--)
							{
								$triMax[$j] = $triMax[$j-1];
							}
							$triMax[$i] = $label[$x];
							break;
						}
					}										
				}	
				$iterator_items = 0;//文章0
			
				foreach($triMax as $triMaxItem)
				{
					//$triMaxItem使用switch case选取类别中的文章
					switch($triMaxItem)
					{
						case "":break;
						case "tz_num":
							$tongzhiMap["cate_id"] = array("in","6,26");
							$myArticleList = $ArticleDB->where($tongzhiMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								//$this->assign('nihao',$myArticleList[$i]["url"]);
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "通知";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}							
							break;
						case "xs_num":
							$xuetangMap["cate_id"] = array("in","1");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "学术";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}	
							break;

                        case "ygzs_num":
							$xuetangMap["cate_id"] = array("in","4");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "研工之声";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}	
							break;
							
						case "xh_num":
							$xiaohuaMap["cate_id"] = array("in","11");
							$myArticleList = $ArticleDB->where($xiaohuaMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "笑话";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}	
							break;
						case "xt_num":
							$xuetangMap["cate_id"] = array("in","5");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "学堂";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}
							break;
						case 'js_num':
							$xuetangMap["cate_id"] = array("in","2");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "竞赛";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}
							break;
               			case 'hd_num':		
							$xuetangMap["cate_id"] = array("in","9");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "活动";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}	
							break;
						case 'rw_num':
							$xuetangMap["cate_id"] = array("in","6");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "人物";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}	
							break;
						
               			case 'yk_num':
							$xuetangMap["cate_id"] = array("in","3");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "翼鲲";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}	
							break;
               			case 'jl_num':
							//不想改了~~
							$xuetangMap["cate_id"] = array("in","27");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "国际交流";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}	
							break;
               			case 'xues_num':
							$xuetangMap["cate_id"] = array("in","7");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "学声嘹亮";
							}	
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}	
							break;
               			case 'jy_num':
							$xuetangMap["cate_id"] = array("in","8");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "就业";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}	
							break;
						case 'yy_num':
							$xuetangMap["cate_id"] = array("in","10");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "英语";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}
							break;
               			case 'sh_num':
							$xuetangMap["cate_id"] = array("in","12");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "生活";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}	
							break;
						case 'zn_num':
							$xuetangMap["cate_id"] = array("in","19");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "办事指南";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}	
							break;
							
               			case 'bd_num':
							$xuetangMap["cate_id"] = array("in","20");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "百问百答";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}	
							break;
						case 'gd_num':
							$xuetangMap["cate_id"] = array("in","13");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "最美工大";
							}
							for($i=0;$i<3;$i++,$iterator_items)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}	
							break;
               			case 'yh_num':
							$xuetangMap["cate_id"] = array("in","15");
							$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
							for($i=0;$i<3;$i++)
							{
								$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
								$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
								$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$triMaxItem;
								$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
								$myArticleList[$i]["category"] = "优惠";
							}
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $myArticleList[$i];
							}	
							break;
						case 'xg_num':
							$xmap['cate_id']=1;
							$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($limitNum)->select();

							for($i=0;$i<3;$i++)
							{
									$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
									$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
									$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
									$paArticleList[$i]["view_count"] = "*";
							}

			
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $paArticleList[$i];
							}	
							break;
               			case 'xgd_num':
							$xmap['cate_id']=7;
							$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($limitNum)->select();

							for($i=0;$i<3;$i++)
							{
								$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
								$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
								$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
								$paArticleList[$i]["view_count"] = "*";
							}

			
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $paArticleList[$i];
							}	
							break;
						case 'sanh_num':
							$xmap['cate_id']=3;
							$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($limitNum)->select();

							for($i=0;$i<3;$i++)
							{
								$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
								$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
								$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
								$paArticleList[$i]["view_count"] = "*";
							}

			
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $paArticleList[$i];
							}	
							break;
               			case 'quan_num':
							$xmap['cate_id']=8;
							$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($limitNum)->select();

							for($i=0;$i<3;$i++)
							{
								$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
								$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
								$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
								$paArticleList[$i]["view_count"] = "*";
							}

			
							for($i=0;$i<3;$i++,$iterator_items++)
							{
								$articleList[$iterator_items] = $paArticleList[$i];
							}	
						    break;
						
							
							
					}
				}
				//推荐的文章乱序一下
				for($x = 0;$x <9; $x++)
				{
					$articleListTemp[$x] = $articleList[$x];
				}
				$articleList[0] = $articleListTemp[0];
				$articleList[1] = $articleListTemp[3];
				$articleList[2] = $articleListTemp[6];
				$articleList[3] = $articleListTemp[1];
				$articleList[4] = $articleListTemp[4];
				$articleList[5] = $articleListTemp[7];
				$articleList[6] = $articleListTemp[2];
				$articleList[7] = $articleListTemp[5];
				$articleList[8] = $articleListTemp[8];	
			}
			break;

		case "tongzhi":
		/*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
		/*1.找到对应的标签进行加1操作*/

            //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 1;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['tz_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set tz_num = '$value' where openid = '$open_id'");
				}
			//--!20150304
            

	
				$tag = "tongzhi";
				$tongzhiMap["cate_id"] = array("in","6,26");
				$myArticleList = $ArticleDB->where($tongzhiMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "通知";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '通知'; // 修改
				$this->HeadTitle = "-通知";
				break;
			case "xueshu":
    	/*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
		/*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 1;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['xs_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set xs_num = '$value' where openid = '$open_id'");
				}
		//--!20150304



	
		$tag = "xueshu";
				$xuetangMap["cate_id"] = array("in","1");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "学术";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '学术'; // 修改
				$this->HeadTitle = "-学术";
				break;
	    case "xiaohua":
    	/*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
		/*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 1;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['xh_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set xh_num = '$value' where openid = '$open_id'");
				}
		//--!20150304
            


			    $tag = "xiaohua";
				$xiaohuaMap["cate_id"] = array("in","11");
				$myArticleList = $ArticleDB->where($xiaohuaMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "笑话";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '笑话'; // 修改
				$this->HeadTitle = "-笑话";
				break;


			case "xuetang":
			/*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
			/*1.找到对应的标签进行加1操作*/
	
            /*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	        /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 1;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['xt_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set xt_num = '$value' where openid = '$open_id'");
				}
		//--!20150304

				$tag = "xuetang";
				$xuetangMap["cate_id"] = array("in","5");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "学堂";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '学堂'; // 修改
				$this->HeadTitle = "-学堂";
				break;


			case "jingsai":
			
    	/*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
		/*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 1;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['js_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set js_num = '$value' where openid = '$open_id'");
				}
		//--!20150304

				$tag = "jingsai";
				$xuetangMap["cate_id"] = array("in","2");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "竞赛";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '竞赛'; // 修改
				$this->HeadTitle = "-竞赛";
				break;

			case "huodong":
			/*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	       /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 1;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['hd_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set hd_num = '$value' where openid = '$open_id'");
				}
		//--!20150304
	
			    $tag = "huodong";
				$xuetangMap["cate_id"] = array("in","9");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "活动";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '活动'; // 修改
				$this->HeadTitle = "-活动";
				break;

			
			case "renwu":
			/*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	        /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 1;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['rw_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set rw_num = '$value' where openid = '$open_id'");
				}
		//--!20150304
	
				$tag = "renwu";
				$xuetangMap["cate_id"] = array("in","6");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "人物";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '人物'; // 修改
				$this->HeadTitle = "-人物";
				break;

		case "yikun":
		/*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
		/*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 1;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['yk_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set yk_num = '$value' where openid = '$open_id'");
				}
		//--!20150304
	
			$tag = "yikun";
				$xuetangMap["cate_id"] = array("in","3");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "翼鲲";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '翼鲲'; // 修改
				$this->HeadTitle = "-翼鲲";
				break;
		

			case "jiaoliu":
			/*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	        /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 1;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['jl_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set jl_num = '$value' where openid = '$open_id'");
				}
		//--!20150304
	
				$tag = "jiaoliu";
				//不想改了~~
				$xuetangMap["cate_id"] = array("in","27");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "国际交流";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '国际交流'; // 修改
				$this->HeadTitle = "-国际交流";
				break;

		
			case "xuesheng":
			/*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	        /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 1;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['xues_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set xues_num = '$value' where openid = '$open_id'");
				}
		//--!20150304
	
			$tag = "xuesheng";
				$xuetangMap["cate_id"] = array("in","7");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "学声嘹亮";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '学声嘹亮'; // 修改
				$this->HeadTitle = "-学声嘹亮";
				break;

			case "jiuye":
			/*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	        /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 1;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['jy_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set jy_num = '$value' where openid = '$open_id'");
				}
		//--!20150304
	
			$tag = "jiuye";
				$xuetangMap["cate_id"] = array("in","8");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "就业";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '就业'; // 修改
				$this->HeadTitle = "-就业";
				break;

		case "yingyu":
		/*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
		/*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 1;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['yy_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set yy_num = '$value' where openid = '$open_id'");
				}
		//--!20150304
	
			$tag = "yingyu";
				$xuetangMap["cate_id"] = array("in","10");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "英语";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '英语'; // 修改
				$this->HeadTitle = "-英语";
				break;


			case "shenghuo":
			/*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	        /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 1;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['sh_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set sh_num = '$value' where openid = '$open_id'");
				}
		//--!20150304

				$tag = "shenghuo";
				$xuetangMap["cate_id"] = array("in","12");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "生活";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '生活'; // 修改
				$this->HeadTitle = "-生活";
				break;

		case "zhinan":
        /*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	    /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 1;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['zn_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set zn_num = '$value' where openid = '$open_id'");
				}
		//--!20150304

				$tag = "zhinan";
				$xuetangMap["cate_id"] = array("in","19");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "办事指南";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '办事指南'; // 修改
				$this->HeadTitle = "-办事指南";
				break;



		case "baida":
    
   		 /*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
		  /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 1;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['bd_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set bd_num = '$value' where openid = '$open_id'");
				}
		//--!20150304



			$tag = "baida";
				$xuetangMap["cate_id"] = array("in","20");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "百问百答";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '百问百答'; // 修改 fuck 大黄
				$this->HeadTitle = "-百问百答";
				break;

			case "gongda":


			/*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	        /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 1;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['gd_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set gd_num = '$value' where openid = '$open_id'");
				}
		//--!20150304

				$tag = "gongda";
				$xuetangMap["cate_id"] = array("in","13");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "最美工大";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '最美工大'; // 修改
				$this->HeadTitle = "-最美工大";
				break;


			case "youhui":

            /*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	        /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 1;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['yh_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set yh_num = '$value' where openid = '$open_id'");
				}
		//--!20150304

				$tag = "youhui";
				$xuetangMap["cate_id"] = array("in","15");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "优惠";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '优惠'; // 修改
				$this->HeadTitle = "-优惠";
				break;



			case "xiaogua":

            /*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	        /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 1;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['xg_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set xg_num = '$value' where openid = '$open_id'");
				}
		//--!20150304

				$tag = "xiaogua";
				$xmap['cate_id']=1;
				$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($limitNum)->select();

					for($i=0;$i<$limitNum;$i++)
					{
						$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
						$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
						$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
						$paArticleList[$i]["view_count"] = "*";
					}

			
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $paArticleList[$i];
				}	
				$this->channelname = '小瓜助手'; // 修改
				$this->HeadTitle = "-小瓜助手";
				break;

			case "xigongda":

            /*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	        /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 1;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['xgd_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set xgd_num = '$value' where openid = '$open_id'");
				}
		//--!20150304


				$tag = "xigongda";
				$xmap['cate_id']=7;
				$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($limitNum)->select();

					for($i=0;$i<$limitNum;$i++)
					{
						$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
						$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
						$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
						$paArticleList[$i]["view_count"] = "*";
					}

			
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $paArticleList[$i];
				}	
				$this->channelname = '西工大'; // 修改
				$this->HeadTitle = "-西工大";
				break;

			case "sanhang":

            /*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	        /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 1;
					$data['quan_num'] = 0;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['sanh_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set sanh_num = '$value' where openid = '$open_id'");
				}
		//--!20150304

				$tag = "sanhang";
				$xmap['cate_id']=3;
				$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($limitNum)->select();

					for($i=0;$i<$limitNum;$i++)
					{
						$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
						$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
						$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
						$paArticleList[$i]["view_count"] = "*";
					}

			
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $paArticleList[$i];
				}	
				$this->channelname = '西北工业大学·三航青年'; // 修改
				$this->HeadTitle = "-西北工业大学·三航青年";
				break;

			case "quan":

            /*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	        /*1.找到对应的标签进行加1操作*/



        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 1;$data['ygzs_num'] = 0;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['quan_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set quan_num = '$value' where openid = '$open_id'");
				}
		//--!20150304

				$tag = "quan";
				$xmap['cate_id']=8;
				$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($limitNum)->select();

					for($i=0;$i<$limitNum;$i++)
					{
						$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
						$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
						$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
						$paArticleList[$i]["view_count"] = "*";
					}

			
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $paArticleList[$i];
				}	
				$this->channelname = '瓜大生活圈'; // 修改
				$this->HeadTitle = "-瓜大生活圈";
				break;

				case "yangongzhisheng":
        /*用户点击后，会相应的对标签记录表对应项进行增加1的操作（策略可以再完善，其他标签是否抑制呢）*/
	    /*1.找到对应的标签进行加1操作*/

        //--20150304
			    $open_id=get_openid();
				//查询数据库中有没有该openid的记录，如果有，直接给该标签+1，如果没有，先插入该openid，在+1
				//$result =  $LabelRecord->where('openid=%d', $open_id)->select;
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				if(empty($result)){
					//插入一条记录
					$data['openid'] = $open_id;
					$data['tz_num'] = 0;$data['xs_num'] = 0;$data['xh_num'] = 0;$data['xt_num'] = 0;$data['js_num'] = 0;
					$data['hd_num'] = 0;$data['rw_num'] = 0;$data['yk_num'] = 0;$data['jl_num'] = 0;$data['xues_num'] = 0;
					$data['jy_num'] = 0;$data['yy_num'] = 0;$data['sh_num'] = 0;$data['zn_num'] = 0;$data['bd_num'] = 0;
					$data['gd_num'] = 0;$data['yh_num'] = 0;$data['xg_num'] = 0;$data['xgd_num'] = 0;$data['sanh_num'] = 0;
					$data['quan_num'] = 0;$data['ygzs_num'] = 1;
					$LabelRecord -> data($data) -> add();
				}
				else{
					$value = $result[0]['ygzs_num'];
					$value = $value +1;
					$LabelRecord->execute("update nl_custom_label_record set ygzs_num = '$value' where openid = '$open_id'");
				}
		//--!20150304

				$tag = "yangongzhisheng";
				$xuetangMap["cate_id"] = array("in","4");
				$myArticleList = $ArticleDB->where($xuetangMap)->order("cTime desc")->limit($limitNum)->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "研工之声";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->channelname = '研工之声'; // 修改
				$this->HeadTitle = "-研工之声";
				break;

		}

		//$this->autoAddClick();

	
		$this->channel = $channel; // 修改
		$this->articleList = $articleList;
		$this->nowdate = 1000*time();
		//$this->category = $channel;
		$this->tag = $tag;


	//获得头像
		$token = get_token();
		$openid = get_openid();		
		$userinfo = getWeixinUserInfo($openid, $token);

		$userList = $Member->where("openid='%s'",$openid)->select();
		//dump($openid);
		$user = $userList[0];

		$headPic= $userinfo['headimgurl'];
		$this->headimg = $headPic;
		$this->nickname = $userinfo['nickname'];

		$this->userjifen = $user['score'];
		$this->uid = $user['uid'];
		// dump($tag);
		$this->assign("openid",$openid);

		$this->display();
	}
	private function autoAddClick()
	{
		
		$MyArticleDB = M("CustomReplyNews");

		
		$myArticleList = $MyArticleDB->select();

		for($i=0;$i<count($paArticleList);$i++)
		{
			if($paArticleList[$i]["view_count"]<800)
			{
				$paArticleList[$i]["view_count"] += (80+rand(10,40));	
			}
			else
			{	
				$paArticleList[$i]["view_count"] += (3+rand(0,5));	
			}

		
		}
		for($i=0;$i<count($myArticleList);$i++)
		{
			if($myArticleList[$i]["view_count"]<800)
			{
				$myArticleList[$i]["view_count"] += (80+rand(10,40));	
			}
			else
			{	
				$myArticleList[$i]["view_count"] += (3+rand(0,5));	
			}
			$MyArticleDB->save($myArticleList[$i]);
		}
	}

	/*获得订阅的栏目,存储于数据库*/
	/*public function getLab()
	{
        $Lab = I('post.lab');
        $this->assign('lab',$Lab);
		$this->display();
	}*/

	/*频道选择*/
	public function channels(){

		$this->display();
	}

	/*视频播放页*/
	public function videoview(){

		$this->display();
	}

	/*详情页*/
	public function view(){

		$this->display();
	}

	/*搜索*/
	public function search(){

		$ArticleDB = M ( "CustomReplyNews" ); // 自己的文章
		$channel = I ( 'get.channel' );		 //获取类别
		$keyWord = I ( 'get.key' );			//获取关键字
		$keywords = split ( '[ ,.-/]', $keyWord );//处理关键字
		$keyWordNum = count ( $keywords );			//关键字的个数
		
	/*提取有用的关键字信息*/
		for($i = 0, $j = 0; $i < $keyWordNum; $i ++) {
			if ($keywords [$i] != '') {
				$useKey[$j] = $keywords [$i];
				$j++;
			}
		}
	/*匹配关键字的模糊查询条件*/
		for($i = 0, $j = 0; $i < $keyWordNum; $i ++) {
			if ($keywords [$i] != '') {
				$likeString [$j] = "%".$keywords [$i]."%";
				$j++;
			}
		}
		$map ['title'] = array (
				'like',
				$likeString,
				'OR' 
		);
	/*按类别进行搜索*/
		switch ($channel) {
			case "" :
			case "__all__" : // 还需要改进，最好是把今天的文章全部罗列出来，然后全部按照时间来排
			                
				// $paArticleList = $PaArticleDB->order("pubdate desc")->limit($limitNum)->select();
				$myArticleList = $ArticleDB->where($map)->order ( "cTime desc" )->select ();
				// 把没有的参数补充完整，这是因为两个数据表设计的不统一
				/*
				 * for($i=0;$i<$limitNum;$i++) { $paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"]; $paArticleList[$i]["cTime"] = $paArticleList[$i]["pubdate"]; $paArticleList[$i]["category"] = $paArticleList[$i]["author"]; }
				 */
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
				    $myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "瓜大生活圈";
				}
				// 把两个混合在一起；
				for($i = 0; $i < $listNum; $i ++) {
					$articleList [$i] = $myArticleList [$i];
					// $articleList[$i*2+1] = $paArticleList[$i];
				}
				break;
			
			case "tongzhi" :
				$tongzhiMap ["cate_id"] = array (
						"in",
						"6,26" 
				);
				$myArticleList = $ArticleDB->where ( $tongzhiMap )->where($map)->order ( "cTime desc" )->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "通知";
				}
				for($i = 0; $i < $listNum; $i ++) {
					$articleList [$i] = $myArticleList [$i];
				}
				break;
			
			case "xiaohua" :
				$xiaohuaMap ["cate_id"] = array (
						"in",
						"11" 
				);
				$myArticleList = $ArticleDB->where ( $xiaohuaMap )->where($map)->order ( "cTime desc" )->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "笑话";
				}
				for($i = 0; $i < $listNum; $i ++) {
					$articleList [$i] = $myArticleList [$i];
				}
				break;
			
			case "xueshu" :
				$xuetangMap ["cate_id"] = array (
						"in",
						"1" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->where($map)->order ( "cTime desc" )->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "学术";
				}
				for($i = 0; $i < $listNum; $i ++) {
					$articleList [$i] = $myArticleList [$i];
				}
				break;
			
			case "xuetang" :
				$xuetangMap ["cate_id"] = array (
						"in",
						"3,7,5,6,10,12" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->where($map)->order ( "cTime desc" )->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "学堂";
				}
				for($i = 0; $i < $listNum; $i ++) {
					$articleList [$i] = $myArticleList [$i];
				}
				break;
			case "jingsai":
				$xuetangMap["cate_id"] = array("in","2");
				$myArticleList = $ArticleDB->where($xuetangMap)->where($map)->order("cTime desc")->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "竞赛";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				break;

			case "huodong":
				$xuetangMap["cate_id"] = array("in","9");
				$myArticleList = $ArticleDB->where($xuetangMap)->where($map)->order("cTime desc")->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "活动";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				break;

			
			case "renwu":
				$xuetangMap["cate_id"] = array("in","6");
				$myArticleList = $ArticleDB->where($xuetangMap)->where($map)->order("cTime desc")->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "人物";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				break;

			case "yikun":
				$xuetangMap["cate_id"] = array("in","3");
				$myArticleList = $ArticleDB->where($xuetangMap)->where($map)->order("cTime desc")->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "翼鲲";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				break;
		

			case "jiaoliu":
				$xuetangMap["cate_id"] = array("in","27");
				$myArticleList = $ArticleDB->where($xuetangMap)->where($map)->order("cTime desc")->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "国际交流";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				$this->HeadTitle = "-国际交流";
				break;

		
			case "xuesheng":
				$xuetangMap["cate_id"] = array("in","7");
				$myArticleList = $ArticleDB->where($xuetangMap)->where($map)->order("cTime desc")->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "学声嘹亮";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				break;

			case "jiuye":
				$xuetangMap["cate_id"] = array("in","8");
				$myArticleList = $ArticleDB->where($xuetangMap)->where($map)->order("cTime desc")->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "就业";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				break;

			case "yingyu":
				$xuetangMap["cate_id"] = array("in","10");
				$myArticleList = $ArticleDB->where($xuetangMap)->where($map)->order("cTime desc")->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "英语";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				break;


			case "shenghuo":
				$tag = "shenghuo";
				$xuetangMap["cate_id"] = array("in","12");
				$myArticleList = $ArticleDB->where($xuetangMap)->where($map)->order("cTime desc")->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "生活";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				break;

			case "zhinan":
				$tag = "zhinan";
				$xuetangMap["cate_id"] = array("in","19");
				$myArticleList = $ArticleDB->where($xuetangMap)->where($map)->order("cTime desc")->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "办事指南";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				break;



			case "baida":
				$tag = "baida";
				$xuetangMap["cate_id"] = array("in","20");
				$myArticleList = $ArticleDB->where($xuetangMap)->where($map)->order("cTime desc")->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "百问百答";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				break;

			case "gongda":
				$tag = "gongda";
				$xuetangMap["cate_id"] = array("in","13");
				$myArticleList = $ArticleDB->where($xuetangMap)->where($map)->order("cTime desc")->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "最美工大";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				break;


			case "youhui":
			//fuck 这么多类别，有人看么？
				$xuetangMap["cate_id"] = array("in","15");
				$myArticleList = $ArticleDB->where($xuetangMap)->where($map)->order("cTime desc")->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "优惠";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				break;

		 case "yangongzhisheng":
				$xuetangMap["cate_id"] = array("in","4");
				$myArticleList = $ArticleDB->where($xuetangMap)->where($map)->order("cTime desc")->select();
				for($i=0;$i<$limitNum;$i++)
				{
					$myArticleList[$i]["picurl"] = get_cover_url($myArticleList[$i]["cover"]);
					$myArticleList[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList[$i]["id"];
					$myArticleList[$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList[$i]["friendlyDate"] = $myArticleList[$i]["cTime"];
					$myArticleList[$i]["category"] = "研工之声";
				}
				for($i=0;$i<$limitNum;$i++)
				{
					$articleList[$i] = $myArticleList[$i];
				}	
				break;

		}
		
		//替换标题中有关键字的地方为红色，同时计数每个标题的页面匹配度
		$keyNum = count($useKey);
		for($i = 0;$i < $listNum;$i++){
			$articleList[$i]['pageRank'] = 0;
			for($j=0;$j < $keyNum;$j++){
				$before = $articleList[$i]['title'];
				$articleList[$i]['title'] = str_replace($useKey[$j],"<font color='#FF0000'>".$useKey[$j]."</font>",$articleList[$i]['title']);
				if(strcmp($before, $articleList[$i]['title'])!= 0){
					$articleList[$i]['pageRank']++;
				}		
			}
		}
		//按页面排名选择排序
		for($i = 0;$i < $listNum;$i++){
			$maxI = $i;
			for($j=$i+1;$j<$listNum;$j++){
				if($articleList[$j]['pageRank']>$articleList[$maxI]['pageRank']){
					$maxI = $j;
				}
			}
			$temp = $articleList[$i];
			$articleList[$i] = $articleList[$maxI];
			$articleList[$maxI] = $temp;
		}	
		$this->ajaxReturn ( $articleList, 'JSON' );

		//$this->display();
	}

	/*栏目订阅*/
	public function guanzhu(){

		$this->display();
	}

	/*订阅号订阅*/
	public function guanzhu2(){

		$this->display();
	}

	//暂时无用，待前端迁移过来，还需要改其他地方的链接形式。
	public function detail()
	{
		$map['id'] = I('get.id');
		$CustomReplyNews = M("CustomReplyNews");
		$news = $CustomReplyNews->where($map)->find();
		
		$news['create_date'] = date('Y-m-d G:i:s',$news['cTime']);
		
		//新增点击量
		($news['view_count']<800)?($news['view_count']+=rand(30,100)):($news['view_count']+=rand(1,5));
		$CustomReplyNews->save($news);
		
		$news['pic'] = get_cover_url ( $news ['cover'] );
		$this->assign("news",$news);
		
		$this->display();
	}


	//文章加载更多
	public function getmore(){
		$page = I('get.page');
		$channel = I('get.channel');
		$ArticleDB = M("CustomReplyNews");//自己的文章
		$paarticle = M("PaArticle","nl_",'DB_CONFIG_NPULIFE_DATA');
		
		$LabelRecord = M("CustomLabelRecord");//记录点击数   20150304

		$limitNum2 = 5;
		$num = 10;
		switch ($channel) {
			
			case "":	//还需要改进，最好是把今天的文章全部罗列出来，然后全部按照时间来排
				//增加的变量
				$deCategory = 3; //默认推荐3个类别的最新新闻
				$faCategory = $limitNum / $deCategory; //默认每个类别推荐新闻的数量
				$deAll  = $deCategory * $faCategory; //默认推荐总共显示的新闻数量
                //判断数据库中是否有该用户的记录
				$open_id=get_openid();
				$result =  $LabelRecord->query("select * from nl_custom_label_record where openid = '$open_id'");
				

				if(empty($result)) {
					$myArticleList1 = $ArticleDB->order("cTime desc")->limit($page*$num/2,$num/2)->select();	
					$paArticleList = $paarticle->order("pudate desc")->limit($page*$num/2,$num/2)->select();
					//把没有的参数补充完整，这是因为两个数据表设计的不统一
					for($i=0;$i<$limitNum2;$i++)
					{
						$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
						$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
						$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
						$paArticleList[$i]["view_count"] = "*";
					}
					for($i=0;$i<$limitNum2;$i++)
					{
						$myArticleList1[$i]["picurl"] = get_cover_url($myArticleList1[$i]["cover"]);
						$myArticleList1[$i]["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/".$myArticleList1[$i]["id"];
						$myArticleList1[$i]["url"] = $myArticleList1[$i]["url"]."/cate/quan_num";
						$myArticleList1[$i]["friendlyDate"] = $myArticleList1[$i]["cTime"];
						$myArticleList1[$i]["category"] = "瓜大生活圈";
					}
					//把两个混合在一起；
					for($i=0;$i<$limitNum2;$i++)
					{
						$myArticleList[$i*2] = $myArticleList1[$i];
						$myArticleList[$i*2+1] = $paArticleList[$i];
					}		
					// $articleList =$myArticleList;
				}
				else {
					//加载more
					$label = array('tz_num', 'xs_num', 'xh_num', 'xt_num', 'js_num', 'hd_num', 'rw_num', 'yk_num', 'jl_num', 'xues_num', 'jy_num', 'yy_num', 'sh_num', 'zn_num', 'bd_num', 'gd_info', 'yh_num', 'xg_num', 'xgd_num', 'sanh_num', 'quan_num','ygzs_num');
					$triMax[0] = $label[0];$triMax[1] = $label[1];$triMax[2] = $label[2];
	
					for($i = 0;$i<2;$i++)
						for($j = $i+1;$j<=2;$j++)
						{
							if($result[0][$triMax[$i]] <$result[0][$triMax[$j]])
							{
								$temp=$triMax[$j];
								$triMax[$j]=$triMax[$i];
								$triMax[$i]=$temp;
							}
						}
				
					//for循环得到出现频率最高的三项
					//for($x=3; $x<4; $x++)
					for($x=3; $x<count($label);$x++)
					{
						for($i=0;$i<3;$i++)
						{
							if($result[0][$label[$x]] > $result[0][$triMax[$i]])
							{
								for($j = 2;$j >= $i; $j--)
								{
									$triMax[$j] = $triMax[$j-1];
								}
								$triMax[$i] = $label[$x];
								break;
							}
						}										
					}	
					$iterator_items = 0;//文章0
					foreach($triMax as $triMaxItem)
					{
						//$triMaxItem使用switch case选取类别中的文章
						switch($triMaxItem)
						{
							case "":break;
							case "tz_num":
								$tongzhiMap ["cate_id"] = array (
										"in",
										"6,26" 
								);
								$myArticleListT = $ArticleDB->where ( $tongzhiMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "通知";
								}
				
				                for($i=0;$i<$listNum;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}				
								break;
							case "xs_num":
								$xuetangMap ["cate_id"] = array (
										"in",
										"1" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "学术";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}	
								break;
							case "xh_num":
								$xiaohuaMap ["cate_id"] = array (
										"in",
										"11" 
								);
								$myArticleListT = $ArticleDB->where ( $xiaohuaMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "笑话";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}		
								break;
							case "xt_num":
								$xuetangMap ["cate_id"] = array (
										"in",
										"3,7,5,6,10,12" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "学堂";
								}
			                    for($i=0;$i<$listNum;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}	
								break;
							case 'js_num':
								$xuetangMap ["cate_id"] = array (
										"in",
										"2" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "竞赛";
								}
								for($i=0;$i<$listNum;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}	
								break;
							case 'hd_num':	
								$xuetangMap ["cate_id"] = array (
										"in",
										"9" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "活动";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}	
								break;
							case 'rw_num':
								$xuetangMap ["cate_id"] = array (
										"in",
										"6" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "人物";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}		
								break;
							case 'yk_num':
								$xuetangMap ["cate_id"] = array (
										"in",
										"3" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "翼鲲";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}	
								break;
							case 'jl_num':
								$xuetangMap ["cate_id"] = array (
										"in",
										"27" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "国际交流";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}	
								break;
							case 'xues_num':
								$xuetangMap ["cate_id"] = array (
										"in",
										"7" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "学声嘹亮";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}	
								break;
							case 'jy_num':
								$xuetangMap ["cate_id"] = array (
										"in",
										"8" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "就业";
								
				                }
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}	
								break;
							case 'yy_num':
								$xuetangMap ["cate_id"] = array (
										"in",
										"10" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "英语";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}
								break;
							case 'sh_num':
								$xuetangMap ["cate_id"] = array (
										"in",
										"12" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "生活技巧";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}
								break;
							case 'zn_num':
						        $xuetangMap ["cate_id"] = array (
										"in",
										"19" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "办公指南";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}
								break;

                            case 'ygzs_num':
								$xuetangMap ["cate_id"] = array (
										"in",
										"4" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "研工之声";
								}
								for($i=0;$i<$listNum;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}	
								break;
								
							case 'bd_num':
								$xuetangMap ["cate_id"] = array (
										"in",
										"20" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "白问百答";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}
								break;
							case 'gd_num':
								$xuetangMap ["cate_id"] = array (
										"in",
										"13" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "最美工大";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}	
								break;
							case 'yh_num':
								$xuetangMap ["cate_id"] = array (
										"in",
										"15" 
								);
								$myArticleListT = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*3,3)->select ();
								$listNum = count($myArticleListT);
								for($i = 0; $i < $listNum; $i ++) {
									$myArticleListT [$i] ["picurl"] = get_cover_url ( $myArticleListT [$i] ["cover"] );
									$myArticleListT [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleListT [$i] ["id"];
									$myArticleListT [$i]["url"] = $myArticleListT [$i]["url"] ."/cate/".$triMaxItem;
									$myArticleListT [$i] ["friendlyDate"] = $myArticleListT [$i] ["cTime"];
									$myArticleListT [$i] ["category"] = "优惠";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $myArticleListT[$i];
								}
								break;
							case 'xg_num':
								$xmap['cate_id']=1;
								$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($page*3,3)->select();
								$listNum = count($paArticleList);
								for($i=0;$i<$listNum;$i++)
								{
									$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
									$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
									$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
									$paArticleList[$i]["view_count"] = "*";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $paArticleList[$i];
								}	
								break;
							case 'xgd_num':
								$xmap['cate_id']=7;
								$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($page*3,3)->select();
								$listNum = count($paArticleList);
								for($i=0;$i<$listNum;$i++)
								{
									$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
									$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
									$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
									$paArticleList[$i]["view_count"] = "*";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $paArticleList[$i];
								}	
								break;
							case 'sanh_num':
								$xmap['cate_id']=3;
								$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($page*3,3)->select();
								$listNum = count($paArticleList);
								for($i=0;$i<$listNum;$i++)
								{
									$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
									$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
									$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
									$paArticleList[$i]["view_count"] = "*";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $paArticleList[$i];
								}	
								break;
							case 'quan_num':
								$xmap['cate_id']=3;
								$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($page*3,3)->select();
								$listNum = count($paArticleList);
								for($i=0;$i<$listNum;$i++)
								{
									$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
									$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
									$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
									$paArticleList[$i]["view_count"] = "*";
								}
								for($i=0;$i<3;$i++,$iterator_items++)
								{
									$myArticleList[$iterator_items] = $paArticleList[$i];
								}
								break;
						}
					}	
				}
				//推荐的文章乱序一下
				for($x = 0;$x <9; $x++)
				{
					$articleListTemp[$x] = $myArticleList[$x];
				}
				$myArticleList[0] = $articleListTemp[0];
				$myArticleList[1] = $articleListTemp[3];
				$myArticleList[2] = $articleListTemp[6];
				$myArticleList[3] = $articleListTemp[1];
				$myArticleList[4] = $articleListTemp[4];
				$myArticleList[5] = $articleListTemp[7];
				$myArticleList[6] = $articleListTemp[2];
				$myArticleList[7] = $articleListTemp[5];
				$myArticleList[8] = $articleListTemp[8];				
                break;


			
			case "tongzhi" :
				$tongzhiMap ["cate_id"] = array (
						"in",
						"6,26" 
				);
				$myArticleList = $ArticleDB->where ( $tongzhiMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i]["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "通知";
				}
			
				break;

			case "xueshu" :
				$xuetangMap ["cate_id"] = array (
						"in",
						"1" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "学术";
				}
			
				break;
			
			case "xiaohua" :
				$xiaohuaMap ["cate_id"] = array (
						"in",
						"11" 
				);
				$myArticleList = $ArticleDB->where ( $xiaohuaMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "笑话";
				}
			
				break;
			
			
			
			case "xuetang" :
				$xuetangMap ["cate_id"] = array (
						"in",
						"3,7,5,6,10,12" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "学堂";
				}
			
				break;

			case "jingsai":
			
			$xuetangMap ["cate_id"] = array (
						"in",
						"2" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "竞赛";
				}
			
				break;

			case "huodong":
					$xuetangMap ["cate_id"] = array (
						"in",
						"9" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "活动";
				}
			
				break;

			
			case "renwu":
				$xuetangMap ["cate_id"] = array (
						"in",
						"6" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "人物";
				}
			
				break;


			case "yikun":
					$xuetangMap ["cate_id"] = array (
						"in",
						"3" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "翼鲲";
				}
			
				break;

		

			case "jiaoliu":
					$xuetangMap ["cate_id"] = array (
						"in",
						"27" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "国际交流";
				}
			
				break;


		
			case "xuesheng":
				$xuetangMap ["cate_id"] = array (
						"in",
						"7" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "学声嘹亮";
				}
			
				break;


			case "jiuye":
					$xuetangMap ["cate_id"] = array (
						"in",
						"8" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "就业";
				}
			
				break;


			case "yingyu":
					$xuetangMap ["cate_id"] = array (
						"in",
						"10" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "英语";
				}
			
				break;



			case "shenghuo":
				$xuetangMap ["cate_id"] = array (
						"in",
						"12" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "生活技巧";
				}
			
				break;


			case "zhinan":
					$xuetangMap ["cate_id"] = array (
						"in",
						"19" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "办公指南";
				}
			
				break;




			case "baida":
				$xuetangMap ["cate_id"] = array (
						"in",
						"20" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "白问百答";
				}
			
				break;

			case "gongda":
					$xuetangMap ["cate_id"] = array (
						"in",
						"13" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "最美工大";
				}
			
				break;



			case "youhui":
			//fuck 这么多类别，有人看么？
					$xuetangMap ["cate_id"] = array (
						"in",
						"15" 
				);
				$myArticleList = $ArticleDB->where ( $xuetangMap )->order ( "cTime desc" )->limit($page*$num,$num)->select ();
				$listNum = count($myArticleList);
				for($i = 0; $i < $listNum; $i ++) {
					$myArticleList [$i] ["picurl"] = get_cover_url ( $myArticleList [$i] ["cover"] );
					$myArticleList [$i] ["url"] = "http://wechat.npulife.com/Home/NewSchoolHome/detail/id/" . $myArticleList [$i] ["id"];
					$myArticleList [$i] ["url"] = $myArticleList[$i]["url"]."/cate/".$channel;
					$myArticleList [$i] ["friendlyDate"] = $myArticleList [$i] ["cTime"];
					$myArticleList [$i] ["category"] = "优惠";
				}
			
				break;



			case "xiaogua":
				
				$xmap['cate_id']=1;
				$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($page*$num,$num)->select();
				$listNum = count($paArticleList);
					for($i=0;$i<$listNum;$i++)
					{
						$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
						$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
						$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
						$paArticleList[$i]["view_count"] = "*";
					}

			
				for($i=0;$i<$listNum;$i++)
				{
					$myArticleList[$i] = $paArticleList[$i];
				}	
				$this->channelname = '小瓜助手'; // 修改
				$this->HeadTitle = "-小瓜助手";
				break;

				case "xigongda":
				$xmap['cate_id']=7;
				$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($page*$num,$num)->select();
				$listNum = count($paArticleList);
					for($i=0;$i<$listNum;$i++)
					{
						$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
						$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
						$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
						$paArticleList[$i]["view_count"] = "*";
					}

			
				for($i=0;$i<$listNum;$i++)
				{
					$myArticleList[$i] = $paArticleList[$i];
				}	
				$this->channelname = '西工大'; // 修改
				$this->HeadTitle = "-西工大";
				break;

				case "sanhang":
				$xmap['cate_id']=3;
				$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($page*$num,$num)->select();
				$listNum = count($paArticleList);
					for($i=0;$i<$listNum;$i++)
					{
						$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
						$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
						$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
						$paArticleList[$i]["view_count"] = "*";
					}

			
				for($i=0;$i<$listNum;$i++)
				{
					$myArticleList[$i] = $paArticleList[$i];
				}	
				$this->channelname = '西北工业大学·三航青年'; // 修改
				$this->HeadTitle = "-西北工业大学·三航青年";
				break;

			case "quan":
				$xmap['cate_id']=8;
				$paArticleList = $paarticle->order("pudate desc")->where($xmap)->limit($page*$num,$num)->select();
				$listNum = count($paArticleList);
					for($i=0;$i<$listNum;$i++)
					{
						$paArticleList[$i]["friendlyDate"] = $paArticleList[$i]["cTime"];
						$paArticleList[$i]["cTime"] = $paArticleList[$i]["pudate"];
						$paArticleList[$i]["category"] = $paArticleList[$i]["wxname"];
						$paArticleList[$i]["view_count"] = "*";
					}

			
				for($i=0;$i<$listNum;$i++)
				{
					$myArticleList[$i] = $paArticleList[$i];
				}	
				$this->channelname = '瓜大生活圈'; // 修改
				$this->HeadTitle = "-瓜大生活圈";
				break;


		}

		$this->ajaxReturn ( $myArticleList, 'JSON' );

		
	}
}
?>