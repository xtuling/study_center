<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/3/20
 * Time: 14:14
 */

namespace Common\Common;

class Constant
{
    /**
     * 计划任务执行时间表达式（1分钟执行一次）
     */
    const TASK_CRON_TIME = '0 0/1 * * * ?';

    /*
     * 分片文件临时目录
     */
    const PART_FILE_DIR = CODE_ROOT . D_S . 'Common' . D_S . 'Runtime' . D_S . 'Temp' . D_S . 'PluploadFiles' . D_S . QY_DOMAIN;

    /**
     * 支持上传文件格式：文件
     */
    const ALLOW_UPLOAD_FILE_TYPE = ['TXT','PDF','PPT','PPTX','DOC','DOCX','XLS','XLSX'];

    /**
     * 文件大小上限：200M
     */
    const FILE_SIZE_LIMIT = 200 * 1024 * 1024;

    /**
     * 支持上传文件格式：图片
     */
    const ALLOW_UPLOAD_IMAGE_TYPE = ['JPG','PNG','GIF','BMP','TIF','JPEG'];

    /**
     * 支持上传图片大小上限：5M
     */
    const IMAGE_SIZE_LIMIT = 5 * 1024 * 1024;

    /**
     * 允许下载图片上限：2M
     */
    const DOWNLOAD_IMAGE_MAX_SIZE = 2 * 1024 * 1024;

    /**
     * 允许下载文件上限：20M
     */
    const DOWNLOAD_FILE_MAX_SIZE = 20 * 1024 * 1024;

    /**
     * 分页:默认页数
     */
    const PAGING_DEFAULT_PAGE = 1;

    /**
     * 分页:默认当前页数据总数
     */
    const PAGING_DEFAULT_LIMIT = 20;

    /**
     * 文件类型:文件夹
     */
    const FILE_TYPE_IS_FOLDER = 1;

    /**
     * 文件类型:文件
     */
    const FILE_TYPE_IS_DOC = 2;

    /**
     * 权限是否为全公司：否
     */
    const RIGHT_IS_ALL_FALSE = 1;

    /**
     * 权限是否为全公司：是
     */
    const RIGHT_IS_ALL_TRUE = 2;

    /**
     * 权限对象类型:全公司
     */
    const RIGHT_TYPE_ALL = 1;

    /**
     * 权限对象类型:部门
     */
    const RIGHT_TYPE_DEPARTMENT = 2;

    /**
     * 权限对象类型:标签
     */
    const RIGHT_TYPE_TAG = 3;

    /**
     * 权限对象类型:人员
     */
    const RIGHT_TYPE_USER = 4;

    /**
     * 权限对象类型:职位
     */
    const RIGHT_TYPE_JOB = 5;

    /**
     * 权限对象类型:角色
     */
    const RIGHT_TYPE_ROLE = 6;

    /**
     * 权限类型:查阅权限
     */
    const RIGHT_TYPE_IS_READ = 1;

    /**
     * 权限类型:下载权限
     */
    const RIGHT_TYPE_IS_DOWNLOAD = 2;

    /**
     * 是否启用下载权限:不启用
     */
    const FILE_DOWNLOAD_RIGHT_OFF = 1;

    /**
     * 是否启用下载权限:启用
     */
    const FILE_DOWNLOAD_RIGHT_ON = 2;

    /**
     * 文件显示状态:隐藏
     */
    const FILE_STATUS_IS_HIDE = 1;

    /**
     * 文件显示状态:显示
     */
    const FILE_STATUS_IS_SHOW = 2;

    /**
     * 文件状态:转码中
     */
    const FILE_STATUS_CONVERT = 1;

    /**
     * 文件状态:转码完成
     */
    const FILE_STATUS_NORMAL = 2;

    /**
     * 我是否可下载:不可下载
     */
    const FILE_MY_DOWNLOAD_RIGHT_NO = 1;

    /**
     * 我是否可下载:可下载
     */
    const FILE_MY_DOWNLOAD_RIGHT_YES = 2;

    /**
     * 收藏:未收藏
     */
    const FILE_MY_FAVORITE_NO = 1;

    /**
     * 收藏:已收藏
     */
    const FILE_MY_FAVORITE_YES = 2;

    /**
     * 收藏操作类型:收藏
     */
    const FAVORITE_TYPE_ADD = 2;

    /**
     * 收藏操作类型:取消收藏
     */
    const FAVORITE_TYPE_DELETE = 1;

    /**
     * 目录是否包含子文件夹：否
     */
    const IS_CHILD_FOLDER_FALSE = 1;

    /**
     * 目录是否包含子文件夹：是
     */
    const IS_CHILD_FOLDER_TRUE = 2;

    /**
     * 文件分片是否组合:未组合
     */
    const FILE_PART_COMPLETE_FALSE = 1;

    /**
     * 文件分片是否组合:已组合
     */
    const FILE_PART_COMPLETE_TRUE = 2;
}
