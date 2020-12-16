<?php
/**
 * Teambition项目文件类
 * @author FlxSNX<211154860@qq.com>
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
    public static function get_dir($projectId,$dirId,$cookie,$page=1){
        $api = 'https://www.teambition.com/api/collections?';
        $param = [
            '_parentId' => $dirId,
            '_projectId' => $projectId,
            'order' => 'updatedDesc',
            'count' => 50,
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
    public static function get_files($projectId,$dirId,$cookie,$page=1){
        $api = 'https://www.teambition.com/api/works?';
        $param = [
            '_parentId' => $dirId,
            '_projectId' => $projectId,
            'order' => 'updatedDesc',
            'count' => 50,
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
     * 获取文件下载链接
     * @param string $parentId 文件ID
     * @param string $cookie teambitionCookie
     * @return array
     */
    public static function get_download_url($parentId,$cookie){
        $result = self::get('https://www.teambition.com/api/works/'.$parentId,$cookie);
        if($result){
            $resultArray = json_decode($result,true);
            if($resultArray['downloadUrl']){
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
    protected static function get($url,$cookie=false,$post=false,$header=false){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER,$header);
        curl_setopt($ch, CURLOPT_COOKIE,$cookie);
        if($post){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
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