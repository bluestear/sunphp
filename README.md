# sunphp-web

## 介绍
[sunphp](https://gitee.com/bluestear/sunphp) 是一个支持多用户，多平台，多应用的开发框架！其根本目的是实现快速开发公众号、小程序、H5、APP，无需开发者重复造轮子！为每一位开发者赋能！

## 官方文档
[sunphp官方文档](https://bluestear.gitee.io/sunphp-web)


## 技术支持
采用vue管理后台、thinkphp6多应用模式开发，安装环境要求：PHP7.4+Mysql（推荐5.7）+Nginx/Apache

## 功能支持
内置常用的微信支付、支付宝支付、文件上传、七牛云存储、阿里云OSS、腾讯云COS，微信登录、阿里云短信、腾讯云短信、邮件发送等功能，开发者只需要查看/app/demo/controller/目录下的示例，对照使用既可！

## 兼容支持
/addons/目录下创建的模块，作为兼容性功能，支持兼容运行微擎2.0模块。(但是**不推荐作为新应用开发**)

## 环境要求
安装环境要求：PHP7.4+Mysql（推荐5.7）+Nginx/Apache

## 安装步骤
1. 下载[install.php](https://bluestear.gitee.io/sunphp-web/install.html)文件到网站根目录
2. 取消php7.4禁用函数——exec
3. 访问https://您的域名/install.php安装本系统
4. 根据提示配置您的数据库、管理后台admin密码
5. 在服务器文件/etc/ssh/ssh_config末尾添加配置
<br/>
Include /www/wwwroot/sunphp.git/*.conf


## 特技

1.  使用 Readme\_XXX.md 来支持不同的语言，例如 Readme\_en.md, Readme\_zh.md
2.  Gitee 官方博客 [blog.gitee.com](https://blog.gitee.com)
3.  你可以 [https://gitee.com/explore](https://gitee.com/explore) 这个地址来了解 Gitee 上的优秀开源项目
4.  [GVP](https://gitee.com/gvp) 全称是 Gitee 最有价值开源项目，是综合评定出的优秀开源项目
5.  Gitee 官方提供的使用手册 [https://gitee.com/help](https://gitee.com/help)
6.  Gitee 封面人物是一档用来展示 Gitee 会员风采的栏目 [https://gitee.com/gitee-stars/](https://gitee.com/gitee-stars/)
