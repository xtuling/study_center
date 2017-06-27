<?php
/**
 * Created by IntelliJ IDEA.
 * 微信端取消奖品兑换
 * User: zs_anything
 * Date: 2016/12/09
 * Time: 上午11:27
 */

namespace Api\Controller\Mall;

use Common\Service\ConvertService;

class PrizeConvertCanelController extends AbstractController
{

    public function Index()
    {

        $reqParams = I('post.');

        // 取消理由
        if(empty($reqParams['mark'])) {
            $this->_set_error('_ERR_CANEL_MARK_NULL_ERROR');
            return false;
        }

        // 取消理由大于60
        if(mb_strlen($reqParams['mark']) > 60) {
            $this->_set_error('_ERR_CANEL_MARK_LENGTH_NULL_ERROR');
            return false;
        }

        // 兑换ID为空
        if(empty($reqParams['ic_id'])) {
            $this->_set_error('_ERR_ICID_NULL_ERROR');
            return false;
        }

        $loginUserInfo = $this->_login->user;
        $reqParams['memUid'] = $loginUserInfo['memUid'];
        $reqParams['memUsername'] = $loginUserInfo['memUsername'];

        $convertService = new ConvertService();
        $convertService->prizeConvertCanel($reqParams);

        return true;
    }



}
