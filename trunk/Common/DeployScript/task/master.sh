#!/usr/bin/env bash
# 发布环境配置

PROJ_DIR="$WORKSPACE/"                                    # 发布工程目录
CONFIG="Conf_"                                            # 新框架配置名称
PACKAGE_NAME=`basename $0 sh`"tar.gz"                     # 打包名称
VERSION=$(date +%Y%m%d%H%M%S)"_"`basename $0 sh`"tar.gz"  # 备份版本名称

# 以下变量值根据上线的应用自行修改
HOST="182.254.149.180"                                    # 服务器IP
TEMP_DIR="/data/wwwroot/publish_tmp_dir/master_temp"                   # 部署临时目录
DEPLOY_DIR="/data/wwwroot/master.vchangyi.com"                         # 部署生产目录
BAK_DIR="/data/wwwroot/publish_tmp_dir/master_bak"                     # 部署备份目录


# 加载发布工具方法
. ${PROJ_DIR}/trunk/Common/DeployScript/tools/deploy-tool.sh

# 备份线上生产配置
tool_backup ${HOST} ${CONFIG} ${VERSION} ${DEPLOY_DIR} ${BAK_DIR}

# 打包并发布到线上临时目录
tool_pack ${PROJ_DIR} ${PACKAGE_NAME} ${HOST} ${TEMP_DIR}

# 将临时目录源码发布到线上生产目录
tool_deploy ${HOST} ${PACKAGE_NAME} ${TEMP_DIR} ${DEPLOY_DIR} ${CONFIG} ${VERSION} ${BAK_DIR} ${PROJ_DIR}
