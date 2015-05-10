<?php

namespace Addons\Qunfa;
use Common\Controller\Addon;

/**
 * 群发插件
 * @author 圈哥
 */

    class QunfaAddon extends Addon{

        public $info = array(
            'name'=>'Qunfa',
            'title'=>'群发',
            'description'=>'利用48小时无限次接口实现不完全的群发。',
            'status'=>1,
            'author'=>'圈哥',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Qunfa/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Qunfa/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }