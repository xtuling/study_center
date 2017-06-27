<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Apicp\Controller\Topic;

use Common\Service\TopicService;

abstract class AbstractController extends \Apicp\Controller\AbstractController
{
    /**
     * @var  TopicService  实例化题库题目表对象
     */
    protected $topic_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->topic_serv = new TopicService();

        return true;
    }
}
