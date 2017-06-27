<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/5/26
 * Time: 20:39
 */
namespace Common\Service;

use Common\Common\Constant;
use Common\Model\ChunkModel;

class ChunkService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new ChunkModel();
    }

    /**
     * 删除本地文件分片
     * @author liyifei
     * @param array $fileKeys 文件唯一标识
     * @return bool
     */
    public function deleteFile($fileKeys)
    {
        // 打开文件目录，组合文件分片
        $openDir = @opendir(Constant::PART_FILE_DIR);
        if ($openDir === false) {
            E('_ERR_PLUPLOAD_OPEN_CATALOG');
        }

        while (false !== $file = readdir($openDir)) {
            foreach ($fileKeys as $fileKey) {
                $isPart = strpos($file, $fileKey);
                if ($isPart !== false) {
                    $filePath = Constant::PART_FILE_DIR . D_S . $file;
                    unlink($filePath);
                }
            }
        }

        return true;
    }
}
