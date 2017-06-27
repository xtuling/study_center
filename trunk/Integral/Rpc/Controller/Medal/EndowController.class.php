<?php
/**
 * Created by PhpStorm.
 * 赋予勋章
 * User: zhoutao
 * Reader: zhoutao 2017-05-31 10:25:59
 * Date: 2017-05-24 19:33:56
 */

/**
 * 使用须知
 *
 * 调用方法:
 *  $rpcUrl = call_user_func_array('sprintf', [
 *      '%s://%s/%s/Integral/Rpc/Medal/Endow',
 *      $_SERVER['REQUEST_SCHEME'],
 *      $_SERVER['HTTP_HOST'],
 *      QY_DOMAIN
 *  ]);
 *
 *  $postData = [
 *      1, // im_id 勋章ID
 *      '9301F6AF7F0000010AF9CD33DD051EB3', // 人员ID
 *      '人员姓名' // 人员姓名
 *  ];
 *  \Com\Rpc::phprpc($rpcUrl)->invoke('Index', $postData)
 *
 * 返回值预览:
 *  true / false
 */

namespace Rpc\Controller\Medal;

use Common\Model\MedalLogModel;
use Common\Service\MedalLogService;
use Common\Service\MedalService;
use Common\Service\MemberMedalService;

class EndowController extends AbstractController
{
    /**
     * @param int $imId 勋章ID
     * @param string $uid 人员ID
     * @param string $userName 人员姓名
     * @return bool
     */
    public function index($imId, $uid, $userName)
    {
        $medalServ = new MedalService();
        $medalLogServ = new MedalLogService();
        $memMedalServ = new MemberMedalService();

        // 判断是否有该勋章
        $medalData = $medalServ->get($imId);
        if (empty($medalData)) {
            return false;
        }

        // 勋章申请
        $mlId = $medalLogServ->insert([
            'im_id' => $imId,
            'mem_uid' => $uid
        ]);

        try {
            $memMedalServ->start_trans();
            $medalLogServ->start_trans();

            // 是否已经拥有过该勋章
            $memMedalData = $memMedalServ->get_by_conds([
                'im_id' => $imId,
                'mem_uid' => $uid,
            ]);
            if (empty($memMedalData)) {
                $memMedalServ->insert([
                    'im_id' => $imId,
                    'mem_uid' => $uid,
                    'mem_username' => $userName,
                    'im_total' => 1,
                ]);
            } else {
                $memMedalServ->addMedalTotal($imId, $uid);
            }
            // 完成获得勋章
            $medalLogServ->update($mlId, ['get_status' => MedalLogModel::GET_STATUS_SUCCESS]);

            $memMedalServ->commit();
            $medalLogServ->commit();
        } catch (\Exception $e) {
            $memMedalServ->rollback();
            $medalLogServ->rollback();

            // 更新获取勋章失败
            $medalLogServ->update($mlId, ['get_status' => MedalLogModel::GET_STATUS_FAILURE]);

            return false;
        }

        return true;
    }
}
