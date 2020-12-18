# TeambitionShare
挂载Teambition项目中的文件 直链分享  
PHP版本推荐 >= 5.6  
伪静态规则分别在.htaccess 和 nginx 文件中  
演示站点:[tbfile.ouoacg.com](http://tbfile.ouoacg.com)  
Teambition网盘的挂载正在开发中...
## 一些问题
1.Cookie有效期  
目前我自己的Cookie使用了1个多月未失效,猜测只要你不在官网手动退出登录就不会失效  
2.下载速度  
开源前测试能跑到20MB/s,2020-12-17测试时速度下架到1MB/s左右  
2020-12-18 测试能到10MB+/s 然后会下降到1MB/s  
感觉下载速度有点不稳定
## 如何使用
先去 www.teambition.com 注册登录  
然后去项目 创建一个项目  
![image](https://ae01.alicdn.com/kf/U78fa30b3f30b47de96af1449808e153cV.jpg)  
然后获取Cookie和项目ID 填到config/app.cfg.php中  
![image](https://ae01.alicdn.com/kf/U6ac816255ae44212a0b10f8d56b8cc01k.jpg)  
![image](https://ae01.alicdn.com/kf/Ube8a1476632a48c59f760d19fec97f79F.jpg)  