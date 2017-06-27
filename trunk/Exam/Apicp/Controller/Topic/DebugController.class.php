<?php
/**
 * 线上调试
 */

namespace Apicp\Controller\Topic;

use Common\Common\Integral;
use Common\Common\User;
use Common\Service\RightService;
use Common\Model\MedalModel;

class DebugController extends AbstractController
{
    // TODO 用完了删除掉

    public function Index()
    {

        return true;
    }

    /** 获取勋章列表 */
    public function Medal()
    {
        $integral = new Integral();

        $list = $integral->listMedal();

        $this->_result = $list;

        return true;
    }

    /** 增加积分 */
    public function Integral()
    {
        $integralUtil = &Integral::instance();
        $integralUtil->asynUpdateIntegral(array(
            'memUid' => '77A9EDD47F00000137CF56C73C35B8A4',
            'miType' => 'mi_type0',
            'irKey' => 'dt_exam_encourage',
            'remark' =>'考试中心-'.'鲜彤测试',
            'integral' => 1,
            'msgIdentifier' => APP_IDENTIFIER
        ));

        return true;
    }

    /** right测试 */
    public function Right()
    {

        $uid = I('post.uid');
//        $uid = '77A9EDD47F00000137CF56C73C35B8A4';

        // 获取用户信息
        $userServ = &User::instance();
        $user = $userServ->getByUid($uid);

        // 获取用户权限
        $rightServ = new RightService();
        $right = $rightServ->get_by_right($user);

        // 实例化勋章model 测试写法请勿模仿
        $medal_model = new MedalModel();

        $data['right'] = $right;
        $data['er_type'] = 2;
        $medal = $medal_model->fetch_all_medal($data);

        $this->_result = [
            'total'=>count($medal),
            'list'=>$medal,
            'right'=>$right,
        ];

        return true;
    }

}
