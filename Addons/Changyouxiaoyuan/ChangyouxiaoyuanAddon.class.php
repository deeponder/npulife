<?php

namespace Addons\Changyouxiaoyuan;
use Common\Controller\Addon;

/**
 * 畅游校园插件
 * @author 圈哥
 */

    class ChangyouxiaoyuanAddon extends Addon{

        public $info = array(
            'name'=>'Changyouxiaoyuan',
            'title'=>'畅游校园',
            'description'=>'LBS畅游校园',
            'status'=>1,
            'author'=>'圈哥',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Changyouxiaoyuan/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Changyouxiaoyuan/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }