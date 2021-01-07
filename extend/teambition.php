<?php
/**
 * Teambition操作类
 * @author 拾年<211154860@qq.com>
 * @version 1.1
 */

namespace extend;
class teambition{
    /**
     * 获取登录所需的Token
     * @return string
     */
    public static function get_login_token(){
        $html = self::get('https://account.teambition.com/login/password');
        if(preg_match('/"TOKEN":"([a-zA-Z0-9_\-\.]+)"/',$html,$match)){
            return $match[1];
        }else{
            return false;
        }
        
    }

    /**
     * 登录获取Cookie
     * @param string $username 账号
     * @param string $password 密码
     * @return string
     */
    public static function login($username,$password){
        $token = self::get_login_token();
        $postJson = json_encode([
            'phone' => $username,
            'password' => $password,
            'token' => $token,
            'client_id' => '90727510-5e9f-11e6-bf41-15ed35b6cc41',
            'response_type' => 'session'
        ]);
        $result = self::get('https://account.teambition.com/api/login/phone',0,$postJson,1);
        if($result){
            $cookie = '';
            if(preg_match('/TEAMBITION_SESSIONID=([a-zA-Z0-9=]+);/',$result['header'],$match)){
                $cookie .= $match[0];
            }
            if(preg_match('/TEAMBITION_SESSIONID\.sig=([a-zA-Z0-9=\-_]+);/',$result['header'],$match)){
                $cookie .= $match[0];
            }
            return $cookie;
        }else{
            return false;
        }
    }

    /**
     * 获取项目列表
     * @param string $cookie teambitionCookie
     * @return array
     */
    public static function get_projects($cookie){
        $api = 'https://www.teambition.com/api/v2/projects?';
        $param = [
            '_organizationId' => '000000000000000000000405',
            'selectBy' => 'joined',
            'orderBy' => 'name',
            'pageToken' => '',
            'pageSize' => 20
        ];
        $result = self::get($api.http_build_query($param),$cookie);
        if($result){
            $result = json_decode($result,true);
            return $result;
        }else{
            return false;
        }
    }

    /**
     * 获取项目信息
     * @param string $projectID 项目ID
     * @param string $cookie teambitionCookie
     * @return array
     */
    public static function get_project($projectId,$cookie){
        $api = 'https://www.teambition.com/api/projects/'.$projectId;
        $result = self::get($api,$cookie);
        if($result){
            $result = json_decode($result,true);
            return $result;
        }else{
            return false;
        }
    }

    /**
     * 获取文件夹下的文件夹
     * @param string $projectID 项目ID
     * @param string $dirid 文件夹ID
     * @param string $cookie teambitionCookie
     * @param int $page 页码
     * @return array
     */
    public static function get_dirs($projectId,$dirId,$cookie,$count=100,$order='updatedDesc',$page=1){
        $api = 'https://www.teambition.com/api/collections?';
        $param = [
            '_parentId' => $dirId,
            '_projectId' => $projectId,
            'order' => $order,
            'count' => $count,
            'page' => $page
        ];
        $result = self::get($api.http_build_query($param),$cookie);
        if($result){
            $result = json_decode($result,true);
            return $result;
        }else{
            return false;
        }
    }

    /**
     * 获取文件夹下的文件
     * @param string $projectID 项目ID
     * @param string $dirid 文件夹ID
     * @param string $cookie teambitionCookie
     * @param int $page 页码
     * @return array
     */
    public static function get_files($projectId,$dirId,$cookie,$count=100,$order='updatedDesc',$page=1){
        $api = 'https://www.teambition.com/api/works?';
        $param = [
            '_parentId' => $dirId,
            '_projectId' => $projectId,
            'order' => $order,
            'count' => $count,
            'page' => $page
        ];
        $result = self::get($api.http_build_query($param),$cookie);
        if($result){
            $result = json_decode($result,true);
            return $result;
        }else{
            return false;
        }
    }

    /**
     * 获取文件下载链接(获取文件信息)
     * @param string $parentId 文件ID
     * @param string $cookie teambitionCookie
     * @return array
     */
    public static function get_download_url($parentId,$cookie){
        $result = self::get('https://www.teambition.com/api/works/'.$parentId,$cookie);
        if($result){
            $result = json_decode($result,true);
            if($result['downloadUrl']){
                return $result;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public static function get_dir($parentId,$cookie){
        $result = self::get('https://www.teambition.com/api/collections/'.$parentId,$cookie);
        if($result){
            $result = json_decode($result,true);
            if($result){
                return $result;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 获取挂载网盘的相关配置
     * @param string $cookie teambitionCookie
     * @return array
     */
    public static function get_pan_config($cookie){
        $config = [];
        $org = self::get_orgId($cookie);
        if($org){
            $config['orgId'] = $org['_id'];
            $config['memberId'] = $org['_creatorId'];
            $space = self::get_spaceId($cookie,$config['orgId'],$config['memberId']);
            if($space){
                $config['spaceId'] = $space[0]['spaceId'];
                $config['rootId'] = $space[0]['rootId'];
                $drive = self::get_driveId($cookie,$config['orgId']);
                if($drive){
                    $config['driveId'] = $drive['data']['driveId'];
                    return $config;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 获取网盘文件列表
     * @param string $cookie teambitionCookie
     * @param string $orgId
     * @param string $spaceId
     * @param int $driveId
     * @param string $parentId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function get_pan_list($cookie,$orgId,$spaceId,$driveId,$parentId,$limit=100,$offset=0){
        $api = 'https://pan.teambition.com/pan/api/nodes?';
        $param = [
            'orgId' => $orgId,
            'spaceId' => $spaceId,
            'driveId' => $driveId,
            'parentId' => $parentId,
            'offset' => $offset,
            'limit' => $limit,
            'orderBy' => 'updateTime',
            'orderDirection' => 'desc'
        ];
        $result = self::get($api.http_build_query($param),$cookie);
        if($result){
            $result = json_decode($result,true);
            return $result;
        }else{
            return false;
        }
    }

    /**
     * 获取网盘文件(文件夹)信息
     * @param string $cookie teambitionCookie
     * @param string $orgId
     * @param string $spaceId
     * @param int $driveId
     * @param string $parentId
     * @return array
     */
    public static function get_pan_file($cookie,$orgId,$spaceId,$driveId,$parentId){
        $api = 'https://pan.teambition.com/pan/api/nodes/'.$parentId.'?';
        $param = [
            'orgId' => $orgId,
            'spaceId' => $spaceId,
            'driveId' => $driveId,
        ];
        $result = self::get($api.http_build_query($param),$cookie);
        if($result){
            $result = json_decode($result,true);
            return $result;
        }else{
            return false;
        }
    }

    /**
     * 获取网盘的orgId&memberId
     * @param string $cookie teambitionCookie
     * @return array
     */
    public static function get_orgId($cookie){
        $api = 'https://www.teambition.com/api/organizations/personal';
        $result = self::get($api,$cookie);
        if($result){
            $result = json_decode($result,true);
            if($result['_id'] && $result['_creatorId']){
                return $result;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 获取网盘的spaceId&根rootId
     * @param string $cookie teambitionCookie
     * @param string $orgId
     * @param string $memberId
     * @return array
     */
    public static function get_spaceId($cookie,$orgId,$memberId){
        $api = 'https://pan.teambition.com/pan/api/spaces?';
        $param = [
            'orgId' => $orgId,
            'memberId' => $memberId
        ];
        $result = self::get($api.http_build_query($param),$cookie);
        if($result){
            $result = json_decode($result,true);
            if($result[0]['spaceId'] && $result[0]['rootId']){
                return $result;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 获取网盘的driveId
     * @param string $cookie teambitionCookie
     * @param string $orgId
     * @return array
     */
    public static function get_driveId($cookie,$orgId){
        $api = 'https://pan.teambition.com/pan/api/orgs/'.$orgId;
        $result = self::get($api,$cookie);
        if($result){
            $result = json_decode($result,true);
            if($result['data']['driveId']){
                return $result;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Curl Json Post & Get
     * @param string $url 请求地址
     * @param string $cookie 请求携带Cookie
     * @param int $post 是否为POST
     * @param int $header 是否返回header
     */
    public static function get($url,$cookie=false,$post=false,$header=false){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER,$header);
        curl_setopt($ch, CURLOPT_COOKIE,$cookie);
        if($post){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT,10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.183 Safari/537.36');
        $result = curl_exec($ch);
        if($header){
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($result, 0, $headerSize);
            $body = substr($result, $headerSize);
            $result = [];
            $result['header'] = $header;
            $result['body'] = $body;
        }
        curl_close($ch);
        return $result;
    }
}