<?php
/**
 * FlxPHP
 * Author:拾年<211154860@qq.com>
 * [框架函数]
 */

/**
 * 系统错误提示
 * 系统级出错提示,关闭调试模式时显示系统出错
 */
function sys_error($error = []){
    global $Flx;
    $debug = debug_backtrace();
    $error['file'] = $debug[0]['file'];
    $error['line'] = $debug[0]['line'];
    if($Flx->_CFG['debug'] == true){
        include SYSTEMDIR.'view/sys_error.php';
        exit();
    }else{
        app_error(500);
    }
}

/**
 * 应用错误提示
 * 应用级别出错提示,不受调试模式影响 通常为开发者给用户的错误提示
 */
function app_error($e){
    if(!is_array($e)){
        if($e == 404){
            $error['code'] = 404;
            $error['title'] = '啊哦~ 页面走丢辣~~~';
            $error['error'] = '你的页面走丢辣(＞﹏＜)';
            $error['info'] = '请确认地址是否正确或返回首页';
        }elseif($e == 500){
            $error['code'] = 500;
            $error['title'] = '系统出错';
            $error['error'] = '系统在运行中出现错误';
            $error['info'] = '如果你是网站用户请联系管理员解决';
        }
        include SYSTEMDIR.'view/app_error.php';
        exit();
    }else{
        $error = $e;
        include SYSTEMDIR.'view/app_error.php';
        exit();
    }
}

/**
 * 获取IP
 */
function real_ip()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] AS $xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                $ip = $xip;
                break;
            }
        }
    }
    return $ip;
}

/**
 * 获取指定目录所有配置文件
 */
function get_cfg($path)
{
    if (!file_exists($path)) {
        sys_error([
            'error' => '加载配置文件失败',
            'info' => '请检查是否文件夹或文件是否存在'
        ]);
    }
    $cfg = [];
    chdir($path);
    $cfgs = glob('*.cfg.php');
    if ($cfgs) {
        foreach ($cfgs as $c) {
            $content = include($path . $c);
            if (is_array($content)) {
                $cfg = array_merge($content, $cfg);
            }
        }
        return $cfg;
    } else {
        return [];
    }
}

/**
 * 将变量传入模板中
 */
function assign($name, $value = '')
{
    global $Flx;
    if (empty($Flx->_SYS['tplval'])) {
        $Flx->_SYS['tplval'] = [];
    }
    if (is_array($name)) {
        $Flx->_SYS['tplval'] = array_merge($Flx->_SYS['tplval'], $name);
    } else {
        $Flx->_SYS['tplval'][$name] = $value;
    }
}

/**
 * 显示模板
 */
function view($t = 'default')
{
    global $Flx;
    $type = '.html';
    if (!empty($Flx->_SYS['tplval'])) extract($Flx->_SYS['tplval']);
    if ($t == 'default') {
        if (!file_exists(APPDIR . '/view/' . $Flx->_SYS['controller'] . '/' . $Flx->_SYS['action'] . $type)) {
            sys_error([
                'error' => '未找到' . $Flx->_SYS['controller'] . '/' . $Flx->_SYS['action'] . '模板',
                'info' => '系统未找到'.$Flx->_SYS['controller'] . '/' . $Flx->_SYS['action'].'对应的模板'
            ]);
        }
        include_once APPDIR . '/view/' . $Flx->_SYS['controller'] . '/' . $Flx->_SYS['action'] . $type;
    } else {
        if (!file_exists(APPDIR . '/view/' . $t . $type)) {
            sys_error([
                'error' => '未找到' . $Flx->_SYS['controller'] . '/' . $Flx->_SYS['action'] . '模板',
                'info' => '系统未找到'.$Flx->_SYS['controller'] . '/' . $Flx->_SYS['action'].'对应的模板'
            ]);
        }
        include_once APPDIR . '/view/' . $t . $type;
    }
}

/**
 * 模板路径助手函数
 */
function template($t)
{
    global $Flx;
    $type = '.html';
    $template = APPDIR . '/view/' . $t . $type;
    if (!file_exists($template)) {
        sys_error([
            'error' => '缺失' . $t . '模板',
            'info' => '请检查是否文件夹或文件是否存在'
        ]);   
    }
    return $template;
}

/**
 *  过滤字符
 */
function authstr($string, $force = 0, $strip = FALSE)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = authstr($val, $force, $strip);
        }
    } else {
        $string = addslashes($strip ? stripslashes($string) : $string);
    }
    return $string;
}


function get_method(){
    $method = $_SERVER['REQUEST_METHOD'];

    if($method){
        $method = strtolower($method);
        return $method;
    }else{
        return false;
    }
}