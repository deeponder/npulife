<?php

namespace Addons\ykbcj;
use Common\Controller\Addon;

/**
 * 翼鲲班成绩插件
 * @author 圈哥
 */

    class ykbcjAddon extends Addon{

        public $info = array(
            'name'=>'ykbcj',
            'title'=>'翼鲲班成绩',
            'description'=>'这是一个临时描述',
            'status'=>1,
            'author'=>'圈哥',
            'version'=>'1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/ykbcj/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/ykbcj/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }