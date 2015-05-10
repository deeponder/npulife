<?php

namespace Addons\popwall;
use Common\Controller\Addon;

/**
 * 微信上墙插件
 * @author peng
 */

    class popwallAddon extends Addon{

        public $info = array(
            'name'=>'popwall',
            'title'=>'微信上墙',
            'description'=>'晚会等互动的微信大屏幕',
            'status'=>1,
            'author'=>'peng',
            'version'=>'1.0',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/popwall/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/popwall/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }