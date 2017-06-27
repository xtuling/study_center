<?php
/**
 * 主页
 */
namespace Api\Controller\ApiDoc;

use Common\Common\FileSystem;
use Common\Service\MethodService;
use Think\Exception;

class RefreshApiListController extends AbstractController
{

    /**
     * @return bool
     */
    public function Index()
    {

        $dir = I('get.dir');
        if (empty($dir)) {
            E('_ERR_DIR_IS_EMPTY');
            return true;
        }

        // 错误提示
        $errors = array();

        $methodService = new MethodService();
        // 清除缓存
        $methodService->clearMethodCache($dir);
        // 获取所有PHP文件列表
        $files = FileSystem::instance()->scanFile($dir, 'php');
        foreach ($files as $_file) {
            try {
                $fileData = $methodService->makeFile($_file);
                // 包含文件
                $fileMethods = @include_once($fileData['fileName']);
                // 通过类名获取方面数据
                $methodService->getMethodData($fileData['className'], $_file, $fileMethods);
            } catch (Exception $e) {
                // do nothing
                if (cfg('SYNTAX_ERROR_CODE') == $e->getCode()) {
                    $errors[] = $e->getCode() . ':' . $e->getMessage() . "({$_file})";
                }
            }
        }

        // 如果解析出错了
        if (!empty($errors)) {
            E("2000:\n" . implode("\n", $errors));
            return false;
        }

        return true;
    }

}
