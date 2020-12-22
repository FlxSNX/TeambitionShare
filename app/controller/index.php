<?php
namespace app\controller;
use extend\teambition;
class index{
    public function __init(){
        global $Flx;
        $this->_CFG = $Flx->_CFG;
        $this->cookie = $this->_CFG['teambition']['cookie'];
        $this->projectId = $this->_CFG['teambition']['projectId'];
        assign(['_CFG' => $this->_CFG]);
        if(isset($this->_CFG['password']) && $this->_CFG['password'] != false){
            session_start();
            if($_POST['password'])$_SESSION['password'] = $_POST['password'];
            if($_SESSION['password'] != $this->_CFG['password']){
                view('password');
                exit;
            }
        }
    }

    public function index($url,$dirid=false){
        if(!$this->_CFG['teambition'] && !$this->_CFG['pan']){
            header('Location:init');
        }else{
            if($this->_CFG['type'] == 'project'){
                $project = teambition::get_project($this->projectId,$this->cookie);
                if($project && $project['_id']){
                    if(!$dirid){
                        $dirid = $project['_rootCollectionId'];
                        $root = true;
                    }else{
                        $root = false;
                    }
                    $dirlist = $this->getDir($dirid,$this->cookie,$root);
                    $filelist = $this->getFile($dirid,$this->cookie);
                    assign([
                        'dirlist' => $dirlist,
                        'filelist' => $filelist,
                        'project' => $project,
                        'root' => $root
                    ]);
                    view();
                }else{
                    app_error([
                        'code' => 500,
                        'title' => '应用配置错误',
                        'error' => 'cookie或projectId配置错误',
                        'info' => '请检查projectId是否配置正确或cookie是否已失效后重新配置'
                    ]);
                }
            }elseif($this->_CFG['type'] == 'pan'){
                if(!$dirid){
                    $dirid = $this->_CFG['pan']['rootId'];
                    $root = true;
                }
                $result = $this->getPanList($dirid);
                assign(['panlist' => $result,'title' => $this->_CFG['pan']['title'],'root' => $root]);
                view('index/pan');
            }else{
                app_error([
                    'code' => 500,
                    'title' => '应用配置错误',
                    'error' => '配置错误',
                    'info' => '类型只能为project或pan'
                ]);
            }
        }
    }

    public function getDownload($url,$id){
        $type = authstr($_GET['type']) ?: 302;
        if($this->_CFG['type'] == 'project'){
            $result = teambition::get_download_url($id,$this->cookie);
            if($result){
                if($result['fileType'] == 'mp4' and $_SERVER['HTTP_RANGE'] == NULL){
                    assign('item',$result);
                    view('index/_play');
                }elseif($result['downloadUrl']){
                    if($type == 'json'){
                        $result = [
                            'code' => 200,
                            'msg' => '解析成功',
                            'data' => $result
                        ];
                        exit(json_encode($result));
                    }elseif($type == 302){
                        header('Location:'.$result['downloadUrl']);
                    }elseif($type == 'url'){
                        exit($result['downloadUrl']);
                    }
                }else{
                    exit('{"code":500,"msg":"解析失败"}');
                }
            }else{
                exit('{"code":500,"msg":"解析失败"}');
            }
        }elseif($this->_CFG['type'] == 'pan'){
            $result = teambition::get_pan_file($this->_CFG['pan']['cookie'],$this->_CFG['pan']['orgId'],$this->_CFG['pan']['spaceId'],$this->_CFG['pan']['driveId'],$id);
            if($result){
                if($result['ext'] == 'mp4' and $_SERVER['HTTP_RANGE'] == NULL){
                    assign('item',$result);
                    view('index/_play');
                }elseif($result['downloadUrl']){
                    if($type == 'json'){
                        $result = [
                            'code' => 200,
                            'msg' => '解析成功',
                            'data' => $result
                        ];
                        exit(json_encode($result));
                    }elseif($type == 302){
                        header('Location:'.$result['downloadUrl']);
                    }elseif($type == 'url'){
                        exit($result['downloadUrl']);
                    }
                }else{
                    exit('{"code":500,"msg":"解析失败"}');
                }
            }
        }
    }

    private function getDir($id,$cookie,$root=false){
        $data = [];
        $i = 1;
        while($result = teambition::get_dir($this->projectId,$id,$cookie,$i)){
            if($result['message'])return app_error(404);
            if($root)unset($result[(count($result) - 1)]);
            $data = array_merge($data,$result);
            $i++;
        }
        return $data;
    }

    private function getFile($id,$cookie){
        $data = [];
        $i = 1;
        while($result = teambition::get_files($this->projectId,$id,$cookie,$i)){
            if($result['message'])return app_error(404);
            $data = array_merge($data,$result);
            $i++;
        }
        return $data;
    }

    private function getPanList($id){
        $result = teambition::get_pan_list($this->_CFG['pan']['cookie'],$this->_CFG['pan']['orgId'],$this->_CFG['pan']['spaceId'],$this->_CFG['pan']['driveId'],$id);
        if($result['data']['message'])return app_error(404);
        $count = $result['totalCount'];
        if($count > 100){
            $result = teambition::get_pan_list($this->_CFG['pan']['cookie'],$this->_CFG['pan']['orgId'],$this->_CFG['pan']['spaceId'],$this->_CFG['pan']['driveId'],$id,$count);
            return $result;
        }else{
            return $result;
        }
    }
}