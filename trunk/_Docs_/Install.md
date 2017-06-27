# 开发环境部署安装说明 #

## WEB服务配置 ##

### Apace（如果启用了.htaccess 发现，则不需要配置） ###
    <VirtualHost *:80>
        ServerAdmin deepseath@localhost
        DocumentRoot "D:\vchangyi\oa\trunk\www"
        ServerName qy.vchangyi.org
        ServerAlias qy.vchangyi.org
        ErrorLog "D:\webserver\logs\Apache\vcy_oa_error.log"
        <IfModule mod_rewrite.c>
            RewriteEngine on
            RewriteCond %{REQUEST_URI} !^/admincp
            RewriteCond %{REQUEST_URI} !^/h5
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteCond %{REQUEST_FILENAME} !\.(js|ico|gif|jpg|png|css|xml|swf|txt|woff|ttf|eot|svg|otf|php|htm|html|map)$
            RewriteRule (.*) /index.php/$1 [L]
        </IfModule>
        SetEnv RUN_MODE development
    </VirtualHost>

### Ngnix ###

    server {
        listen 80;
        server_name qy.vchangyi.org;
        location ~* ^.+.(css|eot|gif|htm|html|ico|jpeg|jpg|js|otf|png|svg|swf|thumb|ttf|txt|woff|xml) {
            if ($request_uri ~ ^/([^/]+)/([^/]+)/h5/index.html) {
                rewrite ^(.*)/h5 /h5/index.html break;
            }
            if ($request_uri ~ ^/([^/]+)/([^/]+)/h5/(.*)(jpg|jpeg|gif|css|png|js|ico|thumb|xml|swf|tx
            t|woff|ttf|eot|svg|otf|apk)) {
                rewrite ^(.*)/h5/(.*) /h5/$2 break;
            }
            root D:/vchangyi/oa/trunk/Public;
        }
        location / {
            root D:/vchangyi/oa/trunk/www;
            index index.php index.html index.htm;

            if ($request_uri ~ ^/([^/]+)/([^/]+)/h5) {
                rewrite ^(.*)/h5 /h5/index.html break;
            }
            rewrite ^(.*)$ /index.php last;
        }
        autoindex off;
        include advanced_settings.conf;
        location ~ [^/]\.php(/|$) {
            root D:/vchangyi/oa/trunk/www;
            set $run_mode "development";
            fastcgi_pass bakend;
            fastcgi_index index.php;
            fastcgi_split_path_info ^((?U).+\.php)(/?.+)$;
            fastcgi_param SCRIPT_FILENAME  $document_root$fastcgi_script_name;
            fastcgi_param PATH_INFO $fastcgi_path_info;
            fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
            fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
            fastcgi_param RUN_MODE $run_mode;
            include fastcgi.conf;
        }
    }


### 数据库和基础代码部署配置 ###

> 本地 Hosts 文件做 127.0.0.1 qy.vchangyi.org 的映射关系。
> 
> 创建数据库：vchangyi_oa2
>  
> 复制 trunk/ThinkPHP/Conf.bak 改名为 trunk/ThinkPHP/Conf
>  
> 复制 trunk/Common/Common/Conf.bak 改名为 trunk/Common/Common/Conf
> 
> 确保：trunk/Common/Runtime 目录是具有可读写权限的，此目录是系统运行缓存和文本日志存储目录
> .
> 修改 trunk/ThinkPHP/Conf/convention.php 内关于数据库配置信息
>
> 访问 [http://qy.vchangyi.org/](http://qy.vchangyi.org/) 没有错误出现即完成配置
> 
> 具体演示可以查看 /_Docs_/Demo.md 内的说明
> 



