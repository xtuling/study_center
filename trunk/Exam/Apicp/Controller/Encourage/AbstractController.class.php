<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Apicp\Controller\Encourage;

use Common\Service\MedalService;

abstract class AbstractController extends \Apicp\Controller\AbstractController
{
    /**
     * 初始化激励表
     * @var MedalService
     */
    protected $medal_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->medal_serv = new MedalService();

        return true;
    }
}
