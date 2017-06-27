<?php
/**
 * 保存试卷规则
 * RuleSaveController.class.php
 * User: daijun
 * Date: 2017-05-23
 */

namespace Apicp\Controller\Paper;

use Common\Service\PaperService;

class RuleSaveController extends AbstractController
{
    /**
     * @var  PaperService 试卷信息表
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
         * 1.验证数据
         * 2.判断试卷分类是否变化，如果变化，则需删除该试卷权限，将该分类的权限存入试卷权限表
         * 3.更新试卷规则至试卷信息表，
         * 4.删除paper_temp的题目信息，
         * 5.按规则抽取相应试题存入paper_temp表
         */
        $param = I('post.');

        // 验证数据并编辑
        if (!$ep_id = $this->paper_serv->rule_save($param)) {
            return false;
        }

        return true;
    }

}
