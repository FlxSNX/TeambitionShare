# TeambitionShare
挂载Teambition文件(网盘文件列表程序)  
## 说明
已支持Teambition网盘(需申请)与Teambition项目  
**希望各位修改页面底部信息时保留本项目的github链接！**  
**如果升级版本时出现报错 请删除 config/app.cfg.php 然后在配置向导中重新生成配置**  
  
PHP版本要求 ≥ 5.6  
PHP版本推荐 ≥ 7.0   
项目挂载演示站点:[tbfile.ouoacg.com](http://tbfile.ouoacg.com)  
网盘挂载演示站点:[tbfile.ouoacg.com/pan](http://tbfile.ouoacg.com/pan) 访问密码:123456  
## 一些问题
1.Cookie有效期  
目前未遇到cookie失效的情况,猜测只要你不在官网手动退出登录就不会失效  
2.下载速度  
下载速度有些不稳定,有时快有时慢(1MB/S);  
3.访问密码  
①)全局密码  
在config/app.cfg.php中添加 `'password' => '你要设置的密码'` 即可  
②)目录密码  
在目录下上传一个文件`.password`,文件内容即为目录密码  
4.二级目录运行  
放在二级目录运行,配置的时候填入对应的URL和[修改伪静态规则](#伪静态规则)(Apache无需修改)即可  
## 如何使用
在没有配置文件时访问网站会跳转到配置向导,在配置向导页面填写对应的参数即可生成配置文件  
   
先去 www.teambition.com 注册登录  
Cookie获取:  
F12 -> Network -> 刷新一下 找到如图所示的cookie  
![image](https://ae01.alicdn.com/kf/U6ac816255ae44212a0b10f8d56b8cc01k.jpg)  

项目ID(projectId)获取:  
先创建一个项目,然后进入创建的项目  
![image](https://ae01.alicdn.com/kf/U78fa30b3f30b47de96af1449808e153cV.jpg)  
![image](https://ae01.alicdn.com/kf/Ube8a1476632a48c59f760d19fec97f79F.jpg)  

## 伪静态规则

### Nginx
```
# 根目录伪静态
location / {
  if (!-e $request_filename){
    rewrite ^(.*)$ /index.php/?s=$1;
  }
}
# 二级目录伪静态，自行修改pan为你的二级目录名字
location /pan {
  if (!-e $request_filename){
    rewrite ^/pan/(.*)$ /pan/index.php/?s=$1;
  }
}
```

### Apache
```
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php/?s=$1 [QSA,PT,L]
</IfModule>
```

## Docker
```
docker pull flxsnx/teambitionshare
docker run -d -p 8081:80 flxsnx/teambitionshare:latest
# 访问: http://ip:8081
```