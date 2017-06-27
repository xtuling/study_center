<?php
/**
 * 获取试卷试题信息（详情用）
 * QuestionListController.class.php
 * User: daijun
 * Date: 2017-05-23
 */

namespace Apicp\Controller\Paper;

use Common\Service\PaperService;
use Common\Service\SnapshotService;

class QuestionListController extends AbstractController
{
    /**
     * @var  PaperService 试卷信息表
     */
    protected $paper_serv;

    /**
     * @var SnapshotService 试卷题目快照信息表
     */
    protected $snapshot_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化试卷信息表
        $this->paper_serv = new PaperService();

        // 实例化试卷题目快照信息表
        $this->snapshot_serv = new SnapshotService();

        return true;
    }

    public function Index_post()
    {
        /**
         * 1.查询试卷详情
         * 2.根据试卷ID去试卷试题快照表（snapshot）查询试题列表(默认不分页)
         * 3.格式化试题列表数据
         */

        $ep_id = I('post.ep_id', 0, 'intval');

        // 验证参数
        if (empty($ep_id)) {
            E('_EMPTY_PAPER_ID');
            return false;
        }

        // 获取试卷详情
        $data = $this->paper_serv->get($ep_id);

        // 查询试题总数
        $total = $this->snapshot_serv->count_by_conds(array('ep_id' => $ep_id));

        // 默认值
        $page = !empty($params['page']) ? intval($params['page']) : 1;
        $limit = !empty($params['limit']) ? intval($params['limit']) : $total;

        // 分页
        list($start, $limit) = page_limit($page, $limit);

        // 排序
        $order_option = array('order_num' => 'ASC');

        // 获取试题列表
        $list = $this->snapshot_serv->list_by_conds(array('ep_id' => $ep_id), array($start, $limit), $order_option);

        // 组装返回数据
        $this->_result = array(
            'total' => intval($total),
            'limit' => intval($limit),
            'page' => intval($page),
            'ep_name' => $data['ep_name'],
            'intro' => $data['intro'],
            'ep_type' => intval($data['ep_type']),
            'list' => $this->snapshot_serv->format_admin_list($list)
        );

        return true;

    }

}
