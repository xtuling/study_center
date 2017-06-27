<?php
/**
 * 主页
 */
namespace Frontend\Controller\Index;

class IndexController extends AbstractController
{

    /**
     * Apidoc 主页, 基础教程
     * @desc Apidoc 主页
     * @return bool
     */
    public function Index()
    {

        $this->_output('Index/Index');
        return true;
    }

}
