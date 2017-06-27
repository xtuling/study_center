<?php
/**
 * 获取试卷基本设置（编辑用）
 * BaseDetailController.class.php
 * User: daijun
 * Date: 2017-05-23
 */

namespace Apicp\Controller\Paper;

use Common\Service\PaperService;
use Common\Service\RightService;

class BaseDetailController extends AbstractController
{

    /**
     * @var  PaperService 试卷信息表
     */
    protected $paper_serv;

    /**
     * @var RightService 权限信息表
     */
    protected $right_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化试卷信息表
        $this->paper_serv = new PaperService();

        // 实例化权限信息表
        $this->right_serv = new RightService();

        return true;
    }

    public function Index_post()
    {
        /**
         * 1.用试卷ID获取试卷详情
         * 2.如果is_all是0，则去权限表获取权限数据
         * 3.格式化返回数据
         */

        $ep_id = I('post.ep_id', 0, 'intval');

        // 验证参数
        if (empty($ep_id)) {
            E('_EMPTY_PAPER_ID');

            return false;
        }

        // 获取基本信息
        $result = $this->paper_serv->get_paper_base_detail($ep_id);
        if (!$result) {
            E('_EMPTY_PAPER_DATA');

            return false;
        }

        // 给权限字段赋值初始值
        $right_data = array(
            'user_list' => array(),
            'dp_list' => array(),
            'tag_list' => array(),
            'job_list' => array(),
        );

        //如果不是全公司，
        if ($result['is_all'] == PaperService::AUTH_NOT_ALL) {
            //此处查询权限数据
            list($right_list, $right_data) = $this->right_serv->get_right_data(array(
                'epc_id' => $ep_id,
                'er_type' => RightService::RIGHT_PAPER
            ));
        }
        $result['right'] = $right_data;

        //组装返回数据
        $this->_result = $result;

        return true;
    }

}
