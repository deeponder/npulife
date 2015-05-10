<?php

namespace Addons\Wewall;
use Common\Controller\Addon;

/**
 * 微信上墙插件
 * @author peng
 */

    class WewallAddon extends Addon{

        public $info = array(
            'name'=>'Wewall',
            'title'=>'微信上墙',
            'description'=>'实现微信上墙功能',
            'status'=>1,
            'author'=>'peng',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Wewall/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Wewall/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }