# docker-php-env

php 基于docker的开发环境框架

# todos

1. [x] docker php 
2. [x] docker nginx
3. [x] 支持 debug (使用xdebug)
4. [x] docker mysql
5. [ ] docker redis
6. [ ] windows .bat 初始化脚本
7. [ ] mysql 开发数据库自动建立和升级

# 使用说明

1. 安装 docker 环境
    
    [获取docker](https://www.docker.com/products/docker#)：
    [mac](https://download.docker.com/mac/stable/Docker.dmg)、[windows](https://download.docker.com/win/stable/InstallDocker.msi)
    
    验证验证安装是否成功，在终端输入如下命令：
    
    ```
    
    docker version
    
    ```
    
    如果没有错误输入，docker安装成功

2. 建立php工作目录

    ```bash
    
    ./ws-init.sh < workspace-path >
    
    ```
    
    这个会在 `workspace-path` 目录下建立以下目录
    
    ```
    
    workspace-path 
        |
        |--htdocs
        |    |
        |    |--cyphp_app
        |    
        |--log
        |   |
        |   |--nginx
        |   |--php
        |
        |--data
        |   |
        |   |--mysql
    ```

2. 拉取 php 代码到 `<workspace-path>/htdocs/cyphp_app`

    ** 目前支持项目名为 `oa2` **

3. 配置 PHP 工程 `Conf` 目录

4. 配置 PHP 开发数据库

5. phpStorm 调试配置

6. 碰到问题, 先查看question.md, 如果没有, 解决后更新question.md

# 开发说明

# 问题列表

[查看 question.md](http://gitlab.vchangyi.com/wangliping/docker-php-env/blob/master/question.md)

# 版本跟新

## v0.3.0

    * docker-compose.yml 配置文件 2.1 版本

## v0.2.1

    * 支持 php、nginx
    * 支持 xdebug 调试
    * 支持 mysql