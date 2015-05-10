<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Home\Model;

use Think\Model;

class AdviceModel extends Model {
	protected $_validate=array(
	   array('advice','require','请填写建议后提交'), //默认情况下用正则进行验证
	   	 
					   
	);

}
