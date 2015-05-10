<?php

namespace Addons\Activity;
use Common\Controller\Addon;

/**
 * 组织活动插件
 * @author zhangs
 */

    class ActivityAddon extends Addon{

        public $info = array(
            'name'=>'Activity',
            'title'=>'组织活动',
            'description'=>'这是一个临时描述',
            'status'=>1,
            'author'=>'zhangs',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Activity/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Activity/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }