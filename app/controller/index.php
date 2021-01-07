<?php
namespace app\controller;
use extend\teambition;
use app\controller\teambition\project;
use app\controller\teambition\pan;
class index{
    public function __init(){
        $version = '1.14';
        global $Flx;
        $this->_CFG = $Flx->_CFG;
        $this->cookie = $this->_CFG['teambition']['cookie'];
        assign(['_CFG' => $this->_CFG,'version' => $version]);
        // 全局密码
        if(isset($this->_CFG['password']) && $this->_CFG['password'] != false){
            session_start();
            if(is_ajax()){
                if(!$_POST['id']){
                    $password = $_POST['password'];
                    if($_POST['password'] == $this->_CFG['password']){
                        $_SESSION['password'] = $_POST['password'];
                        exit('{"code":200,"msg":"密码正确,3秒后跳转"}');
                    }else{
                        exit('{"code":500,"msg":"密码错误"}');
                    }
                }
            }
            if($_SESSION['password'] != $this->_CFG['password']){
                assign(['title' => $this->_CFG['title']]);
                view('password');
                exit;
            }
        }
    }

    public function index($id=false){
        if(!$this->_CFG['teambition'] && !$this->_CFG['pan']){
            header('Location:init');
        }else{
            if($this->_CFG['type'] == 'project'){
                $project = new project($this->_CFG);

                if(!$id)$id = $project->info['_rootCollectionId'];

                $list = $project->get_list($id);

                $dirlist = $list['dirs'];
                $filelist = $list['files'];
                $dir = $project->get_dir($id);
                
                // 查询是否有.password文件
                $fileindex = 0;
                $pass = false;
                foreach($filelist as $file){
                    if($file['fileName'] == '.password'){
                        // 如果文件大于2kb忽略掉
                        if($file['fileSize'] < 2048){
                            $pass = curl($file['downloadUrl']);
                            unset($filelist[$fileindex]);
                            $filelist = array_values($filelist);
                        }
                        break;
                    }
                    $fileindex++;
                }

                assign([
                    'dirlist' => $dirlist,
                    'filelist' => $filelist,
                    'title' => $this->_CFG['title'],
                    'dir' => $dir
                ]);
                
                // 目录密码验证
                if($pass !== false){
                    session_start();
                    if(is_ajax() && !empty($_POST['id'])){
                        if($_POST['password'] == $pass){
                            $_SESSION[$_POST['id']] = $_POST['password'];
                            exit('{"code":200,"msg":"密码正确,3秒后跳转"}');
                        }else{
                            exit('{"code":500,"msg":"密码错误"}');
                        }
                    }
                    if($_SESSION[$id] == $pass){
                        view();
                    }else{
                        assign(['id' => $id]);
                        view('password');
                    }
                }else{
                    view();
                }
            }elseif($this->_CFG['type'] == 'pan'){
                $pan = new pan($this->_CFG);
                if(!$id)$id = $this->_CFG['pan']['rootId'];

                $list = $pan->get_list($id);
                $dir = $pan->get_dir($id);
                 
                // 查询是否有.password文件
                $fileindex = 0;
                $pass = false;
                foreach($list['data'] as $file){
                    if($file['kind'] != 'folder' && $file['name'] == '.password'){
                        // 如果文件大于2kb忽略掉
                        if($file['fileSize'] < 2048){
                            $pass = curl($file['downloadUrl']);
                            unset($list['data'][$fileindex]);
                            $list['data'] = array_values($list['data']);
                        }
                        break;
                    }
                    $fileindex++;
                }

                assign([
                    'panlist' => $list,
                    'title' => $this->_CFG['title'],
                    'dir' => $dir
                ]);
                
                // 目录密码验证
                if($pass !== false){
                    session_start();
                    if(is_ajax() && !empty($_POST['id'])){
                        if($_POST['password'] == $pass){
                            $_SESSION[$_POST['id']] = $_POST['password'];
                            exit('{"code":200,"msg":"密码正确,3秒后跳转"}');
                        }else{
                            exit('{"code":500,"msg":"密码错误"}');
                        }
                    }
                    if($_SESSION[$id] == $pass){
                        view('index/pan');
                    }else{
                        assign(['id' => $id]);
                        view('password');
                    }
                }else{
                    view('index/pan');
                }
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

    public function getDownload($id){
        $type = authstr($_GET['type']) ?: 302;
        if($this->_CFG['type'] == 'project'){
            $result = teambition::get_download_url($id,$this->cookie);
            if($result){
                if($result['downloadUrl']){
                    if(isset($_GET['preview'])){
                        assign([
                            'fileid' => $id,
                            'filename' => $result['fileName'],
                            'filetype' => $result['fileType'],
                            'downloadUrl' => $result['downloadUrl'],
                            'title' => $this->_CFG['title']
                        ]);
                        view('index/_preview');
                    }else{
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
                if($result['downloadUrl']){
                    if(isset($_GET['preview'])){
                        assign([
                            'fileid' => $id,
                            'filename' => $result['name'],
                            'filetype' => $result['ext'],
                            'downloadUrl' => $result['downloadUrl'],
                            'title' => $this->_CFG['title']
                        ]);
                        view('index/_preview');
                    }else{
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
                    }
                }else{
                    exit('{"code":500,"msg":"解析失败"}');
                }
            }
        }
    }
}