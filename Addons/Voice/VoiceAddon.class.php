<?php

namespace Addons\Voice;
use Common\Controller\Addon;

/**
 * 微唱插件
 * @author 圈哥
 */

    class VoiceAddon extends Addon{

        public $info = array(
            'name'=>'Voice',
            'title'=>'微唱',
            'description'=>'语音接收插件',
            'status'=>1,
            'author'=>'圈哥',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Voice/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Voice/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }