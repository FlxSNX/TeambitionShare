<?php
/**
 * teambition网盘控制器
 */
namespace app\controller\teambition;
use extend\teambition;
class pan{
    public $cookie;
    public $_CFG;

    public function __construct($_CFG){
        $orgid = teambition::get_orgId($_CFG['pan']['cookie']);
        if($orgid){
            $this->cookie = $_CFG['pan']['cookie'];
            $this->_CFG = $_CFG;
        }else{
            app_error([
                'code' => 500,
                'title' => '应用配置错误',
                'error' => 'cookie配置错误',
                'info' => '请检查cookie是否已失效后重新配置'
            ]);
        }
    }

    public function get_list($id,$order='updateTime'){
        $result = teambition::get_pan_list($this->_CFG['pan']['cookie'],$this->_CFG['pan']['orgId'],$this->_CFG['pan']['spaceId'],$this->_CFG['pan']['driveId'],$id,$this->_CFG['pan']['maxCount']);
        if($result['data']['message']){
            return app_error(404);
        }else{
            return $result;
        }
    }

    public function get_dir($id){
        $result = teambition::get_pan_file($this->_CFG['pan']['cookie'],$this->_CFG['pan']['orgId'],$this->_CFG['pan']['spaceId'],$this->_CFG['pan']['driveId'],$id);
        if($result['status'] != '400'){
            return $result;
        }else{
            return app_error(404);
        }
    }
}