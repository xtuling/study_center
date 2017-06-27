<?php
/**
 * 类列表页
 */
namespace Frontend\Controller\Index;

use Common\Common\FileSystem;
use Common\Service\MethodService;

class ListClassController extends AbstractController
{

    /**
     * 类列表
     * @desc 类文件列表页
     * @return bool
     */
    public function Index()
    {

        $dir = I('get.dir');
        $files = FileSystem::instance()->scanDir($dir);
        $methodService = new MethodService();
        $methods = $methodService->listMethodByDir($dir);

        // 输出到模板
        $this->assign('files', $files);
        $this->assign('methods', $methods);
        $this->assign('dir', $dir);

        $this->_output('Index/ListClass');
        return true;
    }

}
