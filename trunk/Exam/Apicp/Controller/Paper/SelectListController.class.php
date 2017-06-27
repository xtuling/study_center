<?php
/**
 * 获取试卷已选择题目列表接口
 * SelectListController.class.php
 * User: daijun
 * Date: 2017-05-23
 */

namespace Apicp\Controller\Paper;

use Common\Service\SnapshotService;

class SelectListController extends AbstractController
{
    /**
     * @var  SnapshotService 试卷试题快照信息表
     */
    protected $snapshot_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }

        // 实例化试卷试题快照信息表
        $this->snapshot_serv = new SnapshotService();

        return true;
    }

    public function Index_post()
    {
        /**
         * 1.根据试卷ID去snapshot表查询题目列表
         * 2.格式化返回数据
         */
        $ep_id = I('post.ep_id', 0, 'intval');

        // 验证参数
        if (empty($ep_id)) {
            E('_EMPTY_PAPER_ID');
            return false;
        }

        // 获取已选题列表
        $list = $this->snapshot_serv->get_snapshot_list($ep_id);

        // 组装返回数据
        $this->_result = array(
            'total' => count($list),
            'list' => $list
        );

        return true;
    }

}
