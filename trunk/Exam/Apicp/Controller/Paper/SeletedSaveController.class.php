<?php
/**
 * 保存试卷选择的题目
 * SeletedSaveController.class.php
 * User: daijun
 * Date: 2017-05-23
 */

namespace Apicp\Controller\Paper;

use Common\Service\SnapshotService;

class SeletedSaveController extends AbstractController
{
    /**
     * @var SnapshotService  试卷试题快照信息表
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
         * 1.验证数据
         * 2.根据试题ID集合获取试题列表
         * 3.将试题信息插入试题快照信息表
         * 4.更新试卷表的总分
         */

        $param = I('post.');

        if (!$this->snapshot_serv->add($param)) {
            E('_ERR_UPDATE');
            return false;
        }

        return true;
    }

}
