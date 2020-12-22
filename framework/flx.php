<?php
/**
 * FlxPHP
 * Author:FlxSNX<211154860@qq.com>
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
        $this->loadcfg();

        //处理请求
        $this->request();
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
        define('FLXPHP_VER','3.0');

        //框架内部版本号
        define('FLXPHP_BUILD','3001');
        
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
        
        $this->_SYS['route'] = get_route();
        
        if(!$this->_SYS['route'])sys_error(['error' => '应用未设置路由','info' => '请在应用的route目录下设置路由']);

        /* 下个版本路由模块将加入请求类型的区分 */
            
         //解析路由
         $i = 0;
         foreach ($this->_SYS['route'] as $k => $v){
             if(!preg_match('/{(.+)}/',$k)){
                 $routes[$i]['type'] = 1;
             }else{
                 $routes[$i]['type'] = 2;
             }
             $routes[$i]['param'] = $k;
             $routes[$i]['method'] = $v;
             $i++;
         }
     
         //匹配路由
         foreach($routes as $route){
             if($route['type'] == 2){
                 //转义+去除{}
                 $route['param'] = str_replace('/','\\/',$route['param']);
                 $route['param'] = str_replace('{','(',$route['param']);
                 $route['param'] = str_replace('}',')',$route['param']);
                 $pattern = '/^'.$route['param'].'$/';
                 if(preg_match($pattern,$s,$val)){//匹配成功执行方法(method)
                     //解析method
                     $route['method'] = explode('@',$route['method']);
                     $this->_SYS['controller'] = $route['method'][0];
                     $this->_SYS['action'] = $route['method'][1];
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
                        $run->$run_action(...$val);
                        break;
                     }else{
                        sys_error([
                            'error' => $this->_SYS['controller'].'控制器缺少'.$this->_SYS['action'].'方法',
                            'info' => '在'.APPDIR.'controller/'.$this->_SYS['controller'].'.php未找到'.$this->_SYS['action'].'方法'
                        ]);
                     }
                 }
             }else{
                 if($s == $route['param']){
                     //解析method
                     $route['method'] = explode('@',$route['method']);
                     $this->_SYS['controller'] = $route['method'][0];
                     $this->_SYS['action'] = $route['method'][1];
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
                        $run->$run_action($route['param']);
                        break;
                     }else{
                        sys_error([
                            'error' => $this->_SYS['controller'].'控制器缺少'.$this->_SYS['action'].'方法',
                            'info' => '在'.APPDIR.'controller/'.$this->_SYS['controller'].'.php未找到'.$this->_SYS['action'].'方法'
                        ]);
                     }
                 }
             }
         }
         if(!$run)sys_error(['error' => '未匹配到路由','info' => '系统未找到与请求相对应的路由']);
    }

    private function loadcfg(){
        $this->_CFG = [];
        //获取系统配置
        $this->_CFG = get_cfg(ROOTDIR . 'config/');
    }
 }