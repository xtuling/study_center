#!/usr/bin/env bash

if [ $1 =='' ]
then
    echo '''
        请指定工作目录：
            ./ws-init.sh <work path>

        例如：
            ./ws-init.sh `pwd` # 指定当前目录
    '''
    exit 1;
fi

export WS_PATH=$1;
echo "工作目录：$WS_PATH"

export LOCAL_HOST_IP="`/sbin/ifconfig -a|grep inet|grep -v 127.0.0.1|grep -v inet6|awk '{print $2}'|tr -d "addr:"`"
echo "本机IP地址：$LOCAL_HOST_IP"

echo
echo

# 项目配置初始化
echo "项目代码配置初始化。。。"
code_path="${WS_PATH}/trunk";
ignore_path=(
"^ThinkPHP"
"^www"
"^_.*"
)

true=1;
false=0

is_ignore() {
    for ignore in ${ignore_path[*]}
    do
        if [[ $1 =~ $ignore ]]; then
            echo "忽略目录：$file";
            return $true;
        fi
    done
    return $false;
}

filelist=`ls ${code_path}`
for file in $filelist
do
    is_ignore $file;
    if [[ $? == $false ]]; then
        echo "处理模块：$file"
        if [ ! -d "$code_path/$file/Common/Conf" ]; then
            cp -rf "$code_path/$file/Common/Conf.bak" "$code_path/$file/Common/Conf"
        fi
        echo "        需要手动【$file 模块】修改 db 配置"
    fi
done

echo "处理特殊模块：ThinkPHP";
if [ ! -d "$code_path/ThinkPHP/Conf" ]; then
    cp -rf "$code_path/ThinkPHP/Conf.bak" "$code_path/ThinkPHP/Conf"
fi

echo
echo

## 创建初始化目录
mkdir $WS_PATH $WS_PATH/data $WS_PATH/data/mysql $WS_PATH/log $WS_PATH/log/nginx $WS_PATH/log/php
chmod -R 755 $WS_PATH

build=$2
# 启动 docker 容器
docker-compose -f docker-compose.yml stop
docker-compose -f docker-compose.yml up $build
