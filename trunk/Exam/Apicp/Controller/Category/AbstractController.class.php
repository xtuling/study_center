<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Apicp\Controller\Category;

use Common\Service\CategoryService;

abstract class AbstractController extends \Apicp\Controller\AbstractController
{
    /**
     * 初始化试卷分类表
     * @var CategoryService
     */
    protected $cate_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->cate_serv = new CategoryService();

        return true;
    }
}
