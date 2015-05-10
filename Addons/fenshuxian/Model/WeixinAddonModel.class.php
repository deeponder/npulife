<?php
        	
namespace Addons\fenshuxian\Model;
use Home\Model\WeixinModel;
        	
/**
 * fenshuxian的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'fenshuxian' ); // 获取后台插件的配置参数	
		//dump($config);
		// $this->replyText('hello');
		preg_match('/(.+)分数线/i',$dataArr[Content], $matchs);

		$prov = $matchs[1];
		// $this->replyText($prov);
		$mark = M('benkeluqu');
		switch ($prov) {
			case '福建':
				$line = $mark->getField('fujian',true);
				break;
			case '陕西':
				$line = $mark->getField('sanxi',true);
				break;
			case '北京':
				$line = $mark->getField('beijing',true);
				break;
			case '浙江':
				$line = $mark->getField('zhejiang',true);
				break;	
			case '上海':
				$line = $mark->getField('shanghai',true);
			case '河南':
				$line = $mark->getField('henan',true);
			case '河北':
				$line = $mark->getField('hebei',true);
			case '黑龙江':
				$line = $mark->getField('heilongjiang',true);
			case '吉林':
				$line = $mark->getField('jilin',true);
			case '辽宁':
				$line = $mark->getField('liaoning',true);
			case '天津':
				$line = $mark->getField('tianjin',true);
			case '内蒙古':
				$line = $mark->getField('neimenggu',true);
			case '江苏':
				$line = $mark->getField('jiangsu',true);
			case '安徽':
				$line = $mark->getField('anhui',true);
			case '江西':
				$line = $mark->getField('jiangxi',true);
			case '山东':
				$line = $mark->getField('shandong',true);
			case '湖南':
				$line = $mark->getField('hunan',true);
			case '湖北':
				$line = $mark->getField('hubei',true);
			case '广东':
				$line = $mark->getField('guangdong',true);
			case '云南':
				$line = $mark->getField('yunnan',true);
			case '四川':
				$line = $mark->getField('sichuan',true);
			case '广西':
				$line = $mark->getField('guangxi',true);
			case '重庆':
				$line = $mark->getField('chongqing',true);
			case '海南':
				$line = $mark->getField('hainan',true);
			case '贵州':
				$line = $mark->getField('guizhou',true);
			case '山西':
				$line = $mark->getField('shanxi',true);
			case '甘肃':
				$line = $mark->getField('gansu',true);
			case '青海':
				$line = $mark->getField('qinghai',true);
			case '宁夏':
				$line = $mark->getField('ningxia',true);
			case '新疆':
				$line = $mark->getField('xinjiang',true);
			case '西藏':
				$line = $mark->getField('xizang',true);
			default:
				# code...
				break;
		}

		$text = "最高分:".$line[0]."\n"."平均分:".$line[2]."\n"."最低分:".$line[1];
		$this->replyText($text);


	} 

	// 关注公众号事件
	public function subscribe() {
		return true;
	}
	
	// 取消关注公众号事件
	public function unsubscribe() {
		return true;
	}
	
	// 扫描带参数二维码事件
	public function scan() {
		return true;
	}
	
	// 上报地理位置事件
	public function location() {
		return true;
	}
	
	// 自定义菜单事件
	public function click() {
		return true;
	}	
}
        	