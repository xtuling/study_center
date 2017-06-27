<?php
/**
 * Created by IntelliJ IDEA.
 * 增减积分接口（手动含批量）
 * User: zhoutao
 * Date: 2016/11/15
 * Time: 上午10:07
 */

namespace Apicp\Controller\Integral;

use VcySDK\Integral;
use VcySDK\Service;

class AddDeductController extends AbstractController
{
    /** 积分获得类型 手动增加 */
    const MIL_OPT_TYPE_ADD = 2;
    /** 积分获得类型 手动扣减 */
    const MIL_OPT_TYPE_DEDUCT = 3;
    /** 积分获得类型 数据范围 */
    protected $milOptTypeArea = [
        self::MIL_OPT_TYPE_ADD,
        self::MIL_OPT_TYPE_DEDUCT
    ];

    public function Index()
    {
        $updateData = $this->getUpdateData();

        $sdk = new Integral(Service::instance());
        $this->_result = $sdk->integralUpdate($updateData);

        return true;
    }

    /**
     * 获取提交数据
     *
     * @return array|bool
     */
    protected function getUpdateData()
    {
        $uids = I('post.uids');
        $milOptType = I('post.milOptType');
        $miType = I('post.miType', 'mi_type0');
        $integral = I('post.integral');
        $remark = I('post.remark');

        // 验证数据
        settype($uids, 'array');
        $updateData = [
            'milCreateMemUid' => $this->_login->user['eaId'],
            'milCreateMemUsername' => $this->_login->user['eaRealname'],
            'miType' => $miType,
            'msgIdentifier' => APP_IDENTIFIER
        ];
        if (empty($uids)) {
            E('_ERR_EMPTY_UID');
            return false;
        } else {
            $updateData['uids'] = implode(',', $uids);
        }
        if (empty($milOptType) || !in_array($milOptType, $this->milOptTypeArea)) {
            E('_ERR_MIL_OPT_TYPE_DATA_ERROR');
            return false;
        } else {
            $updateData['milOptType'] = $milOptType;
        }
        if (empty($integral) && $integral != 0) {
            E('_ERR_INTEGRAL_DATA_ERROR');
            return false;
        } else {
            $updateData['integral'] = $integral;
        }
        if (!empty(trim($remark))) {
            $updateData['remark'] = trim($remark);
        }

        return $updateData;
    }
}
