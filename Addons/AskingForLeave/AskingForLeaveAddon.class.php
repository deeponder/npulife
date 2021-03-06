<?php

namespace Addons\AskingForLeave;
use Common\Controller\Addon;

/**
 * 在线请假插件
 * @author zhangs
 */

    class AskingForLeaveAddon extends Addon{

        public $info = array(
            'name'=>'AskingForLeave',
            'title'=>'在线请假',
            'description'=>'在线请假插件',
            'status'=>1,
            'author'=>'zhangs',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/AskingForLeave/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/AskingForLeave/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }