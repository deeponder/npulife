<?php

namespace Addons\erweimachajian\Model;

use Home\Model\WeixinModel;

/**
 * erweimachajian的微信模型
 */
class WeixinAddonModel extends WeixinModel {

    function reply($dataArr, $keywordArr = array())
    {
        $config = getAddonConfig('erweimachajian'); // 获取后台插件的配置参数
        //dump($config);

    }

    // 取消关注公众号事件
    public function unsubscribe()
    {

        return true;


    }

    // 扫描带参数二维码事件
    public function scan($data)
    {
        /*////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    $SmartScene = M("SmartScene","nl_","DB_CONFIG_LIFE");

                    for($i=18771;$i<18783;$i++)
                    {
                        $ssData['scene_id'] = $i;
                        $theS = $SmartScene->where($ssData)->find();
                        $theS['area'] = "西北工业大学友谊校区";
                        //$SmartScene->where($theS)->save();
                        $SmartScene->where('scene_id='.$i)->save($theS);
                    }
                    for($i=10000;$i<18771;$i++)
                    {
                        $ssData['scene_id'] = $i;
                        $theS = $SmartScene->where($ssData)->find();
                        if($theS['building']=="星E"
                            ||$theS['building']=="星F"
                            ||$theS['building']=="星G")
                        {
                            $theS['area'] = "西北工业大学长安校区";
                        }
                        if($theS['building']=="6号楼"
                            ||$theS['building']=="5号楼"
                            ||$theS['building']=="4号楼"
                            ||$theS['building']=="3号楼"
                            ||$theS['building']=="2号楼"
                            ||$theS['building']=="1号楼A座"
                            ||$theS['building']=="旺1"
                            ||$theS['building']=="旺2"
                            ||$theS['building']=="旺3")
                        {
                            $theS['area'] = "西北工业大学友谊校区";
                        }
                        //$SmartScene->where($theS)->save();
                        $SmartScene->where('scene_id='.$i)->save($theS);
                    }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////*/
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ($data['EventKey'] > 10000 && $data['EventKey'] <= 18770)
        {
            //查找是哪个宿舍。

            $SmartScene = M("SmartScene", "nl_", "DB_CONFIG_LIFE");
            $sData['scene_id'] = $data['EventKey'];

            $mydir = "D:/wamp/www/Tool/Public/Erweima/i/AllQrCode";//取得图片路径，放到一起方便查找
            $res = $this->searchFile($mydir, $sData['scene_id']);
            //$this->replyText($res);
            $res1 = strstr($res, '_', true);
            $res1 = $res1 . ".jpg";
            //$this->replyText($res1);
            //return;
            $TheSmartScene = $SmartScene->where($sData)->find();

            $dormArticles[0] = array('Title' => '智慧生活圈-' . $TheSmartScene['building'] . $TheSmartScene['dorm_no'] . '欢迎您！进入我们的宿舍主页',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/Tool/Tip/' . $res,
                'Url' => 'http://wechat.npulife.com/Tool/Home/Erweima/index?id=' . $res1);
            //最好查到瓜大电台最新一期节目，显示在上面。
            $Diantai = M("Diantai", "nl_", "DB_CONFIG_NPULIFE_DATA");
            $diantaiList = $Diantai->order('createdate desc')->select();
            $lastDiantai = $diantaiList[0];
            $dormArticles[1] = array('Title' => '瓜大电台' . '(最新:' . $lastDiantai['songname'] . '，主播:' . $lastDiantai['nickname'] . ')',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/SmartSchool/Public/i/diantai.png',
                'Url' => 'http://wechat.npulife.com/Tool/index.php/Home/Diantai/listDiantai');
            /*
            $dormArticles[2] = array('Title' => '饿了，订瓜大外卖',
                                    'Description'=> '',
                                    'PicUrl'=> 'http://wechat.npulife.com/Tool/Tip/'.$res,
                                    'Url'=> '');
            */
            $dormArticles[2] = array('Title' => '最新校园通知',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/Tool/Erweima/' . $res1,
                'Url' => 'http://wechat.npulife.com/Home/BigSchool?channel=tongzhi');

            //地图上实时显示校车的位置。
            $dormArticles[3] = array('Title' => '实时校车位置',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/Tool/Erweima/' . $res1,
                'Url' => 'http://wechat.npulife.com/Tool/Home/Busloc/map');


            //   校车排队情况
            // $dormArticles[4] = array('Title' => 'test');

            $this->replyNews($dormArticles);

            //$this->replyText("欢迎扫描~~");
        }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ($data['EventKey'] > 18770 && $data['EventKey'] <= 18780)
        {
            //查找是哪个宿舍。
            $SmartScene = M("SmartScene", "nl_", "DB_CONFIG_LIFE");
            $sData['scene_id'] = $data['EventKey'];
            $TheSmartScene = $SmartScene->where($sData)->find();

            $dormArticles[0] = array('Title' => '智慧生活圈-' . $TheSmartScene['building'] . $TheSmartScene['dorm_no'] . '欢迎您！进入我的主页',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/SmartSchool/Public/i/jidianxueyuan.png',
                'Url' => 'http://jidian.nwpu.edu.cn/index.htm');

            $dormArticles[1] = array('Title' => '通知公告',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/SmartSchool/Public/i/tongzhi.jpg',
                'Url' => 'http://wechat.npulife.com/Home/BigSchool?channel=tongzhi');

            //最好查到瓜大电台最新一期节目，显示在上面。
            $Diantai = M("Diantai", "nl_", "DB_CONFIG_NPULIFE_DATA");
            $diantaiList = $Diantai->order('createdate desc')->select();
            $lastDiantai = $diantaiList[0];
            $dormArticles[2] = array('Title' => '瓜大电台' . '(最新:' . $lastDiantai['songname'] . '，主播:' . $lastDiantai['nickname'] . ')',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/SmartSchool/Public/i/diantai.png',
                'Url' => 'http://wechat.npulife.com/Tool/index.php/Home/Diantai/listDiantai');

            $dormArticles[3] = array('Title' => '来杯咖啡，天工沙龙外卖',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/Addons/erweimachajian/Public/tiangonglogo.jpg',
                'Url' => 'http://wechat.npulife.com/SmartSchool/Coffee/pid=1');


            $this->replyNews($dormArticles);
        }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $qrmember = M('ErweimaMember');
        $runtable = M('Nightrun');
        $omap['openid'] = $data['FromUserName'];
        $vmap['openid'] = $data['FromUserName'];
        $vmap['eventkey'] = 101;
        $mmap['openid'] = $data['FromUserName'];
        $mmap['eventkey'] = 100;
        $outtime = $qrmember->where($vmap)->getField('createdate', true);
        $intime = $qrmember->where($mmap)->getField('createdate', true);
        $onum = count($outtime);
        $inum = count($intime);
        $lastintime = $qrmember->where($mmap)->order('createdate DESC')->limit('1')->getField('createdate', true);
        $ltime = strtotime($lastintime[0]);


        //毕业晚会二维码活动
        if ($data['EventKey'] == 100)
        {
            $leave[0] = array('Title' => '【MV】工大不说再见--献给西北工业大学2015全体毕业生',
                'Description' => ' ',
                'PicUrl' => 'http://wechat.npulife.com/Public/graduate/cover.jpg',
                'Url' => 'http://mp.weixin.qq.com/s?__biz=MjM5OTI4NjUxMw==&mid=204149150&idx=1&sn=5e4423df60f81718f79895c732e01884#rd');

            $leave[1] = array('Title' => '毕业季 | 放飞自我，不忘初心，专属于我们自己的微电影',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/Public/graduate/q.jpg',
                'Url' => 'http://mp.weixin.qq.com/s?__biz=MjM5OTI4NjUxMw==&mid=204290282&idx=2&sn=14169e939abd2b04a618e0adbed40e9f#rd');

            $leave[2] = array('Title' => '毕业季 | 有青春，有梦想，有属于我们的回忆',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/Public/graduate/c.jpg',
                'Url' => 'http://wechat.npulife.com/graduate/a/a.html');

            $leave[3] = array('Title' => '毕业季 | 感恩工大，我们献给母校的礼物',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/Public/graduate/b.jpg',
                'Url' => 'http://wechat.npulife.com/tool/home/graduate/gift');

            $leave[4] = array('Title' => '毕业季 | 狂欢不止，专属于我们自己的活动',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/Public/graduate/bi.jpg',
                'Url' => 'http://wechat.npulife.com/tool/home/graduate/');

            $leave[5] = array('Title' => '毕业季 | 榜上有名，今天我们一起毕业了',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/Public/graduate/y.jpg',
                'Url' => 'http://wechat.npulife.com/tool/home/graduate/namelist');

            $this->replyNews($leave);

        }
        //签到活动（分为活动开始和活动结束）
        if ($data['EventKey'] > 19900 && $data['EventKey'] < 19920)
        {
            $openid = $data['FromUserName'];//获取用户名
            $sData['academy_id'] = msubstr($data['EventKey'], 3, 2, 'utf-8', false);

            $res = $sData['academy_id'];
            $checkin[0] = array('Title' => '"古路坝灯火电影首映仪式"' . '      ' . $sData['academy_id'] . '院活动开始签到',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/Tool/Public/Checkin/i/4119.png',
                'Url' => 'http://wechat.npulife.com/Tool/Home/ICheck/index?id=' . $res . '&openid=' . $openid);

            $this->replyNews($checkin);
            //$this->

        }

        if ($data['EventKey'] > 19920 && $data['EventKey'] < 19940)
        {
            $openid = $data['FromUserName'];//获取用户名
            $data['EventKey'] = $data['EventKey'] - 20;
            $sData['academy_id'] = msubstr($data['EventKey'], 3, 2, 'utf-8', false);

            $res = $sData['academy_id'];
            $checkin[0] = array('Title' => '"古路坝灯火电影首映仪式"' . '      ' . $res . '院结束活动签到',
                'Description' => '',
                'PicUrl' => 'http://wechat.npulife.com/Tool/Public/Checkin/i/4119.png',
                'Url' => 'http://wechat.npulife.com/Tool/Home/ICheck/index2?id=' . $res . '&openid=' . $openid);

            $this->replyNews($checkin);
        }
        //校车定位功能中的扫码上车---105：老->新；106：新到老
        $colMod = M('collector', 'nl_', 'DB_CONFIG_NPULIFE_DATA');
        $openid = $data['FromUserName'];
        if ($data['EventKey'] == 105)
        {
            $data['direction'] = 0;
            $data['openid'] = $openid;
            $re = $colMod->where("openid='%s'", $openid)->find();
            if (! $re)
            {
                $colMod->add($data);
            } else
            {
                $re['direction'] = $data['direction'];
                $colMod->save($data);
            }
            $busLoc[0] = array('Title' => '【我要当校车信息员】',
                'Description' => '点我填写校车编号哦',
                'PicUrl' => '',
                'Url' => 'http://wechat.npulife.com/tool/home/busloc/aboard');

            $busLoc[1] = array('Title' => '【我已下车咯】',
                'Description' => '点我校车到站啦~~',
                'PicUrl' => '',
                'Url' => 'http://wechat.npulife.com/tool/home/busloc/offBus');

            $this->replyNews($busLoc);

        }
        if ($data['EventKey'] == 106)
        {
            $data['direction'] = 1;
            $data['openid'] = $openid;
            $re = $colMod->where("openid='%s'", $openid)->find();
            if (! $re)
            {
                $colMod->add($data);
            } else
            {
                $re['direction'] = $data['direction'];
                $colMod->save($data);
            }
            $busLoc[0] = array('Title' => '【我要当校车信息员】',
                'Description' => '点我填写校车编号哦',
                'PicUrl' => '',
                'Url' => 'http://wechat.npulife.com/tool/home/busloc/aboard');
            $busLoc[1] = array('Title' => '【我已下车咯】',
                'Description' => '点我校车到站啦~~',
                'PicUrl' => '',
                'Url' => 'http://wechat.npulife.com/tool/home/busloc/offBus');

            $this->replyNews($busLoc);

        }
        //二维码抽奖，还没写完。
        if ($data['EventKey'] == 6)
        {
            $mapc['openid'] = get_openid();
            $theUser = M('ErweimaMember')->where($mapc)->find();
            $id = $theUser['id'];
            $uid = $theUser['uid'];

            $articles [0] = array(
                'Title' => "",
                'Description' => "",
                'PicUrl' => "",
                'Url' => ""
            );
        }

        //校园寻宝
        // if($data['EventKey']<17||$data['EventKey']>=7)
        // {
        // 	//得到用户的ID，记录下用户扫描
        // 	$mape['openid'] = get_openid();

        // 	$theUser = M('ErweimaMember')->where($mape)->find();
        // 	$id = $theUser['id'];
        // 	$uid = $theUser['uid'];

        // 	//从场景表里找出地点的ID，
        // 	$Erweima = M("Erweima");
        // 	$eMap['eventkey'] = $data['EventKey'];
        // 	$erweima = $Erweima->where($eMap)->find();

        // 	//再从地点表里找到地点的介绍。
        // 	$LbsChangyou = M("LbsChangyou");
        // 	$lmap['id'] = $erweima['articleid'];
        // 	$location = $LbsChangyou->where($lmap)->find();

        // 	//向用户发送一条图文，表示扫描成功。
        // 	$articles [0] = array (
        // 			'Title' => "成功解锁：".$location['name'],
        // 			'Description' => "到哪一步了？点击页面可查看寻宝游戏当前状态。",
        // 			'PicUrl' => "",
        // 			'Url' => ""
        // 	);

        // }
        // $ret = $this->replyNews($articles);

        // return $ret;
    }

    // 自定义菜单事件
    public function click()
    {
        return true;
    }

    function searchFile($dir, $num)
    {
        if (is_dir($dir) && is_readable($dir))
        {
            $handle = opendir($dir);
            while (($f_name = readdir($handle)) != false)
            {
                $arr = explode("_", $f_name);//分割文件名
                if (! strcmp($arr[0], $num))
                {
                    $res = $f_name;
                }
            }

            closedir($handle);

        } else
        {
            $res = "找不到可读取文件。";
        }

        return $res;
    }


}



