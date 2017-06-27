<?php
/**
 * 编辑试卷基本信息
 * BaseSaveController.class.php
 * User: daijun
 * Date: 2017-05-23
 */

namespace Apicp\Controller\Paper;

use Common\Service\PaperService;

class BaseSaveController extends AbstractController
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
         * 1.校验数据
         * 2.保存试卷信息
         * 3.删除之前的权限信息
         * 4.如果is_all为0，把权限信息存入权限表
         */
        $param = I('post.');

        // 验证数据并保存
        if (!$this->paper_serv->base_save($param)) {

            return false;
        }

        return true;
    }

}
