<?php
/**
 * FlxPHP
 * Author:拾年<211154860@qq.com>
 * [路由模块]
 */
namespace FlxPHP;
class Route{
    /**
     * @var $instance Route实例
     * @var $route 路由规则数组
     * @var $tmp 用于传递信息的临时变量
     */
    public static $instance;
    public $route;
    private $tmp;

    public static function match($type,$match,$method){
        if(!self::$instance instanceof self)self::$instance = new Route();
        $requestMethod = ['get','post'];
        if(!is_array($type)){
            if(in_array($type,$requestMethod)){
                if(preg_match('/{(.+)}/',$match)){
                    $matchType = 'regex';
                }else{
                    $matchType = 'default';
                }
                self::$instance->route[$type][$match] = ['method' => $method,'type' => $matchType];
                self::$instance->tmp = ['type' => $type,'match' => $match];
                return self::$instance;
            }else{
                return sys_error(['error' => '不支持的请求类型']);
            }
        }else{
            foreach($type as $t){
                self::match($t,$match,$method);
            }
            self::$instance->tmp = ['type' => $type,'match' => $match];
            return self::$instance;
        }
    }

    public static function get($match,$method){
        return self::match('get',$match,$method);
    }

    public static function post($match,$method){
        return self::match('post',$match,$method);
    }

    public static function any($match,$method){
        return self::match(['get','post'],$match,$method);
    }

    public function where($param,$tmp=false){
        if(!is_array($param))return;
        if(!$tmp)$tmp = self::$instance->tmp;
        
        if(!is_array($tmp['type'])){
            foreach($param as $k => $v){
                self::$instance->route[$tmp['type']][$tmp['match']]['regex'][$k] = $v;
            }
        }else{
            foreach($tmp['type'] as $t){
                $this->where($param,['type' => $t,'match' => $tmp['match']]);
            }
        }
    }
}