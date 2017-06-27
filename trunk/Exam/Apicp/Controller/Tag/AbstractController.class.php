<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Apicp\Controller\Tag;

use Common\Service\TagService;

abstract class AbstractController extends \Apicp\Controller\AbstractController
{
    /**
     * 初始化标签表
     * @var TagService
     */
    protected $tag_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->tag_serv = new TagService();

        return true;
    }
}
