<?php

namespace Addons\UpdateData;
use Common\Controller\Addon;

/**
 * 查询插件
 * @author xh
 */

    class UpdateDataAddon extends Addon{

        public $info = array(
            'name'=>'UpdateData',
            'title'=>'更新数据',
            'description'=>'更新数据信息',
            'status'=>1,
            'author'=>'zwc',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/UpdateData/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/UpdateData/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }