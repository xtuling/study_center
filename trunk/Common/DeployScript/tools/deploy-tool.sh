#!/usr/bin/env bash

#############
# 备份服务器上的生产环境基础配置
# $1: 服务器IP
# $2: 新框架配置名称
# $3: 备份版本号
# $4: 部署生产目录
# $5: 部署备份目录
# $6: 应用名称
#############
function tool_backup() {

    echo "备份----创建备份目录"
    ssh root@$1 "mkdir -p $5 && cd $5 && pwd"

    # 新框架配置打包名称
    config_name="$2$3"
    target_dir="$4/$6"

    echo "备份----备份新框架配置,并移动到备份目录：$5"
    ssh root@$1 "mkdir -p ${target_dir} && chmod -R 755 ${target_dir} && cd ${target_dir} && find . -name '*.php'|grep Conf/|xargs tar -cvzf ${config_name} && mv -f ${config_name} $5"

    echo "备份结束"
}


#############
# 打包工程代码，并发布到临时目录
# $1: 发布工程目录
# $2: 源码包名称
# $3: 服务器IP
# $4: 临时目录
# $5: 应用目录
#############
function tool_pack() {

    handle_temp_directory $4 $2 $3 $1 $5
    echo "打包结束"
}

#############
# 发布生产目录
# $1：服务器IP
# $2: 源码包名称
# $3: 临时目录
# $4: 部署目录
# $5: 框架配置
# $6: 备份文件版本号
# $7: 生产环境备份目录
# $8: 发布工程目录
# $9: 应用目录
#############
function tool_deploy() {

    echo "发布----将备份目录中的线上配置拷贝到临时目录"
    ssh root@$1 "cp -rf $7/$5$6 $3"

    echo "发布----解压项目源码： $2"
    ssh root@$1 "cd $3 && tar -zxvf $2 1>/dev/null"

    echo "发布----解压框架线上配置文件：$5$6$"
    ssh root@$1 "cd $3 && tar -zxvf $5$6 -C trunk/$9"

    echo "发布----将临时目录中的文件拷贝并覆盖到生产目录：$4"
    ssh root@$1 "cp -rf $3/trunk/* $4/"

    # 获取目录列表
    cd $8
    config_d="trunk"
    # 删除临时文件
    for file in `ls ${config_d}`
    do
        if [ -d "${config_d}/$file/Common/Conf.bak" ]
        then
            echo "删除 rm -f ${file}/Common/Conf/debug.php"
            ssh root@$1 "cd $4 && rm -f ${file}/Common/Conf/debug.php"
            echo "删除 rm -f ${file}/Runtime/common~runtime.php"
            ssh root@$1 "cd $4 && rm -f ${file}/Runtime/common~runtime.php"
            echo "删除 rm -f ${file}/Runtime/Data/_fields/*.php"
            ssh root@$1 "cd $4 && rm -f ${file}/Runtime/Data/_fields/*.php"
        fi
    done

    echo "发布结束"
}

#############
# 创建框架的config目录、将bak的配置文件拷贝到config目录下
# 打包后将源码发布到服务器上的临时目录中
# $1: 临时目录
# $2: 源码包名称
# $3: 服务器IP
# $4: 发布工程目录
# $5: 应用目录
#############
function handle_temp_directory() {

    cd "$4"

    # 处理框架Conf目录配置
    copy_conf_dir $4 $5

    echo "打包----目录: $2"
    tar -czvf $2 trunk/$5 1>/dev/null

    echo "打包----创建临时目录：$1"
    ssh root@$3 "mkdir -p $1 && cd $1 && pwd"

    echo "打包----清空临时目录：$1"
    ssh root@$3 "rm -rf $1/*"

    echo "打包----从发布目录中将打包文件：$2 拷贝到 $3 下的临时目录：$1 中"
    scp -r $2 root@$3:$1/$2
}

#############
# 创建框架Conf目录、将Conf.bak/下的文件拷贝到Conf中
# $1: 发布工程目录
#############
function copy_conf_dir() {

    echo "打包----处理框架Conf目录配置"
    cd $1
    config_d="trunk"
    for file in `ls ${config_d}`
    do
        if [ -d "${config_d}/$file/Common/Conf.bak" ]
        then
            if [[ "" = $2 || $file = $2 ]]; then
                echo "修改配置目录 ${config_d}/$file/Common/Conf.bak"
                mkdir -p "${config_d}/$file/Common/Conf"
                cp -rf ${config_d}/${file}/Common/Conf.bak/* "${config_d}/$file/Common/Conf/"
            fi
        fi
    done

    # ThinkPHP
    if [[ "" = $2 || "ThinkPHP" = $2 ]]
    then
        mkdir -p "trunk/ThinkPHP/Conf"
        cp -rf trunk/ThinkPHP/Conf.bak/* trunk/ThinkPHP/Conf/
    fi
}