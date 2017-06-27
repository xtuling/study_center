<?php
/**
 *  ListController.class.php
 * 【考试中心-手机端】考试排名列表接口
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Rank;

use Common\Service\AnswerService;

class ListController extends AbstractController
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

    public function Index_post()
    {
        /**
         *
         * 根据试卷ID进行最高成绩排序，如果成绩相同按照答卷先后顺序取值，并取答卷ID
         */
        $params = I('post.');
        $ep_id = intval($params['ep_id']);
        if (!$ep_id) {
            E('_EMPTY_EP_ID');
            return false;
        }
        $page = isset($params['page']) ? intval($params['page']) : self::DEFAULT_PAGE;
        $limit = isset($params['limit']) ? intval($params['limit']) : self::DEFAULT_LIMIT;
        // 分页
        list($start, $limit) = page_limit($page, $limit);
        // 查询排名记录
        $data = $this->answer_serv->answer_list_all($ep_id, $this->uid);
        if (!$data) {
            return false;
        }
        $total = count($data);
        if ($total) {
            $list = array_slice($data['list'], $start, $limit);
        } else {
            E('_ERR_DATA_NOT_EXIST');
            return false;
        }
        $this->_result = array(
            'total' => intval($total),
            'limit' => intval($limit),
            'page' => intval($page),
            'ranking' => $data['ranking'],
            'list' => $list
        );

        return true;
    }
}
