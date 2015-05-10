<?php

namespace Addons\Choujiang;
use Common\Controller\Addon;

/**
 * 二维码抽奖插件
 * @author 圈哥
 */

    class ChoujiangAddon extends Addon{

        public $info = array(
            'name'=>'Choujiang',
            'title'=>'二维码抽奖',
            'description'=>'二维码抽奖。',
            'status'=>1,
            'author'=>'圈哥',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Choujiang/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Choujiang/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }