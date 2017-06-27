<?php
/**
 * 试卷备选题目重新抽取接口
 * ExtractController.class.php
 * User: daijun
 * Date: 2017-05-23
 */

namespace Apicp\Controller\Paper;

use Common\Service\PaperService;

class ExtractController extends AbstractController
{
    /**
     * @var PaperService 试卷信息表
     */
    protected $paper_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化试卷信息表
        $this->paper_serv = new PaperService();

        return true;
    }

    public function Index_post()
    {
        /**
         * 1.删除已选题目，即snapshot表中题目
         * 2.删除paper_temp表中信息
         * 3.根据试卷ID查询抽题规则
         * 4.根据抽题规则重新抽题存入paper_temp，如果数量不够，则抛错
         */
        $ep_id = I('post.ep_id', 0, 'intval');

        // 验证参数
        if (empty($ep_id)) {
            E('_EMPTY_PAPER_ID');
            return false;
        }

        if (!$this->paper_serv->extract_topic($ep_id)) {
            E('_ERR_UPDATE');
            return false;
        }

        return true;

    }

}
