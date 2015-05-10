<?php

namespace Addons\Lvyouwenhua;
use Common\Controller\Addon;

/**
 * 旅游文化插件
 * @author 圈哥
 */

    class LvyouwenhuaAddon extends Addon{

        public $info = array(
            'name'=>'Lvyouwenhua',
            'title'=>'旅游文化',
            'description'=>'旅游文化栏目弹出',
            'status'=>1,
            'author'=>'圈哥',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Lvyouwenhua/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Lvyouwenhua/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }