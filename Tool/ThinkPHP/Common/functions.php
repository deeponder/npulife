<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * Think 系统函数库
 */

/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @param mixed $default 默认值
 * @return mixed
 */
function C($name=null, $value=null,$default=null) {
    static $_config = array();
    // 无参数时获取所有
    if (empty($name)) {
        return $_config;
    }
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtoupper($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : $default;
            $_config[$name] = $value;
            return;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0]   =  strtoupper($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // 批量设置
    if (is_array($name)){
        $_config = array_merge($_config, array_change_key_case($name,CASE_UPPER));
        return;
    }
    return null; // 避免非法参数
}

/**
 * 加载配置文件 支持格式转换 仅支持一级配置
 * @param string $file 配置文件名
 * @param string $parse 配置解析方法 有些格式需要用户自己解析
 * @return void
 */
function load_config($file,$parse=CONF_PARSE){
    $ext  = pathinfo($file,PATHINFO_EXTENSION);
    switch($ext){
        case 'php':
            return include $file;
        case 'ini':
            return parse_ini_file($file);
        case 'yaml':
            return yaml_parse_file($file);
        case 'xml': 
            return (array)simplexml_load_file($file);
        case 'json':
            return json_decode(file_get_contents($file), true);
        default:
            if(function_exists($parse)){
                return $parse($file);
            }else{
                E(L('_NOT_SUPPERT_').':'.$ext);
            }
    }
}

/**
 * 解析yaml文件返回一个数组
 * @param string $file 配置文件名
 * @return array
 */
if (!function_exists('yaml_parse_file')) {
    function yaml_parse_file($file) {
        vendor('spyc.Spyc');
        return Spyc::YAMLLoad($file);
    }
}

/**
 * 抛出异常处理
 * @param string $msg 异常消息
 * @param integer $code 异常代码 默认为0
 * @return void
 */
function E($msg, $code=0) {
    throw new Think\Exception($msg, $code);
}

/**
 * 记录和统计时间（微秒）和内存使用情况
 * 使用方法:
 * <code>
 * G('begin'); // 记录开始标记位
 * // ... 区间运行代码
 * G('end'); // 记录结束标签位
 * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
 * echo G('begin','end','m'); // 统计区间内存使用情况
 * 如果end标记位没有定义，则会自动以当前作为标记位
 * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
 * </code>
 * @param string $start 开始标签
 * @param string $end 结束标签
 * @param integer|string $dec 小数位或者m
 * @return mixed
 */
function G($start,$end='',$dec=4) {
    static $_info       =   array();
    static $_mem        =   array();
    if(is_float($end)) { // 记录时间
        $_info[$start]  =   $end;
    }elseif(!empty($end)){ // 统计时间和内存使用
        if(!isset($_info[$end])) $_info[$end]       =  microtime(TRUE);
        if(MEMORY_LIMIT_ON && $dec=='m'){
            if(!isset($_mem[$end])) $_mem[$end]     =  memory_get_usage();
            return number_format(($_mem[$end]-$_mem[$start])/1024);
        }else{
            return number_format(($_info[$end]-$_info[$start]),$dec);
        }

    }else{ // 记录时间和内存使用
        $_info[$start]  =  microtime(TRUE);
        if(MEMORY_LIMIT_ON) $_mem[$start]           =  memory_get_usage();
    }
}

/**
 * 获取和设置语言定义(不区分大小写)
 * @param string|array $name 语言变量
 * @param mixed $value 语言值或者变量
 * @return mixed
 */
function L($name=null, $value=null) {
    static $_lang = array();
    // 空参数返回所有定义
    if (empty($name))
        return $_lang;
    // 判断语言获取(或设置)
    // 若不存在,直接返回全大写$name
    if (is_string($name)) {
        $name   =   strtoupper($name);
        if (is_null($value)){
            return isset($_lang[$name]) ? $_lang[$name] : $name;
        }elseif(is_array($value)){
            // 支持变量
            $replace = array_keys($value);
            foreach($replace as &$v){
                $v = '{$'.$v.'}';
            }
            return str_replace($replace,$value,isset($_lang[$name]) ? $_lang[$name] : $name);        
        }
        $_lang[$name] = $value; // 语言定义
        return;
    }
    // 批量定义
    if (is_array($name))
        $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
    return;
}

/**
 * 添加和获取页面Trace记录
 * @param string $value 变量
 * @param string $label 标签
 * @param string $level 日志级别
 * @param boolean $record 是否记录日志
 * @return void
 */
function trace($value='[think]',$label='',$level='DEBUG',$record=false) {
    return Think\Think::trace($value,$label,$level,$record);
}

/**
 * 编译文件
 * @param string $filename 文件名
 * @return string
 */
function compile($filename) {
    $content    =   php_strip_whitespace($filename);
    $content    =   trim(substr($content, 5));
    // 替换预编译指令
    $content    =   preg_replace('/\/\/\[RUNTIME\](.*?)\/\/\[\/RUNTIME\]/s', '', $content);
    if(0===strpos($content,'namespace')){
        $content    =   preg_replace('/namespace\s(.*?);/','namespace \\1{',$content,1);
    }else{
        $content    =   'namespace {'.$content;
    }
    if ('?>' == substr($content, -2))
        $content    = substr($content, 0, -2);
    return $content.'}';
}

/**
 * 获取模版文件 格式 资源://模块@主题/控制器/操作
 * @param string $name 模版资源地址
 * @param string $layer 视图层（目录）名称
 * @return string
 */
function T($template='',$layer=''){

    // 解析模版资源地址
    if(false === strpos($template,'://')){
        $template   =   'http://'.str_replace(':', '/',$template);
    }
    $info   =   parse_url($template);
    $file   =   $info['host'].(isset($info['path'])?$info['path']:'');
    $module =   isset($info['user'])?$info['user'].'/':MODULE_NAME.'/';
    $extend =   $info['scheme'];
    $layer  =   $layer?$layer:C('DEFAULT_V_LAYER');

    // 获取当前主题的模版路径
    $auto   =   C('AUTOLOAD_NAMESPACE');
    if($auto && isset($auto[$extend])){ // 扩展资源
        $baseUrl    =   $auto[$extend].$module.$layer.'/';
    }elseif(C('VIEW_PATH')){ 
        // 改变模块视图目录
        $baseUrl    =   C('VIEW_PATH');
    }elseif(defined('TMPL_PATH')){ 
        // 指定全局视图目录
        $baseUrl    =   TMPL_PATH.$module;
    }else{
        $baseUrl    =   APP_PATH.$module.$layer.'/';
    }

    // 获取主题
    $theme  =   substr_count($file,'/')<2 ? C('DEFAULT_THEME') : '';

    // 分析模板文件规则
    $depr   =   C('TMPL_FILE_DEPR');
    if('' == $file) {
        // 如果模板文件名为空 按照默认规则定位
        $file = CONTROLLER_NAME . $depr . ACTION_NAME;
    }elseif(false === strpos($file, '/')){
        $file = CONTROLLER_NAME . $depr . $file;
    }elseif('/' != $depr){
        $file   =   substr_count($file,'/')>1 ? substr_replace($file,$depr,strrpos($file,'/'),1) : str_replace('/', $depr, $file);
    }
    return $baseUrl.($theme?$theme.'/':'').$file.C('TMPL_TEMPLATE_SUFFIX');
}

/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 * @param string $name 变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filter 参数过滤方法
 * @param mixed $datas 要获取的额外数据源
 * @return mixed
 */
function I($name,$default='',$filter=null,$datas=null) {
    if(strpos($name,'.')) { // 指定参数来源
        list($method,$name) =   explode('.',$name,2);
    }else{ // 默认为自动判断
        $method =   'param';
    }
    switch(strtolower($method)) {
        case 'get'     :   $input =& $_GET;break;
        case 'post'    :   $input =& $_POST;break;
        case 'put'     :   parse_str(file_get_contents('php://input'), $input);break;
        case 'param'   :
            switch($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input  =  $_POST;
                    break;
                case 'PUT':
                    parse_str(file_get_contents('php://input'), $input);
                    break;
                default:
                    $input  =  $_GET;
            }
            break;
        case 'path'    :   
            $input  =   array();
            if(!empty($_SERVER['PATH_INFO'])){
                $depr   =   C('URL_PATHINFO_DEPR');
                $input  =   explode($depr,trim($_SERVER['PATH_INFO'],$depr));            
            }
            break;
        case 'request' :   $input =& $_REQUEST;   break;
        case 'session' :   $input =& $_SESSION;   break;
        case 'cookie'  :   $input =& $_COOKIE;    break;
        case 'server'  :   $input =& $_SERVER;    break;
        case 'globals' :   $input =& $GLOBALS;    break;
        case 'data'    :   $input =& $datas;      break;
        default:
            return NULL;
    }
    if(''==$name) { // 获取全部变量
        $data       =   $input;
        array_walk_recursive($data,'filter_exp');
        $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
        if($filters) {
            if(is_string($filters)){
                $filters    =   explode(',',$filters);
            }
            foreach($filters as $filter){
                $data   =   array_map_recursive($filter,$data); // 参数过滤
            }
        }
    }elseif(isset($input[$name])) { // 取值操作
        $data       =   $input[$name];
        is_array($data) && array_walk_recursive($data,'filter_exp');
        $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
        if($filters) {
            if(is_string($filters)){
                $filters    =   explode(',',$filters);
            }elseif(is_int($filters)){
                $filters    =   array($filters);
            }
            
            foreach($filters as $filter){
                if(function_exists($filter)) {
                    $data   =   is_array($data)?array_map_recursive($filter,$data):$filter($data); // 参数过滤
                }else{
                    $data   =   filter_var($data,is_int($filter)?$filter:filter_id($filter));
                    if(false === $data) {
                        return   isset($default)?$default:NULL;
                    }
                }
            }
        }
    }else{ // 变量默认值
        $data       =    isset($default)?$default:NULL;
    }
    return $data;
}

function array_map_recursive($filter, $data) {
     $result = array();
     foreach ($data as $key => $val) {
         $result[$key] = is_array($val)
             ? array_map_recursive($filter, $val)
             : call_user_func($filter, $val);
     }
     return $result;
 }

/**
 * 设置和获取统计数据
 * 使用方法:
 * <code>
 * N('db',1); // 记录数据库操作次数
 * N('read',1); // 记录读取次数
 * echo N('db'); // 获取当前页面数据库的所有操作次数
 * echo N('read'); // 获取当前页面读取次数
 * </code>
 * @param string $key 标识位置
 * @param integer $step 步进值
 * @return mixed
 */
function N($key, $step=0,$save=false) {
    static $_num    = array();
    if (!isset($_num[$key])) {
        $_num[$key] = (false !== $save)? S('N_'.$key) :  0;
    }
    if (empty($step))
        return $_num[$key];
    else
        $_num[$key] = $_num[$key] + (int) $step;
    if(false !== $save){ // 保存结果
        S('N_'.$key,$_num[$key],$save);
    }
}

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @return string
 */
function parse_name($name, $type=0) {
    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function($match){return strtoupper($match[1]);}, $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

/**
 * 优化的require_once
 * @param string $filename 文件地址
 * @return boolean
 */
function require_cache($filename) {
    static $_importFiles = array();
    if (!isset($_importFiles[$filename])) {
        if (file_exists_case($filename)) {
            require $filename;
            $_importFiles[$filename] = true;
        } else {
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}

/**
 * 区分大小写的文件存在判断
 * @param string $filename 文件地址
 * @return boolean
 */
function file_exists_case($filename) {
    if (is_file($filename)) {
        if (IS_WIN && APP_DEBUG) {
            if (basename(realpath($filename)) != basename($filename))
                return false;
        }
        return true;
    }
    return false;
}

/**
 * 导入所需的类库 同java的Import 本函数有缓存功能
 * @param string $class 类库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 * @return boolean
 */
function import($class, $baseUrl = '', $ext=EXT) {
    static $_file = array();
    $class = str_replace(array('.', '#'), array('/', '.'), $class);
    if (isset($_file[$class . $baseUrl]))
        return true;
    else
        $_file[$class . $baseUrl] = true;
    $class_strut     = explode('/', $class);
    if (empty($baseUrl)) {
        if ('@' == $class_strut[0] || MODULE_NAME == $class_strut[0]) {
            //加载当前模块的类库
            $baseUrl = MODULE_PATH;
            $class   = substr_replace($class, '', 0, strlen($class_strut[0]) + 1);
        }elseif (in_array($class_strut[0],array('Think','Org','Behavior','Com','Vendor')) || is_dir(LIB_PATH.$class_strut[0])) {
            // 系统类库包和第三方类库包
            $baseUrl = LIB_PATH;
        }else { // 加载其他模块的类库
            $baseUrl = APP_PATH;
        }
    }
    if (substr($baseUrl, -1) != '/')
        $baseUrl    .= '/';
    $classfile       = $baseUrl . $class . $ext;
    if (!class_exists(basename($class),false)) {
        // 如果类不存在 则导入类库文件
        return require_cache($classfile);
    }
}

/**
 * 基于命名空间方式导入函数库
 * load('@.Util.Array')
 * @param string $name 函数库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 * @return void
 */
function load($name, $baseUrl='', $ext='.php') {
    $name = str_replace(array('.', '#'), array('/', '.'), $name);
    if (empty($baseUrl)) {
        if (0 === strpos($name, '@/')) {//加载当前模块函数库
            $baseUrl    =   MODULE_PATH.'Common/';
            $name       =   substr($name, 2);
        } else { //加载其他模块函数库
            $array      =   explode('/', $name);
            $baseUrl    =   APP_PATH . array_shift($array).'/Common/';
            $name       =   implode('/',$array);
        }
    }
    if (substr($baseUrl, -1) != '/')
        $baseUrl       .= '/';
    require_cache($baseUrl . $name . $ext);
}

/**
 * 快速导入第三方框架类库 所有第三方框架的类库文件统一放到 系统的Vendor目录下面
 * @param string $class 类库
 * @param string $baseUrl 基础目录
 * @param string $ext 类库后缀
 * @return boolean
 */
function vendor($class, $baseUrl = '', $ext='.php') {
    if (empty($baseUrl))
        $baseUrl = VENDOR_PATH;
    return import($class, $baseUrl, $ext);
}

/**
 * 实例化模型类 格式 [资源://][模块/]模型
 * @param string $name 资源地址
 * @param string $layer 模型层名称
 * @return Model
 */
function D($name='',$layer='') {
    if(empty($name)) return new Think\Model;
    static $_model  =   array();
    $layer          =   $layer? : C('DEFAULT_M_LAYER');
    if(isset($_model[$name.$layer]))
        return $_model[$name.$layer];
    $class          =   parse_res_name($name,$layer);
    if(class_exists($class)) {
        $model      =   new $class(basename($name));
    }elseif(false === strpos($name,'/')){
        // 自动加载公共模块下面的模型
        if(!C('APP_USE_NAMESPACE')){
            import('Common/'.$layer.'/'.$class);
        }else{
            $class      =   '\\Common\\'.$layer.'\\'.$name.$layer;
        }
        $model      =   class_exists($class)? new $class($name) : new Think\Model($name);
    }else {
        Think\Log::record('D方法实例化没找到模型类'.$class,Think\Log::NOTICE);
        $model      =   new Think\Model(basename($name));
    }
    $_model[$name.$layer]  =  $model;
    return $model;
}

/**
 * 实例化一个没有模型文件的Model
 * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
 * @param string $tablePrefix 表前缀
 * @param mixed $connection 数据库连接信息
 * @return Model
 */
function M($name='', $tablePrefix='',$connection='') {
    static $_model  = array();
    if(strpos($name,':')) {
        list($class,$name)    =  explode(':',$name);
    }else{
        $class      =   'Think\\Model';
    }
    $guid           =   (is_array($connection)?implode('',$connection):$connection).$tablePrefix . $name . '_' . $class;
    if (!isset($_model[$guid]))
        $_model[$guid] = new $class($name,$tablePrefix,$connection);
    return $_model[$guid];
}

/**
 * 解析资源地址并导入类库文件
 * 例如 module/controller addon://module/behavior
 * @param string $name 资源地址 格式：[扩展://][模块/]资源名
 * @param string $layer 分层名称
 * @return string
 */
function parse_res_name($name,$layer,$level=1){
    if(strpos($name,'://')) {// 指定扩展资源
        list($extend,$name)  =   explode('://',$name);
    }else{
        $extend  =   '';
    }
    if(strpos($name,'/') && substr_count($name, '/')>=$level){ // 指定模块
        list($module,$name) =  explode('/',$name,2);
    }else{
        $module =   MODULE_NAME;
    }
    $array  =   explode('/',$name);
    if(!C('APP_USE_NAMESPACE')){
        $class  =   parse_name($name, 1);
        import($module.'/'.$layer.'/'.$class.$layer);
    }else{
        $class  =   $module.'\\'.$layer;
        foreach($array as $name){
            $class  .=   '\\'.parse_name($name, 1);
        }
        // 导入资源类库
        if($extend){ // 扩展资源
            $class      =   $extend.'\\'.$class;
        }
    }
    return $class.$layer;
}

/**
 * 用于实例化访问控制器
 * @param string $name 控制器名
 * @param string $path 控制器命名空间（路径）
 * @return Controller|false
 */
function controller($name,$path=''){
    $layer  =   C('DEFAULT_C_LAYER');
    if(!C('APP_USE_NAMESPACE')){
        $class  =   parse_name($name, 1).$layer;
        import(MODULE_NAME.'/'.$layer.'/'.$class);
    }else{
        $class  =   MODULE_NAME.'\\'.($path?$path.'\\':'').$layer;
        $array  =   explode('/',$name);
        foreach($array as $name){
            $class  .=   '\\'.parse_name($name, 1);
        }
        $class .=   $layer;
    }
    if(class_exists($class)) {
        return new $class();
    }else {
        return false;
    }
}

/**
 * 实例化多层控制器 格式：[资源://][模块/]控制器
 * @param string $name 资源地址
 * @param string $layer 控制层名称
 * @param integer $level 控制器层次
 * @return Controller|false
 */
function A($name,$layer='',$level='') {
    static $_action = array();
    $layer  =   $layer? : C('DEFAULT_C_LAYER');
    $level  =   $level? : ($layer == C('DEFAULT_C_LAYER')?C('CONTROLLER_LEVEL'):1);
    if(isset($_action[$name.$layer]))
        return $_action[$name.$layer];
    
    $class  =   parse_res_name($name,$layer,$level);
    if(class_exists($class)) {
        $action             =   new $class();
        $_action[$name.$layer]     =   $action;
        return $action;
    }else {
        return false;
    }
}

/**
 * 远程调用控制器的操作方法 URL 参数格式 [资源://][模块/]控制器/操作
 * @param string $url 调用地址
 * @param string|array $vars 调用参数 支持字符串和数组
 * @param string $layer 要调用的控制层名称
 * @return mixed
 */
function R($url,$vars=array(),$layer='') {
    $info   =   pathinfo($url);
    $action =   $info['basename'];
    $module =   $info['dirname'];
    $class  =   A($module,$layer);
    if($class){
        if(is_string($vars)) {
            parse_str($vars,$vars);
        }
        return call_user_func_array(array(&$class,$action.C('ACTION_SUFFIX')),$vars);
    }else{
        return false;
    }
}

/**
 * 处理标签扩展
 * @param string $tag 标签名称
 * @param mixed $params 传入参数
 * @return mixed
 */
function tag($tag, &$params=NULL) {
    return \Think\Hook::listen($tag,$params);
}

/**
 * 执行某个行为
 * @param string $name 行为名称
 * @param string $tag 标签名称（行为类无需传入） 
 * @param Mixed $params 传入的参数
 * @return void
 */
function B($name, $tag='',&$params=NULL) {
    if(''==$tag){
        $name   .=  'Behavior';
    }
    return \Think\Hook::exec($name,$tag,$params);
}

/**
 * 去除代码中的空白和注释
 * @param string $content 代码内容
 * @return string
 */
function strip_whitespace($content) {
    $stripStr   = '';
    //分析php源码
    $tokens     = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr  .= $tokens[$i];
        } else {
            switch ($tokens[$i][0]) {
                //过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //过滤空格
                case T_WHITESPACE:
                    if (!$last_space) {
                        $stripStr  .= ' ';
                        $last_space = true;
                    }
                    break;
                case T_START_HEREDOC:
                    $stripStr .= "<<<THINK\n";
                    break;
                case T_END_HEREDOC:
                    $stripStr .= "THINK;\n";
                    for($k = $i+1; $k < $j; $k++) {
                        if(is_string($tokens[$k]) && $tokens[$k] == ';') {
                            $i = $k;
                            break;
                        } else if($tokens[$k][0] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr  .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}

/**
 * 自定义异常处理
 * @param string $msg 异常消息
 * @param string $type 异常类型 默认为Think\Exception
 * @param integer $code 异常代码 默认为0
 * @return void
 */
function throw_exception($msg, $type='Think\\Exception', $code=0) {
    Think\Log::record('建议使用E方法替代throw_exception',Think\Log::NOTICE);
    if (class_exists($type, false))
        throw new $type($msg, $code);
    else
        Think\Think::halt($msg);        // 异常类型不存在则输出错误信息字串
}

/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function dump($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}

/**
 * 设置当前页面的布局
 * @param string|false $layout 布局名称 为false的时候表示关闭布局
 * @return void
 */
function layout($layout) {
    if(false !== $layout) {
        // 开启布局
        C('LAYOUT_ON',true);
        if(is_string($layout)) { // 设置新的布局模板
            C('LAYOUT_NAME',$layout);
        }
    }else{// 临时关闭布局
        C('LAYOUT_ON',false);
    }
}

/**
 * URL组装 支持不同URL模式
 * @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param string|array $vars 传入的参数，支持数组和字符串
 * @param string $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean $domain 是否显示域名
 * @return string
 */
function U($url='',$vars='',$suffix=true,$domain=false) {
    // 解析URL
    $info   =  parse_url($url);
    $url    =  !empty($info['path'])?$info['path']:ACTION_NAME;
    if(isset($info['fragment'])) { // 解析锚点
        $anchor =   $info['fragment'];
        if(false !== strpos($anchor,'?')) { // 解析参数
            list($anchor,$info['query']) = explode('?',$anchor,2);
        }        
        if(false !== strpos($anchor,'@')) { // 解析域名
            list($anchor,$host)    =   explode('@',$anchor, 2);
        }
    }elseif(false !== strpos($url,'@')) { // 解析域名
        list($url,$host)    =   explode('@',$info['path'], 2);
    }
    // 解析子域名
    if(isset($host)) {
        $domain = $host.(strpos($host,'.')?'':strstr($_SERVER['HTTP_HOST'],'.'));
    }elseif($domain===true){
        $domain = $_SERVER['HTTP_HOST'];
        if(C('APP_SUB_DOMAIN_DEPLOY') ) { // 开启子域名部署
            $domain = $domain=='localhost'?'localhost':'www'.strstr($_SERVER['HTTP_HOST'],'.');
            // '子域名'=>array('模块[/控制器]');
            foreach (C('APP_SUB_DOMAIN_RULES') as $key => $rule) {
                $rule   =   is_array($rule)?$rule[0]:$rule;
                if(false === strpos($key,'*') && 0=== strpos($url,$rule)) {
                    $domain = $key.strstr($domain,'.'); // 生成对应子域名
                    $url    =  substr_replace($url,'',0,strlen($rule));
                    break;
                }
            }
        }
    }

    // 解析参数
    if(is_string($vars)) { // aaa=1&bbb=2 转换成数组
        parse_str($vars,$vars);
    }elseif(!is_array($vars)){
        $vars = array();
    }
    if(isset($info['query'])) { // 解析地址里面参数 合并到vars
        parse_str($info['query'],$params);
        $vars = array_merge($params,$vars);
    }
    
    // URL组装
    $depr       =   C('URL_PATHINFO_DEPR');
    $urlCase    =   C('URL_CASE_INSENSITIVE');
    if($url) {
        if(0=== strpos($url,'/')) {// 定义路由
            $route      =   true;
            $url        =   substr($url,1);
            if('/' != $depr) {
                $url    =   str_replace('/',$depr,$url);
            }
        }else{
            if('/' != $depr) { // 安全替换
                $url    =   str_replace('/',$depr,$url);
            }
            // 解析模块、控制器和操作
            $url        =   trim($url,$depr);
            $path       =   explode($depr,$url);
            $var        =   array();
            $varModule      =   C('VAR_MODULE');
            $varController  =   C('VAR_CONTROLLER');
            $varAction      =   C('VAR_ACTION');
            $var[$varAction]       =   !empty($path)?array_pop($path):ACTION_NAME;
            $var[$varController]   =   !empty($path)?array_pop($path):CONTROLLER_NAME;
            if($maps = C('URL_ACTION_MAP')) {
                if(isset($maps[strtolower($var[$varController])])) {
                    $maps    =   $maps[strtolower($var[$varController])];
                    if($action = array_search(strtolower($var[$varAction]),$maps)){
                        $var[$varAction] = $action;
                    }
                }
            }
            if($maps = C('URL_CONTROLLER_MAP')) {
                if($controller = array_search(strtolower($var[$varController]),$maps)){
                    $var[$varController] = $controller;
                }
            }
            if($urlCase) {
                $var[$varController]   =   parse_name($var[$varController]);
            }
            $module =   '';
            
            if(!empty($path)) {
                $var[$varModule]    =   implode($depr,$path);
            }else{
                if(C('MULTI_MODULE')) {
                    if(MODULE_NAME != C('DEFAULT_MODULE') || !C('MODULE_ALLOW_LIST')){
                        $var[$varModule]=   MODULE_NAME;
                    }
                }
            }
            if($maps = C('URL_MODULE_MAP')) {
                if($_module = array_search(strtolower($var[$varModule]),$maps)){
                    $var[$varModule] = $_module;
                }
            }
            if(isset($var[$varModule])){
                $module =   $var[$varModule];
                unset($var[$varModule]);
            }
            
        }
    }

    if(C('URL_MODEL') == 0) { // 普通模式URL转换
        $url        =   __APP__.'?'.C('VAR_MODULE')."={$module}&".http_build_query(array_reverse($var));
        if($urlCase){
            $url    =   strtolower($url);
        }        
        if(!empty($vars)) {
            $vars   =   http_build_query($vars);
            $url   .=   '&'.$vars;
        }
    }else{ // PATHINFO模式或者兼容URL模式
        if(isset($route)) {
            $url    =   __APP__.'/'.rtrim($url,$depr);
        }else{
            $module =   (defined('BIND_MODULE') && BIND_MODULE==$module )? '' : $module;
            $url    =   __APP__.'/'.($module?$module.MODULE_PATHINFO_DEPR:'').implode($depr,array_reverse($var));
        }
        if($urlCase){
            $url    =   strtolower($url);
        }
        if(!empty($vars)) { // 添加参数
            foreach ($vars as $var => $val){
                if('' !== trim($val))   $url .= $depr . $var . $depr . urlencode($val);
            }                
        }
        if($suffix) {
            $suffix   =  $suffix===true?C('URL_HTML_SUFFIX'):$suffix;
            if($pos = strpos($suffix, '|')){
                $suffix = substr($suffix, 0, $pos);
            }
            if($suffix && '/' != substr($url,-1)){
                $url  .=  '.'.ltrim($suffix,'.');
            }
        }
    }
    if(isset($anchor)){
        $url  .= '#'.$anchor;
    }
    if($domain) {
        $url   =  (is_ssl()?'https://':'http://').$domain.$url;
    }
    return $url;
}

/**
 * 渲染输出Widget
 * @param string $name Widget名称
 * @param array $data 传入的参数
 * @return void
 */
function W($name, $data=array()) {
    return R($name,$data,'Widget');
}

/**
 * 判断是否SSL协议
 * @return boolean
 */
function is_ssl() {
    if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
        return true;
    }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
        return true;
    }
    return false;
}

/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time=0, $msg='') {
    //多行URL地址支持
    $url        = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg))
        $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    } else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}

/**
 * 缓存管理
 * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value 缓存值
 * @param mixed $options 缓存参数
 * @return mixed
 */
function S($name,$value='',$options=null) {
    static $cache   =   '';
    if(is_array($options) && empty($cache)){
        // 缓存操作的同时初始化
        $type       =   isset($options['type'])?$options['type']:'';
        $cache      =   Think\Cache::getInstance($type,$options);
    }elseif(is_array($name)) { // 缓存初始化
        $type       =   isset($name['type'])?$name['type']:'';
        $cache      =   Think\Cache::getInstance($type,$name);
        return $cache;
    }elseif(empty($cache)) { // 自动初始化
        $cache      =   Think\Cache::getInstance();
    }
    if(''=== $value){ // 获取缓存
        return $cache->get($name);
    }elseif(is_null($value)) { // 删除缓存
        return $cache->rm($name);
    }else { // 缓存数据
        if(is_array($options)) {
            $expire     =   isset($options['expire'])?$options['expire']:NULL;
        }else{
            $expire     =   is_numeric($options)?$options:NULL;
        }
        return $cache->set($name, $value, $expire);
    }
}

/**
 * 快速文件数据读取和保存 针对简单类型数据 字符串、数组
 * @param string $name 缓存名称
 * @param mixed $value 缓存值
 * @param string $path 缓存路径
 * @return mixed
 */
function F($name, $value='', $path=DATA_PATH) {
    static $_cache  =   array();
    $filename       =   $path . $name . '.php';
    if ('' !== $value) {
        if (is_null($value)) {
            // 删除缓存
            if(false !== strpos($name,'*')){
                return false; // TODO 
            }else{
                unset($_cache[$name]);
                return Think\Storage::unlink($filename,'F');
            }
        } else {
            Think\Storage::put($filename,serialize($value),'F');
            // 缓存数据
            $_cache[$name]  =   $value;
            return ;
        }
    }
    // 获取缓存数据
    if (isset($_cache[$name]))
        return $_cache[$name];
    if (Think\Storage::has($filename,'F')){
        $value      =   unserialize(Think\Storage::read($filename,'F'));
        $_cache[$name]  =   $value;
    } else {
        $value          =   false;
    }
    return $value;
}

/**
 * 根据PHP各种类型变量生成唯一标识号
 * @param mixed $mix 变量
 * @return string
 */
function to_guid_string($mix) {
    if (is_object($mix)) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);
}

/**
 * XML编码
 * @param mixed $data 数据
 * @param string $root 根节点名
 * @param string $item 数字索引的子节点名
 * @param string $attr 根节点属性
 * @param string $id   数字索引子节点key转换的属性名
 * @param string $encoding 数据编码
 * @return string
 */
function xml_encode($data, $root='think', $item='item', $attr='', $id='id', $encoding='utf-8') {
    if(is_array($attr)){
        $_attr = array();
        foreach ($attr as $key => $value) {
            $_attr[] = "{$key}=\"{$value}\"";
        }
        $attr = implode(' ', $_attr);
    }
    $attr   = trim($attr);
    $attr   = empty($attr) ? '' : " {$attr}";
    $xml    = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
    $xml   .= "<{$root}{$attr}>";
    $xml   .= data_to_xml($data, $item, $id);
    $xml   .= "</{$root}>";
    return $xml;
}

/**
 * 数据XML编码
 * @param mixed  $data 数据
 * @param string $item 数字索引时的节点名称
 * @param string $id   数字索引key转换为的属性名
 * @return string
 */
function data_to_xml($data, $item='item', $id='id') {
    $xml = $attr = '';
    foreach ($data as $key => $val) {
        if(is_numeric($key)){
            $id && $attr = " {$id}=\"{$key}\"";
            $key  = $item;
        }
        $xml    .=  "<{$key}{$attr}>";
        $xml    .=  (is_array($val) || is_object($val)) ? data_to_xml($val, $item, $id) : $val;
        $xml    .=  "</{$key}>";
    }
    return $xml;
}

/**
 * session管理函数
 * @param string|array $name session名称 如果为数组则表示进行session设置
 * @param mixed $value session值
 * @return mixed
 */
function session($name='',$value='') {
    $prefix   =  C('SESSION_PREFIX');
    if(is_array($name)) { // session初始化 在session_start 之前调用
        if(isset($name['prefix'])) C('SESSION_PREFIX',$name['prefix']);
        if(C('VAR_SESSION_ID') && isset($_REQUEST[C('VAR_SESSION_ID')])){
            session_id($_REQUEST[C('VAR_SESSION_ID')]);
        }elseif(isset($name['id'])) {
            session_id($name['id']);
        }
        if('common' != APP_MODE){ // 其它模式可能不支持
            ini_set('session.auto_start', 0);
        }
        if(isset($name['name']))            session_name($name['name']);
        if(isset($name['path']))            session_save_path($name['path']);
        if(isset($name['domain']))          ini_set('session.cookie_domain', $name['domain']);
        if(isset($name['expire']))          ini_set('session.gc_maxlifetime', $name['expire']);
        if(isset($name['use_trans_sid']))   ini_set('session.use_trans_sid', $name['use_trans_sid']?1:0);
        if(isset($name['use_cookies']))     ini_set('session.use_cookies', $name['use_cookies']?1:0);
        if(isset($name['cache_limiter']))   session_cache_limiter($name['cache_limiter']);
        if(isset($name['cache_expire']))    session_cache_expire($name['cache_expire']);
        if(isset($name['type']))            C('SESSION_TYPE',$name['type']);
        if(C('SESSION_TYPE')) { // 读取session驱动
            $type   =   C('SESSION_TYPE');
            $class  =   strpos($type,'\\')? $type : 'Think\\Session\\Driver\\'. ucwords(strtolower($type));
            $hander =   new $class();
            session_set_save_handler(
                array(&$hander,"open"), 
                array(&$hander,"close"), 
                array(&$hander,"read"), 
                array(&$hander,"write"), 
                array(&$hander,"destroy"), 
                array(&$hander,"gc")); 
        }
        // 启动session
        if(C('SESSION_AUTO_START'))  session_start();
    }elseif('' === $value){ 
        if(''===$name){
            // 获取全部的session
            return $prefix ? $_SESSION[$prefix] : $_SESSION;
        }elseif(0===strpos($name,'[')) { // session 操作
            if('[pause]'==$name){ // 暂停session
                session_write_close();
            }elseif('[start]'==$name){ // 启动session
                session_start();
            }elseif('[destroy]'==$name){ // 销毁session
                $_SESSION =  array();
                session_unset();
                session_destroy();
            }elseif('[regenerate]'==$name){ // 重新生成id
                session_regenerate_id();
            }
        }elseif(0===strpos($name,'?')){ // 检查session
            $name   =  substr($name,1);
            if(strpos($name,'.')){ // 支持数组
                list($name1,$name2) =   explode('.',$name);
                return $prefix?isset($_SESSION[$prefix][$name1][$name2]):isset($_SESSION[$name1][$name2]);
            }else{
                return $prefix?isset($_SESSION[$prefix][$name]):isset($_SESSION[$name]);
            }
        }elseif(is_null($name)){ // 清空session
            if($prefix) {
                unset($_SESSION[$prefix]);
            }else{
                $_SESSION = array();
            }
        }elseif($prefix){ // 获取session
            if(strpos($name,'.')){
                list($name1,$name2) =   explode('.',$name);
                return isset($_SESSION[$prefix][$name1][$name2])?$_SESSION[$prefix][$name1][$name2]:null;  
            }else{
                return isset($_SESSION[$prefix][$name])?$_SESSION[$prefix][$name]:null;                
            }            
        }else{
            if(strpos($name,'.')){
                list($name1,$name2) =   explode('.',$name);
                return isset($_SESSION[$name1][$name2])?$_SESSION[$name1][$name2]:null;  
            }else{
                return isset($_SESSION[$name])?$_SESSION[$name]:null;
            }            
        }
    }elseif(is_null($value)){ // 删除session
        if($prefix){
            unset($_SESSION[$prefix][$name]);
        }else{
            unset($_SESSION[$name]);
        }
    }else{ // 设置session
        if($prefix){
            if (!isset($_SESSION[$prefix])) {
                $_SESSION[$prefix] = array();
            }
            $_SESSION[$prefix][$name]   =  $value;
        }else{
            $_SESSION[$name]  =  $value;
        }
    }
}

/**
 * Cookie 设置、获取、删除
 * @param string $name cookie名称
 * @param mixed $value cookie值
 * @param mixed $options cookie参数
 * @return mixed
 */
function cookie($name='', $value='', $option=null) {
    // 默认设置
    $config = array(
        'prefix'    =>  C('COOKIE_PREFIX'), // cookie 名称前缀
        'expire'    =>  C('COOKIE_EXPIRE'), // cookie 保存时间
        'path'      =>  C('COOKIE_PATH'), // cookie 保存路径
        'domain'    =>  C('COOKIE_DOMAIN'), // cookie 有效域名
        'httponly'  =>  C('COOKIE_HTTPONLY'), // httponly设置
    );
    // 参数设置(会覆盖黙认设置)
    if (!is_null($option)) {
        if (is_numeric($option))
            $option = array('expire' => $option);
        elseif (is_string($option))
            parse_str($option, $option);
        $config     = array_merge($config, array_change_key_case($option));
    }
    if(!empty($config['httponly'])){
        ini_set("session.cookie_httponly", 1);
    }
    // 清除指定前缀的所有cookie
    if (is_null($name)) {
        if (empty($_COOKIE))
            return;
        // 要删除的cookie前缀，不指定则删除config设置的指定前缀
        $prefix = empty($value) ? $config['prefix'] : $value;
        if (!empty($prefix)) {// 如果前缀为空字符串将不作处理直接返回
            foreach ($_COOKIE as $key => $val) {
                if (0 === stripos($key, $prefix)) {
                    setcookie($key, '', time() - 3600, $config['path'], $config['domain']);
                    unset($_COOKIE[$key]);
                }
            }
        }
        return;
    }elseif('' === $name){
        // 获取全部的cookie
        return $_COOKIE;
    }
    $name = $config['prefix'] . str_replace('.', '_', $name);
    if ('' === $value) {
        if(isset($_COOKIE[$name])){
            $value =    $_COOKIE[$name];
            if(0===strpos($value,'think:')){
                $value  =   substr($value,6);
                return array_map('urldecode',json_decode(MAGIC_QUOTES_GPC?stripslashes($value):$value,true));
            }else{
                return $value;
            }
        }else{
            return null;
        }
    } else {
        if (is_null($value)) {
            setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
            unset($_COOKIE[$name]); // 删除指定cookie
        } else {
            // 设置cookie
            if(is_array($value)){
                $value  = 'think:'.json_encode(array_map('urlencode',$value));
            }
            $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
            setcookie($name, $value, $expire, $config['path'], $config['domain']);
            $_COOKIE[$name] = $value;
        }
    }
}

/**
 * 加载动态扩展文件
 * @return void
 */
function load_ext_file($path) {
    // 加载自定义外部文件
    if($files = C('LOAD_EXT_FILE')) {
        $files      =  explode(',',$files);
        foreach ($files as $file){
            $file   = $path.'Common/'.$file.'.php';
            if(is_file($file)) include $file;
        }
    }
    // 加载自定义的动态配置文件
    if($configs = C('LOAD_EXT_CONFIG')) {
        if(is_string($configs)) $configs =  explode(',',$configs);
        foreach ($configs as $key=>$config){
            $file   = $path.'Conf/'.$config.CONF_EXT;
            if(is_file($file)) {
                is_numeric($key)?C(load_config($file)):C($key,load_config($file));
            }
        }
    }
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装） 
 * @return mixed
 */
function get_client_ip($type = 0,$adv=false) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 发送HTTP状态
 * @param integer $code 状态码
 * @return void
 */
function send_http_status($code) {
    static $_status = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
    );
    if(isset($_status[$code])) {
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:'.$code.' '.$_status[$code]);
    }
}

// 过滤表单中的表达式
function filter_exp(&$value){
    if (in_array(strtolower($value),array('exp','or'))){
        $value .= ' ';
    }
}

// 不区分大小写的in_array实现
function in_array_case($value,$array){
    return in_array(strtolower($value),array_map('strtolower',$array));
}

/**
 * 根据用户ID获取用户名
 *
 * @param integer $uid
 *        	用户ID
 * @return string 用户名
 */
function get_username($uid = 0) {
	static $list;
	if (! ($uid && is_numeric ( $uid ))) { // 获取当前登录用户名
		return session ( 'user_auth.username' );
	}
	
	/* 获取缓存数据 */
	if (empty ( $list )) {
		$list = S ( 'sys_active_user_list' );
	}
	
	/* 查找用户信息 */
	$key = "u{$uid}";
	if (isset ( $list [$key] )) { // 已缓存，直接使用
		$name = $list [$key];
	} else { // 调用接口获取用户信息
		$User = new User\Api\UserApi ();
		$info = $User->info ( $uid );
		if ($info && isset ( $info [1] )) {
			$name = $list [$key] = $info [1];
			/* 缓存用户 */
			$count = count ( $list );
			$max = C ( 'USER_MAX_CACHE' );
			while ( $count -- > $max ) {
				array_shift ( $list );
			}
			S ( 'sys_active_user_list', $list );
		} else {
			$name = '';
		}
	}
	return $name;
}

/**
 * 根据用户ID获取用户昵称
 *
 * @param integer $uid
 *        	用户ID
 * @return string 用户昵称
 */
function get_nickname($uid = 0) {
	$info = D ( 'Home/Member' )->getMemberInfo ( $uid );
	return $info ['nickname'];;
}
function get_truename($uid) {
	$info = D ( 'Home/Member' )->getMemberInfo ( $uid );
	return $info ['truename'];
}
function get_memberinfo($uid) {
	return D ( 'Home/Member' )->getMemberInfo ( $uid );
}
function get_userid($openid){
	$uMap['openid'] = $openid;
	$user = M('Member')->where($uMap)->find();
	$uid = $user['uid'];
	return $uid;
}

/**
 * 获取分类信息并缓存分类
 *
 * @param integer $id
 *        	分类ID
 * @param string $field
 *        	要获取的字段名
 * @return string 分类信息
 */
function get_category($id, $field = null) {
	static $list;
	
	/* 非法分类ID */
	if (empty ( $id ) || ! is_numeric ( $id )) {
		return '';
	}
	
	/* 读取缓存数据 */
	if (empty ( $list )) {
		$list = S ( 'sys_category_list' );
	}
	
	/* 获取分类名称 */
	if (! isset ( $list [$id] )) {
		$cate = M ( 'Category' )->find ( $id );
		if (! $cate || 1 != $cate ['status']) { // 不存在分类，或分类被禁用
			return '';
		}
		$list [$id] = $cate;
		S ( 'sys_category_list', $list ); // 更新缓存
	}
	return is_null ( $field ) ? $list [$id] : $list [$id] [$field];
}

/* 根据ID获取分类标识 */
function get_category_name($id) {
	return get_category ( $id, 'name' );
}

/* 根据ID获取分类名称 */
function get_category_title($id) {
	return get_category ( $id, 'title' );
}

/**
 * 获取文档模型信息
 *
 * @param integer $id
 *        	模型ID
 * @param string $field
 *        	模型字段
 * @return array
 */
function get_document_model($id = null, $field = null) {
	static $list;
	
	/* 非法分类ID */
	if (! (is_numeric ( $id ) || is_null ( $id ))) {
		return '';
	}
	
	/* 读取缓存数据 */
	if (empty ( $list )) {
		$list = S ( 'DOCUMENT_MODEL_LIST' );
	}
	
	/* 获取模型名称 */
	if (empty ( $list )) {
		$map = array (
				'status' => 1,
				'extend' => 1 
		);
		$model = M ( 'Model' )->where ( $map )->field ( true )->select ();
		foreach ( $model as $value ) {
			$list [$value ['id']] = $value;
		}
		S ( 'DOCUMENT_MODEL_LIST', $list ); // 更新缓存
	}
	
	/* 根据条件返回数据 */
	if (is_null ( $id )) {
		return $list;
	} elseif (is_null ( $field )) {
		return $list [$id];
	} else {
		return $list [$id] [$field];
	}
}

/**
 * 解析UBB数据
 *
 * @param string $data
 *        	UBB字符串
 * @return string 解析为HTML的数据
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function ubb($data) {
	// TODO: 待完善，目前返回原始数据
	return $data;
}

/**
 * 记录行为日志，并执行该行为的规则
 *
 * @param string $action
 *        	行为标识
 * @param string $model
 *        	触发行为的模型名
 * @param int $record_id
 *        	触发行为的记录id
 * @param int $user_id
 *        	执行行为的用户id
 * @return boolean
 * @author huajie <banhuajie@163.com>
 */
function action_log($action = null, $model = null, $record_id = null, $user_id = null) {
	
	// 参数检查
	if (empty ( $action ) || empty ( $model ) || empty ( $record_id )) {
		return '参数不能为空';
	}
	if (empty ( $user_id )) {
		$user_id = is_login ();
	}
	
	// 查询行为,判断是否执行
	$action_info = M ( 'Action' )->getByName ( $action );
	if ($action_info ['status'] != 1) {
		return '该行为被禁用或删除';
	}
	
	// 插入行为日志
	$data ['action_id'] = $action_info ['id'];
	$data ['user_id'] = $user_id;
	$data ['action_ip'] = ip2long ( get_client_ip () );
	$data ['model'] = $model;
	$data ['record_id'] = $record_id;
	$data ['create_time'] = NOW_TIME;
	
	// 解析日志规则,生成日志备注
	if (! empty ( $action_info ['log'] )) {
		if (preg_match_all ( '/\[(\S+?)\]/', $action_info ['log'], $match )) {
			$log ['user'] = $user_id;
			$log ['record'] = $record_id;
			$log ['model'] = $model;
			$log ['time'] = NOW_TIME;
			$log ['data'] = array (
					'user' => $user_id,
					'model' => $model,
					'record' => $record_id,
					'time' => NOW_TIME 
			);
			foreach ( $match [1] as $value ) {
				$param = explode ( '|', $value );
				if (isset ( $param [1] )) {
					$replace [] = call_user_func ( $param [1], $log [$param [0]] );
				} else {
					$replace [] = $log [$param [0]];
				}
			}
			$data ['remark'] = str_replace ( $match [0], $replace, $action_info ['log'] );
		} else {
			$data ['remark'] = $action_info ['log'];
		}
	} else {
		// 未定义日志规则，记录操作url
		$data ['remark'] = '操作url：' . $_SERVER ['REQUEST_URI'];
	}
	
	M ( 'ActionLog' )->add ( $data );
	
	if (! empty ( $action_info ['rule'] )) {
		// 解析行为
		$rules = parse_action ( $action, $user_id );
		
		// 执行行为
		$res = execute_action ( $rules, $action_info ['id'], $user_id );
	}
}

/**
 * 解析行为规则
 * 规则定义 table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
 * 规则字段解释：table->要操作的数据表，不需要加表前缀；
 * field->要操作的字段；
 * condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
 * rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
 * cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
 * max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
 * 单个行为后可加 ； 连接其他规则
 *
 * @param string $action
 *        	行为id或者name
 * @param int $self
 *        	替换规则里的变量为执行用户的id
 * @return boolean array: ， 成功返回规则数组
 * @author huajie <banhuajie@163.com>
 */
function parse_action($action = null, $self) {
	if (empty ( $action )) {
		return false;
	}
	
	// 参数支持id或者name
	if (is_numeric ( $action )) {
		$map = array (
				'id' => $action 
		);
	} else {
		$map = array (
				'name' => $action 
		);
	}
	
	// 查询行为信息
	$info = M ( 'Action' )->where ( $map )->find ();
	if (! $info || $info ['status'] != 1) {
		return false;
	}
	
	// 解析规则:table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
	$rules = $info ['rule'];
	$rules = str_replace ( '{$self}', $self, $rules );
	$rules = explode ( ';', $rules );
	$return = array ();
	foreach ( $rules as $key => &$rule ) {
		$rule = explode ( '|', $rule );
		foreach ( $rule as $k => $fields ) {
			$field = empty ( $fields ) ? array () : explode ( ':', $fields );
			if (! empty ( $field )) {
				$return [$key] [$field [0]] = $field [1];
			}
		}
		// cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
		if (! array_key_exists ( 'cycle', $return [$key] ) || ! array_key_exists ( 'max', $return [$key] )) {
			unset ( $return [$key] ['cycle'], $return [$key] ['max'] );
		}
	}
	
	return $return;
}

/**
 * 执行行为
 *
 * @param array $rules
 *        	解析后的规则数组
 * @param int $action_id
 *        	行为id
 * @param array $user_id
 *        	执行的用户id
 * @return boolean false 失败 ， true 成功
 * @author huajie <banhuajie@163.com>
 */
function execute_action($rules = false, $action_id = null, $user_id = null) {
	if (! $rules || empty ( $action_id ) || empty ( $user_id )) {
		return false;
	}
	
	$return = true;
	foreach ( $rules as $rule ) {
		
		// 检查执行周期
		$map = array (
				'action_id' => $action_id,
				'user_id' => $user_id 
		);
		$map ['create_time'] = array (
				'gt',
				NOW_TIME - intval ( $rule ['cycle'] ) * 3600 
		);
		$exec_count = M ( 'ActionLog' )->where ( $map )->count ();
		if ($exec_count > $rule ['max']) {
			continue;
		}
		
		// 执行数据库操作
		$Model = M ( ucfirst ( $rule ['table'] ) );
		$field = $rule ['field'];
		$res = $Model->where ( $rule ['condition'] )->setField ( $field, array (
				'exp',
				$rule ['rule'] 
		) );
		
		if (! $res) {
			$return = false;
		}
	}
	return $return;
}

// 基于数组创建目录和文件
function create_dir_or_files($files) {
	foreach ( $files as $key => $value ) {
		if (substr ( $value, - 1 ) == '/') {
			mkdir ( $value );
		} else {
			@file_put_contents ( $value, '' );
		}
	}
}

if (! function_exists ( 'array_column' )) {
	function array_column(array $input, $columnKey, $indexKey = null) {
		$result = array ();
		if (null === $indexKey) {
			if (null === $columnKey) {
				$result = array_values ( $input );
			} else {
				foreach ( $input as $row ) {
					$result [] = $row [$columnKey];
				}
			}
		} else {
			if (null === $columnKey) {
				foreach ( $input as $row ) {
					$result [$row [$indexKey]] = $row;
				}
			} else {
				foreach ( $input as $row ) {
					$result [$row [$indexKey]] = $row [$columnKey];
				}
			}
		}
		return $result;
	}
}

/**
 * 获取表名（不含表前缀）
 *
 * @param string $model_id        	
 * @return string 表名
 * @author huajie <banhuajie@163.com>
 */
function get_table_name($model_id = null) {
	if (empty ( $model_id )) {
		return false;
	}
	$Model = M ( 'Model' );
	$name = '';
	$info = $Model->getById ( $model_id );
	if ($info ['extend'] != 0) {
		$name = $Model->getFieldById ( $info ['extend'], 'name' ) . '_';
	}
	$name .= $info ['name'];
	return $name;
}

/**
 * 获取属性信息并缓存
 *
 * @param integer $id
 *        	属性ID
 * @param string $field
 *        	要获取的字段名
 * @return string 属性信息
 */
function get_model_attribute($model_id, $group = true) {
	static $list;
	
	/* 非法ID */
	if (empty ( $model_id ) || ! is_numeric ( $model_id )) {
		return '';
	}
	
	/* 读取缓存数据 */
	if (empty ( $list )) {
		$list = S ( 'attribute_list' );
	}
	
	/* 获取属性 */
	if (! isset ( $list [$model_id] )) {
		$map = array (
				'model_id' => $model_id 
		);
		$extend = M ( 'Model' )->getFieldById ( $model_id, 'extend' );
		
		if ($extend) {
			$map = array (
					'model_id' => array (
							"in",
							array (
									$model_id,
									$extend 
							) 
					) 
			);
		}
		$info = M ( 'Attribute' )->where ( $map )->select ();
		$list [$model_id] = $info;
		// S('attribute_list', $list); //更新缓存
	}
	
	$attr = array ();
	foreach ( $list [$model_id] as $value ) {
		$attr [$value ['name']] = $value;
	}
	
	if ($group) {
		$sort = M ( 'Model' )->getFieldById ( $model_id, 'field_sort' );
		
		if (empty ( $sort )) { // 未排序
			$group = array (
					1 => array_merge ( $attr ) 
			);
		} else {
			$group = json_decode ( $sort, true );

			$keys = array_keys ( $group );
			foreach ( $group as &$value ) {
				foreach ( $value as $key => $val ) {
					$value [$key] = $attr [$val];
					unset ( $attr [$val] );
				}
			}
			
			if (! empty ( $attr )) {
				$group [$keys [0]] = array_merge ( $group [$keys [0]], $attr );
			}
		}
		$attr = $group;
	}
	return $attr;
}

/**
 * 调用系统的API接口方法（静态方法）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5'); 调用Admin模块的User接口
 *
 * @param string $name
 *        	格式 [模块名]/接口名/方法名
 * @param array|string $vars
 *        	参数
 */
function api($name, $vars = array()) {
	$array = explode ( '/', $name );
	$method = array_pop ( $array );
	$classname = array_pop ( $array );
	$module = $array ? array_pop ( $array ) : 'Common';
	$callback = $module . '\\Api\\' . $classname . 'Api::' . $method;
	if (is_string ( $vars )) {
		parse_str ( $vars, $vars );
	}
	return call_user_func_array ( $callback, $vars );
}

/**
 * 根据条件字段获取指定表的数据
 *
 * @param mixed $value
 *        	条件，可用常量或者数组
 * @param string $condition
 *        	条件字段
 * @param string $field
 *        	需要返回的字段，不传则返回整个数据
 * @param string $table
 *        	需要查询的表
 * @author huajie <banhuajie@163.com>
 */
function get_table_field($value = null, $condition = 'id', $field = null, $table = null) {
	if (empty ( $value ) || empty ( $table )) {
		return false;
	}
	
	// 拼接参数
	$map [$condition] = $value;
	$info = M ( ucfirst ( $table ) )->where ( $map );
	if (empty ( $field )) {
		$info = $info->field ( true )->find ();
	} else {
		$info = $info->getField ( $field );
	}
	return $info;
}

/**
 * 获取链接信息
 *
 * @param int $link_id        	
 * @param string $field        	
 * @return 完整的链接信息或者某一字段
 * @author huajie <banhuajie@163.com>
 */
function get_link($link_id = null, $field = 'url') {
	$link = '';
	if (empty ( $link_id )) {
		return $link;
	}
	$link = M ( 'Url' )->getById ( $link_id );
	if (empty ( $field )) {
		return $link;
	} else {
		return $link [$field];
	}
}

/**
 * 获取文档封面图片
 *
 * @param int $cover_id        	
 * @param string $field        	
 * @return 完整的数据 或者 指定的$field字段值
 * @author huajie <banhuajie@163.com>
 */
function get_cover($cover_id, $field = null) {
	if (empty ( $cover_id )) {
		return false;
	}
	$picture = M ( 'Picture','nl_','DB_CONFIG1' )->where ( array (
			'status' => 1 
	) )->getById ( $cover_id );
	return empty ( $field ) ? $picture : $picture [$field];
}
function get_cover_url($cover_id) {
	if (empty ( $cover_id )) {
		return '';
	}
	return "http://wechat.npulife.com/" . get_cover ( $cover_id, 'path' );
}
function get_picture_url($id, $img = false) {
	$url = get_cover_url ( $id );
	if (! $img)
		return $url;
	
	return '<img src="' . $url . '" >';
}
/**
 * 检查$pos(推荐位的值)是否包含指定推荐位$contain
 *
 * @param number $pos
 *        	推荐位的值
 * @param number $contain
 *        	指定推荐位
 * @return boolean true 包含 ， false 不包含
 * @author huajie <banhuajie@163.com>
 */
function check_document_position($pos = 0, $contain = 0) {
	if (empty ( $pos ) || empty ( $contain )) {
		return false;
	}
	
	// 将两个参数进行按位与运算，不为0则表示$contain属于$pos
	$res = $pos & $contain;
	if ($res !== 0) {
		return true;
	} else {
		return false;
	}
}

/**
 * 获取数据的所有子孙数据的id值
 *
 * @author 朱亚杰 <xcoolcc@gmail.com>
 */
function get_stemma($pids, Model &$model, $field = 'id') {
	$collection = array ();
	
	// 非空判断
	if (empty ( $pids )) {
		return $collection;
	}
	
	if (is_array ( $pids )) {
		$pids = trim ( implode ( ',', $pids ), ',' );
	}
	$result = $model->field ( $field )->where ( array (
			'pid' => array (
					'IN',
					( string ) $pids 
			) 
	) )->select ();
	$child_ids = array_column ( ( array ) $result, 'id' );
	
	while ( ! empty ( $child_ids ) ) {
		$collection = array_merge ( $collection, $result );
		$result = $model->field ( $field )->where ( array (
				'pid' => array (
						'IN',
						$child_ids 
				) 
		) )->select ();
		$child_ids = array_column ( ( array ) $result, 'id' );
	}
	return $collection;
}

/**
 * 判断关键词是否唯一
 *
 * @author weiphp
 */
function keyword_unique($keyword) {
	if (empty ( $keyword ))
		return false;
	
	$map ['keyword'] = $keyword;
	$info = M ( 'keyword' )->where ( $map )->find ();
	return empty ( $info );
}
// 分析枚举类型配置值 格式 a:名称1,b:名称2
// weiphp 该函数是从admin的function的文件里提取这到里
function parse_config_attr($string) {
	$array = preg_split ( '/[,;\r\n]+/', trim ( $string, ",;\r\n" ) );
	if (strpos ( $string, ':' )) {
		$value = array ();
		foreach ( $array as $val ) {
			list ( $k, $v ) = explode ( ':', $val );
			$value [$k] = $v;
		}
	} else {
		$value = $array;
	}
	return $value;
}

// 分析枚举类型字段值 格式 a:名称1,b:名称2
// 暂时和 parse_config_attr功能相同
// 但请不要互相使用，后期会调整
function parse_field_attr($string) {
	if (0 === strpos ( $string, ':' )) {
		// 采用函数定义
		return eval ( substr ( $string, 1 ) . ';' );
	}
	$array = preg_split ( '/[,;\r\n]+/', trim ( $string, ",;\r\n" ) );
	if (strpos ( $string, ':' )) {
		$value = array ();
		foreach ( $array as $val ) {
			list ( $k, $v ) = explode ( ':', $val );
			$value [$k] = $v;
		}
	} else {
		$value = $array;
	}
	return $value;
}
/* 解析列表定义规则 */
function get_list_field($data, $grid, $model) {
	// 获取当前字段数据
	foreach ( $grid ['field'] as $field ) {
		$array = explode ( '|', $field );
		$array [0] = trim ( $array [0] );
		$temp = $data [$array [0]];
		// 函数支持
		if (isset ( $array [1] )) {
			if ($array [1] == 'get_name_by_status') {
				$temp = get_name_by_status ( $temp, $array [0], $model ['id'] );
			} else {
				$temp = call_user_func ( $array [1], $temp );
			}
		}
		$data2 [$array [0]] = $temp;
	}
	if (! empty ( $grid ['format'] )) {
		$value = preg_replace_callback ( '/\[([a-z_]+)\]/', function ($match) use($data2) {
			return $data2 [$match [1]];
		}, $grid ['format'] );
	} else {
		$value = implode ( ' ', $data2 );
	}
	
	// 链接支持
	if (! empty ( $grid ['href'] )) {
		$links = explode ( ',', $grid ['href'] );
		foreach ( $links as $link ) {
			$array = explode ( '|', $link );
			$href = $array [0];
			if (preg_match ( '/^\[([a-z_]+)\]$/', $href, $matches )) {
				$val [] = $data2 [$matches [1]];
			} else {
				$show = isset ( $array [1] ) ? $array [1] : $value;
				// 替换系统特殊字符串
				$href = str_replace ( array (
						'[DELETE]',
						'[EDIT]',
						'[MODEL]' 
				), array (
						'del?id=[id]&model=[MODEL]',
						'edit?id=[id]&model=[MODEL]',
						$model ['id'] 
				), $href );
				
				// 替换数据变量
				$href = preg_replace_callback ( '/\[([a-z_]+)\]/', function ($match) use($data) {
					return $data [$match [1]];
				}, $href );

				$val [] = '<a href="' . urldecode( U ( $href ) ) . '">' . $show . '</a>';
			}
		}
		$value = implode ( ' ', $val );
	}
	return $value;
}
/**
 * 获取状态值对应的标题
 *
 * @author weiphp
 */
function get_name_by_status($val, $name, $model_id) {
	static $_name = array ();
	if (! isset ( $_name [$model_id] )) {
		$_name [$model_id] = array ();
		$map ['extra'] = array (
				'EXP',
				'!=""' 
		);
		$map ['model_id'] = $model_id;
		$list = M ( 'attribute' )->where ( $map )->select ();
		foreach ( $list as $attr ) {
			if (empty ( $attr ['extra'] ))
				continue;
			
			$extra = parse_config_attr ( $attr ['extra'] );
			if (is_array ( $extra ) && ! empty ( $extra )) {
				$_name [$model_id] [$attr ['name']] = $extra;
			}
		}
	}
	return empty ( $_name [$model_id] [$name] [$val] ) ? $val : $_name [$model_id] [$name] [$val];
}
function addWeixinLog($data, $data_post = '') {
	$log ['cTime'] = time ();
	$log ['cTime_format'] = date ( 'Y-m-d H:i:s', $log ['cTime'] );
	$log ['data'] = is_array ( $data ) ? serialize ( $data ) : $data;
	$log ['data_post'] = $data_post;
	M ( 'weixin_log' )->add ( $log );
}
/**
 * 取一个二维数组中的每个数组的固定的键知道的值来形成一个新的一维数组
 *
 * @param $pArray 一个二维数组        	
 * @param $pKey 数组的键的名称        	
 * @return 返回新的一维数组
 */
function getSubByKey($pArray, $pKey = "", $pCondition = "") {
	$result = array ();
	if (is_array ( $pArray )) {
		foreach ( $pArray as $temp_array ) {
			if (is_object ( $temp_array )) {
				$temp_array = ( array ) $temp_array;
			}
			if (("" != $pCondition && $temp_array [$pCondition [0]] == $pCondition [1]) || "" == $pCondition) {
				$result [] = ("" == $pKey) ? $temp_array : isset ( $temp_array [$pKey] ) ? $temp_array [$pKey] : "";
			}
		}
		return $result;
	} else {
		return false;
	}
}
// 判断是否是在微信浏览器里
function isWeixinBrowser() {
	$agent = $_SERVER ['HTTP_USER_AGENT'];
	if (! strpos ( $agent, "icroMessenger" )) {
		return false;
	}
	return true;
}
// php获取当前访问的完整url地址
function GetCurUrl() {
	$url = 'http://';
	if (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] == 'on') {
		$url = 'https://';
	}
	if ($_SERVER ['SERVER_PORT'] != '80') {
		$url .= $_SERVER ['SERVER_NAME'] . ':' . $_SERVER ['SERVER_PORT'] . $_SERVER ['REQUEST_URI'];
	} else {
		$url .= $_SERVER ['SERVER_NAME'] . $_SERVER ['REQUEST_URI'];
	}
	// 兼容后面的参数组装
	if (stripos ( $url, '?' ) === false) {
		$url .= '?t=' . time ();
	}
	return $url;
}
// 获取当前用户的OpenId
function get_openid() {
	if (! empty ( $_REQUEST ['openid'] )) {
		session ( 'openid', $_REQUEST ['openid'] );
	}
	$openid = session ( 'openid' );
	
	$isWeixinBrowser = isWeixinBrowser ();
	if (empty ( $openid ) && $isWeixinBrowser) {
	$GLOBALS ['user'] ['appid'] ='wx4c81bc4055e38cf5';
	$GLOBALS ['user'] ['secret']='6936657155c874611fc77b3641164ae0';

		$callback = GetCurUrl ();
		OAuthWeixin ( $callback );
	}
	
	if (empty ( $openid )) {
		return -1;
	}
	
	return $openid;
}
// 获取当前用户的Token
function get_token() {
	if (! empty ( $_REQUEST ['token'] )) {
		session ( 'token', $_REQUEST ['token'] );
	}
	
	$token = session ( 'token' );
	
	if (empty ( $token )) {
		$token = '535ca7e3cde42';
	}
	
	return $token;
}
// 获取当前用户的UID,方便在模型里的自动填充功能使用
function get_mid() {
	return session ( 'mid' );
}

