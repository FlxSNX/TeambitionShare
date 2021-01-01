<?php
/**
 * teambition项目文件控制器
 */
namespace app\controller\teambition;
use extend\teambition;
class project{
    public $cookie;
    public $projectId;
    public $info;
    public $_CFG;

    public function __construct($_CFG){
        $project = teambition::get_project($_CFG['teambition']['projectId'],$_CFG['teambition']['cookie']);
        if($project && $project['_id']){
            $this->cookie = $_CFG['teambition']['cookie'];
            $this->projectId = $_CFG['teambition']['projectId'];
            $this->info = $project;
            $this->_CFG = $_CFG;
        }else{
            app_error([
                'code' => 500,
                'title' => '应用配置错误',
                'error' => 'cookie或projectId配置错误',
                'info' => '请检查projectId是否配置正确或cookie是否已失效后重新配置'
            ]);
        }
    }

    public function get_list($id=false,$order='updatedDesc'){
        if(!$id)$id = $this->project['_rootCollectionId'];
        $dirs = $this->get_dirs($id,$order);
        $files = $this->get_files($id,$order);
        return ['dirs' => $dirs,'files' => $files];
    }

    public function get_dirs($id,$order='updatedDesc'){
        // 因为project的获取目录和文件是分开的所以数量除以2
        $maxCount = ceil($this->_CFG['teambition']['maxCount']/2);
        $result = teambition::get_dirs($this->projectId,$id,$this->cookie,$maxCount,$order);
        // 如果有message则代表ID错误 返回404
        if($result['message'])return app_error(404);
        return $result;
    }

    public function get_files($id,$order='updatedDesc'){
        // 因为project的获取目录和文件是分开的所以数量除以2
        $maxCount = ceil($this->_CFG['teambition']['maxCount']/2);
        $result = teambition::get_files($this->projectId,$id,$this->cookie,$maxCount,$order);
        // 如果有message则代表ID错误 返回404
        if($result['message'])return app_error(404);
        return $result;
    }

    public function get_dir($id){
        $result = teambition::get_dir($id,$this->cookie);
        if($result){
            return $result;
        }else{
            return app_error(404);
        }
    }
}