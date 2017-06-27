<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017-5-18
 * Time: 14:07:02
 */
namespace Apicp\Controller\Doc;

use Think\Log;
use Think\Exception;
use VcySDK\Service;
use VcySDK\Attach;
use VcySDK\FileConvert;
use Common\Common\Constant;
use Common\Service\FileService;
use Common\Service\TaskService;
use Common\Service\ChunkService;

class UploadController extends \Apicp\Controller\AbstractController
{
    /**
     * Upload
     * @author liyifei
     * @desc 文件上传接口
     * @param String parent_id:true:0 父级文件夹ID（根目录时为0）
     * @return bool
     */
    public function Index_post()
    {
        // $_POST必须包含的参数，依次表示：真实文件名、文件分片偏移量、文件分片总数、插件生成的唯一文件ID、文件总大小（字节）
        $postParam = ['name', 'chunk', 'chunks', 'file_id', 'file_size'];
        if (array_diff($postParam, array_keys($_POST))) {
            E('_ERR_PLUPLOAD_POST_PARAM_LOSE');
        }

        // $_FILES必须包含的参数，依次表示：临时文件名、文件大小（字节）
        $fileParam = ['tmp_name', 'size'];
        if (!isset($_FILES['file']) || array_diff($fileParam, array_keys($_FILES['file']))) {
            E('_ERR_PLUPLOAD_FILES_PARAM_LOSE');
        }

        // 父级目录
        $parentId = I('post.parent_id', 0, 'intval');
        $fileServ = new FileService();
        if ($parentId > 0) {
            $folder = $fileServ->get_by_conds([
                'file_id' => $parentId,
                'file_type' => Constant::FILE_TYPE_IS_FOLDER,
            ]);
            if (empty($folder)) {
                E('_ERR_FILE_PARENT_IS_NULL');
            }
        }

        // 临时目录保存文件分片
        $this->_saveFile();

        // chunk表保存文件分片信息
        $chunkServ = new ChunkService();
        $chunkData = [
            'parent_id' => $parentId,
            'file_key' => $_POST['file_id'],
            'file_name' => $_POST['name'],
            'file_size' => $_FILES['file']['size'],
            'chunk' => $_POST['chunk'],
            'chunk_total' => $_POST['chunks'],
        ];
        $chunkServ->insert($chunkData);

        // 最后文件分片上传成功后，将文件分片组合成一个文件（分片总数=分片偏移量+1）
        if ($_POST['chunks'] == $_POST['chunk'] + 1) {
            // 文件唯一名称检查、修改
            $fileServ->checkFileName($chunkData['file_name'], $parentId, Constant::FILE_TYPE_IS_DOC);

            // 组合分片成完整文件
            $fullFilePath = $this->_completeFile($chunkData);

            // 请求UC，上传文件
            $suffix = $this->_getFileSuffix($chunkData['file_name']);
            $fileType = $this->_getFileType($suffix);
            $checkRightParam = getResAuthConfig('doc');
            $uploadParam = array_merge(['atMediatype' => $fileType], $checkRightParam);
            $fileData = [
                'file' => [
                    'name' => $_POST['name'],
                    'type' => mime_content_type($fullFilePath),
                    'size' => filesize($fullFilePath),
                    'tmp_name' => $fullFilePath,
                ],
            ];
            try {
                $attachServ = new Attach(Service::instance());
                $data = $attachServ->upload($uploadParam, $fileData);
                unlink($fullFilePath);

            } catch (Exception $e) {
                unlink($fullFilePath);
                Log::record($e->getMessage() . ':' . $e->getCode());
            }
            if (!isset($data['atId'])) {
                E('_ERR_PLUPLOAD_UC_SAVE_DATA_FAIL');
            }

            // 请求UC，文件转码
            $convertServ = new FileConvert(Service::instance());
            $convertServ->convert([
                'atIds' => [$data['atId']],
                'convertType' => FileConvert::CONVERT_TYPE_HTML,
                'high' => FileConvert::CONVERT_IS_HIGH_TRUE,
            ]);

            // 文件转码状态（图片文件无需转码）
            $fileStatus = (in_array(strtoupper($suffix), Constant::ALLOW_UPLOAD_FILE_TYPE)) ? Constant::FILE_STATUS_CONVERT : Constant::FILE_STATUS_NORMAL;

            // file表保存文件信息
            $fileId = $fileServ->insert([
                'parent_id' => $parentId,
                'file_name' => $chunkData['file_name'],
                'file_type' => Constant::FILE_TYPE_IS_DOC,
                'at_id' => $data['atId'],
                'at_size' => $data['atFilesize'],
                'at_url' => $data['atAttachment'],
                'update_time' => MILLI_TIME,
                'file_status' => $fileStatus,
            ]);

            // 修改chunk表（写入file_id）
            if ($fileId) {
                $chunkServ->update_by_conds(['file_key' => $chunkData['file_key']], ['file_id' => $fileId]);
            }

            // 请求UC，创建查询文件转码结果的计划任务
            if ($fileId && $fileStatus == Constant::FILE_STATUS_CONVERT) {
                $taskServ = new TaskService();
                $taskServ->createTask($fileId);
            }
        }
    }

    /**
     * 创建临时目录、保存文件分片
     * @author liyifei
     * @return string
     */
    private function _saveFile()
    {
        // 上传文件有错误
        if (isset($_FILES['file']['error']) && $_FILES['file']['error'] > 0) {
            E('_ERR_PLUPLOAD_FILE_ERROR');
        }

        // 文件类型验证
        $suffix = $this->_getFileSuffix($_POST['name']);
        $suffixUpper = strtoupper($suffix);
        $checkTypeRes = $this->_checkFileType($suffixUpper);
        if (!$checkTypeRes) {
            E('_ERR_PLUPLOAD_FILE_TYPE_NOTALLOW');
        }

        // 文件大小验证
        $this->_checkFileSize($suffixUpper, $_POST['file_size']);

        // 创建本地临时目录
        if (!file_exists(Constant::PART_FILE_DIR)) {
            @mkdir(Constant::PART_FILE_DIR, 0777, true);
        }

        // 分片文件名：文件ID + 分片偏移量 + 后缀
        $fileName = $_POST['file_id'] . '(' . $_POST['chunk'] . ').' . $suffix;

        // 文件保存路径
        $filePath = Constant::PART_FILE_DIR . D_S . $fileName;

        // 将文件分片保存至目录
        $res = move_uploaded_file($_FILES['file']['tmp_name'], $filePath);
        if ($res === false) {
            E('_ERR_PLUPLOAD_SAVE_CATALOG_FAIL');
        }

        return $filePath;
    }

    /**
     * 组合文件分片
     * @author liyifei
     * @param array $data 分片详情
     *      + int parent_id 父目录ID
     *      + string file_key 文件唯一标识
     *      + string file_name 真实文件名称
     *      + int chunk 文件分片偏移量（从0开始）
     *      + int chunk_total 文件分片总数
     * @return string
     */
    private function _completeFile($data)
    {
        // 打开文件目录，组合文件分片
        $openDir = @opendir(Constant::PART_FILE_DIR);
        if ($openDir === false) {
            E('_ERR_PLUPLOAD_OPEN_CATALOG');
        }

        // 创建完整文件（登录人员ID为目录，真实文件名 + 文件后缀，防止文件名重复）
        $eaId = $this->_login->user['eaId'];
        if (!file_exists(Constant::PART_FILE_DIR . D_S . $eaId)) {
            @mkdir(Constant::PART_FILE_DIR . D_S . $eaId, 0777, true);
        }
        $fullFilePath = Constant::PART_FILE_DIR . D_S . $eaId . D_S . $data['file_name'];
        if (file_exists($fullFilePath)) {
            file_put_contents($fullFilePath, '');
        }

        // 循环读取目录下所有文件（非目录中文件顺序，按尾缀标号顺序拼接文件流）
        $allPart = [];
        $suffix = $this->_getFileSuffix($data['file_name']);
        for ($i = 0; $i < $data['chunk_total']; $i++) {
            // 读取文件分片数据
            $chunk = '(' . $i . ')';
            $filePath = Constant::PART_FILE_DIR . D_S . $data['file_key'] . $chunk . '.' . $suffix;
            $content = file_get_contents($filePath);

            // 将文件分片数据写入完整文件
            file_put_contents($fullFilePath, $content, FILE_APPEND);
            unset($content);

            // 汇总文件分片
            $allPart[] = $filePath;
        }

        // 组合的文件分片数与分片总数是否一致
        if (count($allPart) != $data['chunk_total']) {
            unlink($fullFilePath);
            E('_ERR_PLUPLOAD_FILE_PART_INCOMPLETE');
        }

        // 删除本地文件分片
        $chunkServ = new ChunkService();
        $chunkServ->deleteFile([$data['file_key']]);

        // 修改chunk表（更新is_complete）
        $chunkServ->update_by_conds(['file_key' => $data['file_key']], ['is_complete' => Constant::FILE_PART_COMPLETE_TRUE]);

        return $fullFilePath;
    }

    /**
     * 文件大小验证（普通文件200M，图片5M）
     * @param string $suffix 文件后缀（大写）
     * @param int $size 文件大小（字节）
     * @return bool
     */
    private function _checkFileSize($suffix, $size)
    {
        if (in_array($suffix, Constant::ALLOW_UPLOAD_FILE_TYPE) && $size > Constant::FILE_SIZE_LIMIT) {
            E('_ERR_PLUPLOAD_FILE_SIZE_FILE');
        }

        if (in_array($suffix, Constant::ALLOW_UPLOAD_IMAGE_TYPE) && $size > Constant::IMAGE_SIZE_LIMIT) {
            E('_ERR_PLUPLOAD_FILE_SIZE_IMAGE');
        }

        return true;
    }

    /**
     * 文件类型验证
     * @author liyifei
     * @param string $suffix 文件后缀（大写）
     * @return bool
     */
    private function _checkFileType($suffix)
    {
        // 目前仅支持普通文件、图片文件的上传！
        $fileTypes = array_merge(Constant::ALLOW_UPLOAD_FILE_TYPE, Constant::ALLOW_UPLOAD_IMAGE_TYPE);

        if (!in_array($suffix, $fileTypes)) {
            return false;
        }

        return true;
    }

    /**
     * 根据文件后缀，获取UC上传附件接口的文件类型参数
     * @author liyifei
     * @param string $suffix 文件后缀
     * @return int
     */
    private function _getFileType($suffix)
    {
        $suffix = strtoupper($suffix);

        // 99=普通文件(20M, 支持类型:xls,xlsx,doc,docx,pptx,ppt,rar, zip, pdf,txt)
        if (in_array($suffix, Constant::ALLOW_UPLOAD_FILE_TYPE)) {
            $fileType = Attach::TYPE_NORMAL;
        }

        // 1=图片(2M, 支持类型:jpg,jpeg,png,bmp,gif)
        if (in_array($suffix, Constant::ALLOW_UPLOAD_IMAGE_TYPE)) {
            $fileType = Attach::TYPE_IMG;
        }

        // 2=音频(50M, 支持类型:mp3,amr,m4a)
        // 3=视频(50M, 支持类型:avi,mp4)

        return isset($fileType) ? $fileType : 0;
    }

    /**
     * 获取文件后缀
     * @author liyifei
     * @param string $fileName 文件名
     * @return string
     */
    private function _getFileSuffix($fileName)
    {
        $suffix = '';
        $fileNames = explode('.', $fileName);
        if (count($fileNames) > 1) {
            $suffix = end($fileNames);
        }
        return $suffix;
    }
}
