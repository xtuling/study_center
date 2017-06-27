<?php
/**
 * 附件上传
 * Created by PhpStorm.
 * User: xiantong
 * Date: 2017年04月08日
 */

namespace Apicp\Controller\Attachment;

use Common\Common\Uploader;
use VcySDK\Service;
use VcySDK\Attach;

header('Access-Control-Allow-Origin: http://localhost:3000'); //设置http://www.baidu.com允许跨域访问
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header

class UeditorUploadController extends AbstractController
{
    // 上传配置
    protected $conf;

    // 动作
    protected $action;

    public $_require_login = false;

    public function before_action($action = '')
    {

        if (! parent::before_action($action)) {
            return false;
        }

        // 获取配置
        $this->conf = $this->getConf();

        // 获取动作
        $this->action = I('get.action');

        return true;
    }

    public function Index()
    {

        $_FILES['file'] = $_FILES['upfile'];

        switch ($this->action) {
            case 'config':
                $result = json_encode($this->conf);
                break;

            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
//                $result = include("action_upload.php");
            $result = $this->uploadFile();
                break;

            /* 列出图片 */
            case 'listimage':
//                $result = include("action_list.php");
                break;
            /* 列出文件 */
            case 'listfile':
//                $result = include("action_list.php");
                break;

            /* 抓取远程文件 */
            case 'catchimage':
//                $result = include("action_crawler.php");
                break;

            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }

        exit();

    }

    protected function uploadFile(){
        /* 上传配置 */
        $base64 = "upload";
        // 默认上传类型为图片
        $uctag = 1;
        switch (htmlspecialchars($this->action)) {
            case 'uploadimage':
                $config = array(
                    "pathFormat" => $this->conf['imagePathFormat'],
                    "maxSize" => $this->conf['imageMaxSize'],
                    "allowFiles" => $this->conf['imageAllowFiles']
                );
                $fieldName = $this->conf['imageFieldName'];
                // 上传图片
                $uctag = 1;
                break;
            case 'uploadscrawl':
                $config = array(
                    "pathFormat" => $this->conf['scrawlPathFormat'],
                    "maxSize" => $this->conf['scrawlMaxSize'],
                    "allowFiles" => $this->conf['scrawlAllowFiles'],
                    "oriName" => "scrawl.png"
                );
                $fieldName = $this->conf['scrawlFieldName'];
                $base64 = "base64";
                // 上传涂鸦，图片
                $uctag = 1;
                break;
            case 'uploadvideo':
                $config = array(
                    "pathFormat" => $this->conf['videoPathFormat'],
                    "maxSize" => $this->conf['videoMaxSize'],
                    "allowFiles" => $this->conf['videoAllowFiles']
                );
                $fieldName = $this->conf['videoFieldName'];
                // 上传视频
                $uctag = 3;
                break;
            case 'uploadfile':
            default:
                $config = array(
                    "pathFormat" => $this->conf['filePathFormat'],
                    "maxSize" => $this->conf['fileMaxSize'],
                    "allowFiles" => $this->conf['fileAllowFiles']
                );
                $fieldName = $this->conf['fileFieldName'];
                // 上传其他
                $uctag = 99;

                $arr = explode("/",$_FILES['file']['type']);

                if(in_array("image", $arr)){
                    // 图片类型
                    $uctag = 1;
                }

                if(in_array("audio", $arr)){
                    // 音频类型
                    $uctag = 2;
                }

                if(in_array("video", $arr)){
                    // 视频类型
                    $uctag = 3;
                }
                
                break;
        }

        /* 生成上传实例对象并完成上传 */
        $up = new Uploader($fieldName, $config, $base64, $uctag);

        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
         *     "url" => "",            //返回的地址
         *     "title" => "",          //新文件名
         *     "original" => "",       //原始文件名
         *     "type" => ""            //文件类型
         *     "size" => "",           //文件大小
         * )
         */

        /* 返回数据 */
        return json_encode($up->getFileInfo());

    }

    protected function getConf(){
        $json = '
            /* 前后端通信相关的配置,注释只允许使用多行方式 */
            {
                /* 上传图片配置项 */
                "imageActionName": "uploadimage", /* 执行上传图片的action名称 */
                "imageFieldName": "upfile", /* 提交的图片表单名称 */
                "imageMaxSize": 2048000, /* 上传大小限制，单位B */
                "imageAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 上传图片格式显示 */
                "imageCompressEnable": true, /* 是否压缩图片,默认是true */
                "imageCompressBorder": 1600, /* 图片压缩最长边限制 */
                "imageInsertAlign": "none", /* 插入的图片浮动方式 */
                "imageUrlPrefix": "", /* 图片访问路径前缀 */
                "imagePathFormat": "/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
                                            /* {filename} 会替换成原文件名,配置这项需要注意中文乱码问题 */
                                            /* {rand:6} 会替换成随机数,后面的数字是随机数的位数 */
                                            /* {time} 会替换成时间戳 */
                                            /* {yyyy} 会替换成四位年份 */
                                            /* {yy} 会替换成两位年份 */
                                            /* {mm} 会替换成两位月份 */
                                            /* {dd} 会替换成两位日期 */
                                            /* {hh} 会替换成两位小时 */
                                            /* {ii} 会替换成两位分钟 */
                                            /* {ss} 会替换成两位秒 */
                                            /* 非法字符 \ : * ? " < > | */
                                            /* 具请体看线上文档: fex.baidu.com/ueditor/#use-format_upload_filename */

                /* 涂鸦图片上传配置项 */
                "scrawlActionName": "uploadscrawl", /* 执行上传涂鸦的action名称 */
                "scrawlFieldName": "upfile", /* 提交的图片表单名称 */
                "scrawlPathFormat": "/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
                "scrawlMaxSize": 2048000, /* 上传大小限制，单位B */
                "scrawlUrlPrefix": "", /* 图片访问路径前缀 */
                "scrawlInsertAlign": "none",

                /* 截图工具上传 */
                "snapscreenActionName": "uploadimage", /* 执行上传截图的action名称 */
                "snapscreenPathFormat": "/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
                "snapscreenUrlPrefix": "", /* 图片访问路径前缀 */
                "snapscreenInsertAlign": "none", /* 插入的图片浮动方式 */

                /* 抓取远程图片配置 */
                "catcherLocalDomain": ["127.0.0.1", "localhost"],
                "catcherActionName": "catchimage", /* 执行抓取远程图片的action名称 */
                "catcherFieldName": "source", /* 提交的图片列表表单名称 */
                "catcherPathFormat": "/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
                "catcherUrlPrefix": "", /* 图片访问路径前缀 */
                "catcherMaxSize": 2048000, /* 上传大小限制，单位B */
                "catcherAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 抓取图片格式显示 */

                /* 上传视频配置 */
                "videoActionName": "uploadvideo", /* 执行上传视频的action名称 */
                "videoFieldName": "upfile", /* 提交的视频表单名称 */
                "videoPathFormat": "/ueditor/php/upload/video/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
                "videoUrlPrefix": "", /* 视频访问路径前缀 */
                "videoMaxSize": 102400000, /* 上传大小限制，单位B，默认100MB */
                "videoAllowFiles": [
                ".mp3", ".amr", ".m4a", ".avi", ".mp4"], /* 上传视频格式显示 */

                /* 上传文件配置 */
                "fileActionName": "uploadfile", /* controller里,执行上传视频的action名称 */
                "fileFieldName": "upfile", /* 提交的文件表单名称 */
                "filePathFormat": "/ueditor/php/upload/file/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
                "fileUrlPrefix": "", /* 文件访问路径前缀 */
                "fileMaxSize": 51200000, /* 上传大小限制，单位B，默认50MB */

                "fileAllowFiles": [
                ".xls", ".xlsx", ".doc", ".docx", ".pptx",
                ".ppt", ".rar", ".zip", ".pdf", ".txt"
            ], /* 上传文件格式显示 */

                /* 列出指定目录下的图片 */
                "imageManagerActionName": "listimage", /* 执行图片管理的action名称 */
                "imageManagerListPath": "/ueditor/php/upload/image/", /* 指定要列出图片的目录 */
                "imageManagerListSize": 20, /* 每次列出文件数量 */
                "imageManagerUrlPrefix": "", /* 图片访问路径前缀 */
                "imageManagerInsertAlign": "none", /* 插入的图片浮动方式 */
                "imageManagerAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 列出的文件类型 */

                /* 列出指定目录下的文件 */
                "fileManagerActionName": "listfile", /* 执行文件管理的action名称 */
                "fileManagerListPath": "/ueditor/php/upload/file/", /* 指定要列出文件的目录 */
                "fileManagerUrlPrefix": "", /* 文件访问路径前缀 */
                "fileManagerListSize": 20, /* 每次列出文件数量 */
                "fileManagerAllowFiles": [
                ".xls", ".xlsx", ".doc", ".docx", ".pptx",
                ".ppt", ".rar", ".zip", ".pdf", ".txt",
                ".mp3", ".amr", ".m4a", ".avi", ".mp4",
                ".png", ".jpg", ".jpeg", ".gif", ".bmp"
            ] /* 列出的文件类型 */

            }';
        return json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", $json), true);
    }
}

