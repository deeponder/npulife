<?php

namespace Addons\Chat;
use Common\Controller\Addon;

/**
 * 智能聊天插件
 * @author 地下凡星
 */

    class ChatAddon extends Addon{

        public $info = array(
            'name'=>'Chat',
            'title'=>'智能聊天',
            'description'=>'通过网络上支持的智能API，实现：天气、翻译、藏头诗、笑话、歌词、计算、域名信息/备案/收录查询、IP查询、手机号码归属、人工智能聊天等功能',
            'status'=>1,
            'author'=>'地下凡星',
            'version'=>'0.1'
        );

        public $admin_list = array(
            'model'=>'Example',		//要查的表
			'fields'=>'*',			//要查的字段
			'map'=>'',				//查询条件, 如果需要可以再插件类的构造方法里动态重置这个属性
			'order'=>'id desc',		//排序,
			'listKey'=>array( 		//这里定义的是除了id序号外的表格里字段显示的表头名
				'字段名'=>'表头显示名'
			),
        );

        public function install(){
            return true;
        }

        public function uninstall(){
            return true;
        }

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }