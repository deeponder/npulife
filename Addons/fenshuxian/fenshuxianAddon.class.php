<?php

namespace Addons\fenshuxian;
use Common\Controller\Addon;

/**
 * 分数线查询插件
 * @author peng
 */

    class fenshuxianAddon extends Addon{

        public $info = array(
            'name'=>'fenshuxian',
            'title'=>'分数线查询',
            'description'=>'本科各省最近一年分数线查询',
            'status'=>1,
            'author'=>'peng',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/fenshuxian/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/fenshuxian/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }