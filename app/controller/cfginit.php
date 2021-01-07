<?php
namespace app\controller;
use extend\teambition;
class cfginit{
    public function init(){
        global $Flx;
        $this->_CFG = $Flx->_CFG;
        if(!$this->_CFG['teambition'] && !$this->_CFG['pan']){
            if(is_ajax()){
                $type = authstr($_POST['type']) ?: NULL;
                $title = authstr($_POST['title']) ?: NULL;
                $url = authstr($_POST['url']) ?: NULL;
                $cookie = authstr($_POST['cookie']) ?: NULL;
                if(!$type || !$title || !$url || !$cookie)exit('{"code":500,"msg":"参数错误"}');
                if($type == 'pan'){
                    $result = teambition::get_pan_config($cookie);
                    if($result){
                        $config = <<<TEXT
<?php
return [
    'type' => '$type',
    'url' => '$url',
    'pan' => [
        'cookie' => '$cookie',
        'orgId' => '$result[orgId]',
        'spaceId' => '$result[spaceId]',
        'driveId' => '$result[driveId]',
        'rootId' => '$result[rootId]',
        'maxCount' => 1000
    ],
    'title' => '$title'
];
TEXT;

                        $savecfg = file_put_contents(ROOTDIR.'config/app.cfg.php', $config);
                        if($savecfg){
                            exit('{"code":200,"msg":"配置成功"}');
                        }else{
                            exit('{"code":500,"msg":"保存信息时出错"}');
                        }
                    }else{
                        exit('{"code":500,"msg":"获取网盘信息失败:cookie错误或未开通网盘"}');
                    }
                }elseif($type == 'project'){
                    $projectId = authstr($_POST['projectId']) ?: NULL;
                    if(!$projectId)exit('{"code":500,"msg":"缺少projectId"}');
                    $project = teambition::get_project($projectId,$cookie);
                    if($project){
                        $config = <<<TEXT
<?php
return [
    'type' => '$type',
    'url' => '$url',
    'teambition' => [
        'cookie' => '$cookie',
        'projectId' => '$projectId',
        'maxCount' => 1000
    ],
    'title' => '$title'
];
TEXT;

                        $savecfg = file_put_contents(ROOTDIR.'config/app.cfg.php', $config);
                        if($savecfg){
                            exit('{"code":200,"msg":"配置成功"}');
                        }else{
                            exit('{"code":500,"msg":"保存信息时出错"}');
                        }
                    }else{
                        exit('{"code":500,"msg":"获取项目信息失败:cookie或projectId错误"}');
                    }
                }
            }
            assign([
                'title' => '配置向导',
                'version' => '1.14'
            ]);
            view('cfginit');
        }else{
            app_error([
                'code' => 403,
                'title' => '拒绝访问',
                'error' => '请问重复配置',
                'info' => '如果需要重新配置,请将配置文件{app.cfg.php}清空！'
            ]); 
        }
    }
}