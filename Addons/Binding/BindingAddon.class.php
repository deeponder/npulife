<?php

namespace Addons\Binding;
use Common\Controller\Addon;

/**
 * 用户绑定插件
 * @author huang
 */

    class BindingAddon extends Addon{

        public $info = array(
            'name'=>'Binding',
            'title'=>'用户绑定',
            'description'=>'将微信ID和学号绑定',
            'status'=>1,
            'author'=>'huang',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Binding/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Binding/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }