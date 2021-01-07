<?php
/**
 * FlxPHP
 * Author:拾年<211154860@qq.com>
 * [框架核心文件]
 */
namespace FlxPHP;
class Flx{
    public $_CFG;
    public $_SYS;

    public function run(){
        //初始化
        $this->base();

        //自动加载
        $this->autoload();

        //框架函数
        include SYSTEMDIR.'function.php';

        //检测加载应用目录的函数文件
        if(file_exists(APPDIR.'function.php'))include APPDIR.'function.php';

        //加载配置
        $this->loadCfg();

        //加载路由
        include_once SYSTEMDIR.'route.php';
        include_once APPDIR.'route.php';

        //处理请求
        $response = $this->request();
        echo $response;
    }

    private function base(){
        //设置编码
        header("Content-type: text/html; charset=utf-8");

        //屏蔽NOTICE&WARNING报错
        error_reporting(E_ALL^E_WARNING^E_NOTICE);

        //设置时区
        date_default_timezone_set("PRC");

        //根目录
        define('ROOTDIR', dirname(__DIR__) . '/');

        //应用目录
        define('APPDIR', dirname(__DIR__) . '/app/');

        //框架目录
        define('SYSTEMDIR', __DIR__ . '/');

        //扩展目录
        define('EXTENDDIR', dirname(__DIR__). '/extend/');

        //资源目录
        define('ASSETSDIR', dirname(__DIR__). '/public/');

        //框架版本
        define('FLXPHP_VER','3.1');

        //框架内部版本号
        define('FLXPHP_BUILD','3100');
        
        //应用URL
        define('APPURL',((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://'). $_SERVER['HTTP_HOST']);
    }

    private function autoload(){
        //设置加载目录
        set_include_path(APPDIR.PATH_SEPARATOR.EXTENDDIR.PATH_SEPARATOR.ROOTDIR.PATH_SEPARATOR.get_include_path());

        //注册自动加载
        spl_autoload_register(function($classname){
            if(substr($classname,0,3) == 'app'){
                $classname = substr($classname,4);
            }elseif(substr($classname,0,6) == 'FlxPHP'){
                $classname = substr($classname,7);
            }
            $classname = str_replace("\\","/",$classname);
            include_once $classname . '.php';
        });
    }

    private function request(){
        $s = !empty($_GET['s']) ? $_GET['s'] : '/';
        //去除首尾的 "/"
        if($s != '/' and substr($s,0,1) == '/'){
            $s = substr($s,1);
        }
        if($s != '/' and substr($s,-1) == '/'){
            $s = substr($s,0,-1);
        }

        $this->_SYS['route'] = Route::$instance->route;

        if(!$this->_SYS['route'])sys_error(['error' => '应用未设置路由']);

        $method = get_method();

        if($method == 'get'){
            $routes = $this->_SYS['route']['get'];
        }elseif($method == 'post'){
            $routes = $this->_SYS['route']['post'];
        }else{
            if(!$this->_SYS['route'])sys_error(['error' => '非法的请求方式']);
        }
        
        foreach($routes as $k => $v){
            if($v['type'] == 'regex'){
                preg_match_all('/{([^\/]+)}/',$k,$regexResult);
                if(!$regexResult)sys_error(['error' => '路由规则出错:'.$k]);
                $rule = str_replace('/','\\/',$k);
                $rule = '/^'.$rule.'$/';

                $names = $regexResult[1];
                foreach($names as $name){
                    if($v['regex'][$name]){
                        $rule = str_replace('{'.$name.'}','('.$v['regex'][$name].')',$rule);
                    }else{
                        $rule = str_replace('{'.$name.'}','([^\/]+)',$rule);
                    }
                }
                if(preg_match($rule,$s,$val)){
                    $val[count($val)] = $val[0];
                    unset($val[0]);
                    if($v['method'] instanceof \Closure){
                        $run = $v['method'];
                        return $run(...$val);
                    }else{
                        $v['method'] = explode('@',$v['method']);
                        $this->_SYS['controller'] = $v['method'][0];
                        $this->_SYS['action'] = $v['method'][1];
                        $this->_SYS['routeVal'] = $val;
                        $this->_SYS['s'] = $s;
                        break;
                    }
                }
            }else{
                if($s == $k){
                    if($v['method'] instanceof \Closure){
                        $run = $v['method'];
                        return $run();
                    }else{
                        $v['method'] = explode('@',$v['method']);
                        $this->_SYS['controller'] = $v['method'][0];
                        $this->_SYS['action'] = $v['method'][1];
                        $this->_SYS['routeVal'] = false;
                        $this->_SYS['s'] = $s;
                        break;
                    }
                }
            }
        }

        if($this->_SYS['controller'] && $this->_SYS['action']){
            //检查controller对应的文件是否存在
            if(!file_exists(APPDIR.'controller/'.$this->_SYS['controller'].'.php')){
                sys_error([
                    'error' => '未找到'.$this->_SYS['controller'].'控制器',
                    'info' => '在'.APPDIR.'controller目录未找到'.$this->_SYS['controller'].'.php文件'
                ]);
            }
            //实例化类并执行对应的类方法
            $run_class_name = 'app\\controller\\'. $this->_SYS['controller'];
            $run_action = $this->_SYS['action'];
            $run = new $run_class_name;
            if(method_exists($run,$run_action)) {//检查是否存在对应的方法
                //检查是否存在__init方法 用于防止子类重写父类的__construct方法
                if(method_exists($run,'__init'))$run->__init();
                if($this->_SYS['routeVal']){
                    return $run->$run_action(...$this->_SYS['routeVal']);
                }else{
                    return $run->$run_action();
                }  
            }else{
                sys_error([
                    'error' => $this->_SYS['controller'].'控制器缺少'.$this->_SYS['action'].'方法',
                    'info' => '在'.APPDIR.'controller/'.$this->_SYS['controller'].'.php未找到'.$this->_SYS['action'].'方法'
                ]);
            }
        }else{
            sys_error(['error' => '未匹配到路由']);
        }
    }

    private function loadCfg(){
        $this->_CFG = [];
        //获取系统配置
        $this->_CFG = get_cfg(ROOTDIR . 'config/');
    }
 }