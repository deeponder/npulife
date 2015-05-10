<?php

namespace Addons\DataAccquisiton;
use Common\Controller\Addon;

/**
 * 数据库更新插件
 * @author 无名
 */

    class DataAccquisitonAddon extends Addon{

        public $info = array(
            'name'=>'DataAccquisiton',
            'title'=>'数据库更新',
            'description'=>'用于从app读取数据，并存到我们的数据库里',
            'status'=>1,
            'author'=>'无名',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/DataAccquisiton/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/DataAccquisiton/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }