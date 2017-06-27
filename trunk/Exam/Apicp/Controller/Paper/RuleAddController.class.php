<?php
/**
 * 新增试卷规则
 * RuleAddController.class.php
 * User: daijun
 * Date: 2017-05-23
 */

namespace Apicp\Controller\Paper;

use Common\Service\PaperService;
use Common\Service\RightService;

class RuleAddController extends AbstractController
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
         * 1.验证必填字段
         * 2.保存试卷规则至试卷表，试卷状态为初始化
         * 3.将分类的权限信息插入试卷权限信息表
         * 4.按规则抽取相应试题存入paper_temp表
         */

        $param = I('post.');

        // 验证数据并添加
        if (!$ep_id = $this->paper_serv->rule_add($param)) {
            return false;
        }
        // 格式化返回数据
        $this->_result = array('ep_id' => intval($ep_id));

        return true;

    }

}
