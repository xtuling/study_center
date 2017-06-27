<?php
/**
 *【考试中心-手机端】模拟详情记录接口
 * TestRecordController.class.php
 * @author: 蔡建华
 * @date :  2017-05-8
 * @version $Id$
 */

namespace Api\Controller\Paper;

use Common\Service\AnswerService;

class TestRecordController extends AbstractController
{
    /**
     * @var  AnswerService
     */
    protected $answer_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化答卷ervice
        $this->answer_serv = new AnswerService();
        return true;
    }

    /**
     *  模拟详情记录
     * @paper_id    是    Int    考试分类ID
     * @page    否    Int   页面
     * @limit    否    Int   条数
     * @return bool
     */
    public function Index_post()
    {
        /*
        * 根据试题ID和用户ID 判断用户是否答卷
        * 再次判断试卷类型为模拟试题否则抛出答卷类型不为模拟试卷
        * 对答卷记录按照最高分数进行排名，如果多次考试分数相同按照最早的排名
        */

        $params = I('post.');
        // 获取模拟记录
        $data = $this->answer_serv->test_record_list($params, $this->uid, 1);
        if (!$data) {
            return false;
        }
        $this->_result = $data;
        return true;
    }
}
