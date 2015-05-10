<?php

namespace Addons\erweimachajian;
use Common\Controller\Addon;

/**
 * 二维码插件插件
 * @author 圈哥
 */

    class erweimachajianAddon extends Addon{

        public $info = array(
            'name'=>'erweimachajian',
            'title'=>'二维码插件',
            'description'=>'扫描带参数的二维码，响应事件',
            'status'=>1,
            'author'=>'圈哥',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/erweimachajian/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/erweimachajian/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }