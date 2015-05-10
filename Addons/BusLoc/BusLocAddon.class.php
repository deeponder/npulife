<?php

namespace Addons\BusLoc;
use Common\Controller\Addon;

/**
 * 校车定位插件
 * @author peng
 */

    class BusLocAddon extends Addon{

        public $info = array(
            'name'=>'BusLoc',
            'title'=>'校车定位',
            'description'=>'校车位置实时掌控',
            'status'=>1,
            'author'=>'peng',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/BusLoc/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/BusLoc/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }