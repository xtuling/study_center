<?php
/**
 * 获取试卷基本信息（详情用）
 * DetailController.class.php
 * User: daijun
 * Date: 2017-05-23
 */

namespace Apicp\Controller\Paper;

use Common\Service\AnswerService;
use Common\Service\PaperService;
use Common\Service\RightService;

class DetailController extends AbstractController
{

    /**
     * @var  PaperService 试卷信息表
     */
    protected $paper_serv;

    /**
     * @var RightService 权限信息表
     */
    protected $right_serv;

    /**
     * @var AnswerService 用户答题记录表
     */
    protected $answer_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化试卷信息表
        $this->paper_serv = new PaperService();

        // 实例化权限信息表
        $this->right_serv = new RightService();

        // 实例化用户答题信息表
        $this->answer_serv = new AnswerService();

        return true;
    }

    public function Index_post()
    {
        /**
         * 1.根据试卷ID获取试卷详情
         * 2.根据试卷详情的分类ID获取分类名称
         * 3.如果is_all为0，则去权限表查询权限数据
         * 4.查询已参与人数
         * 5.获取未参与人数
         */

        $ep_id = I('post.ep_id', 0, 'intval');

        // 验证参数
        if (empty($ep_id)) {
            E('_EMPTY_PAPER_ID');

            return false;
        }

        // 获取基本信息
        $data = $this->paper_serv->get_paper_detail_admin($ep_id);
        if (!$data) {
            E('_EMPTY_PAPER_DATA');

            return false;
        }

        // 给权限字段赋值初始值
        $right_data = array(
            'user_list' => array(),
            'dp_list' => array(),
            'tag_list' => array(),
            'job_list' => array(),
            'role_list' => array(),
        );

        // 用来查询应参与人列表数据
        $arr = array();
        $arr['is_all'] = PaperService::AUTH_ALL;

        //如果不是全公司，
        if ($data['is_all'] == PaperService::AUTH_NOT_ALL) {
            //此处查询权限数据
            list($right_list, $right_data) = $this->right_serv->get_right_data(array(
                'epc_id' => $ep_id,
                'er_type' => RightService::RIGHT_PAPER
            ));

            $arr['is_all'] = PaperService::AUTH_NOT_ALL;
            $arr['uids'] = array_filter(array_column($right_list, 'uid'));
            $arr['dp_ids'] = array_filter(array_column($right_list, 'cd_id'));
            $arr['tag_ids'] = array_filter(array_column($right_list, 'tag_id'));
            $arr['job_ids'] = array_filter(array_column($right_list, 'job_id'));
        }
        $data['right'] = $right_data;

        // 获取已参与与未参与人数
        $join_data=$this->answer_serv->get_unjoin_data(array('epc_id'=>$ep_id,'er_type'=>AnswerService::RIGHT_PAPER),$ep_id,$data['is_all']);

        $data['join_num'] = count($join_data['join_list']);
        $data['no_join_num'] = count($join_data['unjoin_list']);;

        // 返回数据
        $this->_result = $data;

        return true;
    }

}
