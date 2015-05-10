<?php

namespace Addons\Lvsetongdao;
use Common\Controller\Addon;

/**
 * 绿色通道结果查询插件
 * @author 圈哥
 */

    class LvsetongdaoAddon extends Addon{

        public $info = array(
            'name'=>'Lvsetongdao',
            'title'=>'绿色通道结果查询',
            'description'=>'这是一个临时描述',
            'status'=>1,
            'author'=>'圈哥',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Lvsetongdao/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Lvsetongdao/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }