<?php
/**
 * 类方法列表页
 */
namespace Frontend\Controller\Index;

use Common\Service\MethodService;

class ListMethodController extends AbstractController
{

    /**
     * 列出类方法
     * @return bool
     */
    public function Index()
    {

        $file = I('get.file');
        if (empty($file)) {
            die('缺少参数: file');
        }

        // 通过原来的类文件生成新的类文件
        $methodService = new MethodService();
        $fileData = $methodService->makeFile($file);
        // 包含文件
        $fileMethods = @include_once($fileData['fileName']);
        // 通过类名获取方法数据
        $methods = $methodService->getMethodData($fileData['className'], $file, $fileMethods);

        $this->assign('file', $file);
        $this->assign('methods', $methods);

        $this->_output('Index/ListMethod');
        return true;
    }

}
