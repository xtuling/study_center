<?php
/**
 * ResolveController.class.php
 *【考试中心-手机端】查看答案解析接口
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Answer;

use Common\Service\AnswerDetailService;
use Common\Service\AnswerService;
use Common\Service\PaperService;

class ResolveController extends AbstractController
{
    /**
     * @var  AnswerDetailService
     */
    protected $answer_detail_serv;
    /**
     * @var AnswerService
     */
    protected $answer_serv;
    /**
     * @var PaperService
     */
    protected  $paper_serv;

    /**
     * @param string $action
     * @return bool
     */
    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化答卷详情service
        $this->answer_detail_serv = new AnswerDetailService();

        $this->answer_serv = new AnswerService();
        $this->paper_serv = new PaperService();

        return true;
    }

    public function Index_post()
    {
        /****
         * 首先判断答卷ID不能为空
         * 其次根据 答卷ID获取试题
         */
        $params = I('post.');
        // 默认值
        $page = !empty($params['page']) ? intval($params['page']) : self::DEFAULT_PAGE;
        $limit = !empty($params['limit']) ? intval($params['limit']) : self::DEFAULT_LIMIT_ONE;
        $ea_id = intval($params['ea_id']);
        $type = intval($params['type']);
        /**
         * 判断答卷ID不能为空
         */
        if (!$ea_id) {
            E('_EMPTY_EA_ID');
            return false;
        }
        // 判断试卷是否交卷
        $datalist = $this->answer_serv->get_by_conds(array('ea_id' => $ea_id));
        if (empty($datalist)) {
            E('_EMPTY_PAPER_DATA');
            return false;
        }
        // 分页
        list($start, $limit) = page_limit($page, $limit);
        //  按照题号排序
        $order_option = array('order_num' => 'asc');
        $conds = array('ea_id' => $ea_id);
        // 判断错题解析
        if ($type == AnswerService::MY_PASS) {
            $conds['is_pass!=?'] = AnswerService::MY_PASS;
        }
        // 获取记录总数
        $total = $this->answer_detail_serv->count_by_conds($conds);
        // 获取列表数据
        $list = array();
        if ($total > 0) {
            $list = $this->answer_detail_serv->list_by_conds($conds, array($start, $limit), $order_option, '*');
        }
        // 获取试卷详情
        $paper = $this->paper_serv->get($datalist['ep_id']);
        $this->_result = array(
            'total' => intval($total),
            'limit' => intval($limit),
            'ep_name' => $paper['ep_name'],
            'ep_id' => $paper['ep_id'],
            'ea_id' => $datalist['ea_id'],
            'page' => intval($page),
            // 格式化试题解析信息
            'list' => $this->answer_detail_serv->question_list_param($list, 2)
        );

        return true;
    }
}