// 获取Access_Token,基础支持,查表节省使用次数
function getAccessToken()
{
	$GLOBALS ['user'] ['appid'] ='wx4c81bc4055e38cf5';
	$GLOBALS ['user'] ['secret']='6936657155c874611fc77b3641164ae0';
	
	//先从数据库里查，然后看有没有过期。
	$tData['token'] = get_token();
	$at = M('AccessToken','nl_','DB_CONFIG1')->where($tData)->find();
	
	if((time()-$at['update_time'])>7000)
	{
		//如果过期，新的accesstoken还要存到数据库里。
		$param ['appid'] = $GLOBALS ['user'] ['appid'];
		$param ['secret'] = $GLOBALS ['user'] ['secret'];
		$param ['grant_type'] = 'client_credential';
		$url = 'https://api.weixin.qq.com/cgi-bin/token?' . http_build_query ( $param );
		$content = file_get_contents ( $url );
		$content = json_decode ( $content, true );
		
		$access_token = $content ['access_token'];
		
		$aData['update_time'] = time();
		$aData['access_token'] = $access_token;
		$aData['token'] = '535ca7e3cde42';
		M('AccessToken','nl_','DB_CONFIG1')->save($aData);
	}
	else
	{
		$access_token = $at['access_token'];
	}
	
	return $access_token;
}

// 通过客服接口发送消息
function customSend($touser, $token, $content, $msgtype) {
	$GLOBALS ['user'] ['appid'] ='wx4c81bc4055e38cf5';
	$GLOBALS ['user'] ['secret']='6936657155c874611fc77b3641164ae0';
	
	header("Content-type: text/html; charset=utf-8");
	
	if (empty ( $GLOBALS ['user'] ['appid'] )) {
		return false;
	}
	
	$access_token = getAccessToken();
	
	$at ['access_token'] = $access_token;
	
	switch($msgtype)
	{
		case "text":
			$data = "{
				\"touser\": \"".$touser."\", 
				\"msgtype\": \"text\", 
				\"text\": {
							\"content\": \"".$content."\"
							}
			}";
			break;
		case "news":
			$data = "{\"touser\":\"".$touser."\",\"msgtype\":\"news\",\"news\":{\"articles\": [";
			if(count($content)&&(!empty($content[0]["Title"])))
			{
				for($i=0;$i<count($content);$i++)
				{
					$data.="{\"title\":\"".$content[$i]['Title']."\",\"description\":\"".$content[$i]['Description']."\",\"url\":\"".$content[$i]['Url']."\",\"picurl\":\"".$content[$i]['PicUrl']."\"},";
				}
				$data.="]}}";
			}
						
			break;		
		case "voice":
			$data = "{
				\"touser\": \"".$touser."\", 
				\"msgtype\": \"voice\", 
				\"voice\": {
							\"media_id\": \"".$content['media_id']."\"
							}
				}";
			break;
		case "image":
			$data = "{
				\"touser\": \"".$touser."\", 
				\"msgtype\": \"image\", 
				\"image\": {
							\"media_id\": \"".$content['media_id']."\"
							}
				}";
			break;
		case "video":
			$data = "{
				\"touser\": \"".$touser."\", 
				\"msgtype\": \"video\", 
				\"video\": {
							\"media_id\": \"".$content['media_id']."\",
							\"title\": \"".$content['title']."\",
							
							}
				}";
			break;
		
		case "music":
			$data = "{
				\"touser\": \"".$touser."\", 
				\"msgtype\": \"music\", 
				\"music\": {
							\"title\": \"".$content['title']."\",
							\"description\": \"".$content['description']."\",
							\"musicurl\": \"".$content['musicurl']."\",
							\"hqmusicurl\": \"".$content['hqmusicurl']."\",
							\"thumb_media_id\": \"".$content['thumb_media_id']."\",
							}
					}";
			break;
		
		default:
	}
	
	$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?'.http_build_query($at);
		
	$ch = curl_init(); 

	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	
	if(!empty($data))
	{
		$ret = curl_exec($ch); 
	}
	
	return $ret;
}


// 通过openid获取微信用户基本信息,此功能只有认证的服务号才能用
function getWeixinUserInfo($openid, $token) {
	$GLOBALS ['user'] ['appid'] ='wx4c81bc4055e38cf5';
	$GLOBALS ['user'] ['secret']='6936657155c874611fc77b3641164ae0';
	if (empty ( $GLOBALS ['user'] ['appid'] )) {
		return false;
	}
	
	$access_token = getAccessToken();
	
	$param2 ['access_token'] = $access_token;
	$param2 ['openid'] = $openid;
	$param2 ['lang'] = 'zh_CN';
	$url = 'https://api.weixin.qq.com/cgi-bin/user/info?' . http_build_query ( $param2 );
	$content = file_get_contents ( $url );
	$content = json_decode ( $content, true );
	return $content;
}
function OAuthWeixin($callback) {
	$isWeixinBrowser = isWeixinBrowser ();
	if (! $isWeixinBrowser || empty ( $GLOBALS ['user'] ['appid'] )) {
		redirect ( $callback . '&openid=-1' );
	}
	$param ['appid'] = $GLOBALS ['user'] ['appid'];
	//echo '1374 '.$param ['appid'];
	if (! isset ( $_GET ['getOpenId'] )) {
		$param ['redirect_uri'] = $callback . '&getOpenId=1';
		$param ['response_type'] = 'code';
		$param ['scope'] = 'snsapi_base';
		$param ['state'] = 123;
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query ( $param ) . '#wechat_redirect';
		//echo '1381 '.$param ['state'];
		redirect ( $url );
	} elseif ($_GET ['state']) {
		$param ['secret'] = $GLOBALS ['user'] ['secret'];
		$param ['code'] = I('code');
		$param ['grant_type'] = 'authorization_code';
		
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?' . http_build_query ( $param );
		$content = file_get_contents ( $url );
		$content = json_decode ( $content, true );
		//echo  $callback . '&openid=' . $content ['openid'] ;
		redirect ( $callback . '&token=535ca7e3cde42&openid=' . $content ['openid'] );
	}
}
/**
 * 执行SQL文件
 */
function execute_sql_file($sql_path) {
	// 读取SQL文件
	$sql = file_get_contents ( $sql_path );
	$sql = str_replace ( "\r", "\n", $sql );
	$sql = explode ( ";\n", $sql );
	
	// 替换表前缀
	$orginal = 'wp_';
	$prefix = C ( 'DB_PREFIX' );
	$sql = str_replace ( "{$orginal}", "{$prefix}", $sql );

	// 开始安装
	foreach ( $sql as $value ) {
		$value = trim ( $value );
		if (empty ( $value ))
			continue;
		
		$res = M ()->execute ( $value );
 		//dump($res);
 		//dump(M()->getLastSql());
	}
}
// 设置微信关联聊天中用到的用户状态
function set_user_status($addon, $keywordArr = array()) {
	// 设置用户状态
	$user_status ['addon'] = $addon;
	$user_status ['keywordArr'] = $keywordArr;
	
	$uid = intval ( $GLOBALS ['mid'] );
	return S ( 'user_status_' . $uid, $user_status);
}

// 获取公众号等级名
function get_public_group_name($group_id) {
	static $_public_group_name;
	
	$group_id = intval ( $group_id );
	if (! isset ( $_public_group_name [$group_id] )) {
		$group_list = M ( 'member_public_group' )->field ( 'id, title' )->select ();
		foreach ( $group_list as $g ) {
			$_public_group_name [$g ['id']] = $g ['title'];
		}
		$_public_group_name [0] = '无';
	}

	return $_public_group_name [$group_id];
}

//截取内容
function getShort($str, $length = 40, $ext = '') {
	$str    =   htmlspecialchars($str);
	$str    =   strip_tags($str);
	$str    =   htmlspecialchars_decode($str);
	$strlenth   =   0;
	$out        =   '';
	preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/", $str, $match);
	foreach($match[0] as $v){
		preg_match("/[\xe0-\xef][\x80-\xbf]{2}/",$v, $matchs);
		if(!empty($matchs[0])){
			$strlenth   +=  1;
		}elseif(is_numeric($v)){
			$strlenth   +=  0.5;    // 字符字节长度比例 汉字为1
		}else{
			$strlenth   +=  0.5;    // 字符字节长度比例 汉字为1
		}

		if ($strlenth > $length) {
			$output .= $ext;
			break;
		}

		$output .=  $v;
	}
	return $output;
}

//防超时的file_get_contents改造函数
function wp_file_get_contents($url){
	$context = stream_context_create(array(
		 'http' => array(
		  'timeout' => 30 //超时时间，单位为秒
		 ) 
	));  
	
	return file_get_contents($url, 0, $context);
}

//获取头像
function get_headurl($id,$token){
    
    $userinfo = getWeixinUserInfo($id, $token);
    return $userinfo['headimgurl'];
}
//获取昵称
function get_nickname1($id, $token){
    
    $userinfo = getWeixinUserInfo($id, $token);
    return $userinfo['nickname'];
}