<?php
/**
 * Created by IntelliJ IDEA.
 * 奖品上下架
 * User: zhoutao
 * Date: 2016-12-06
 * Time: 16:48:56
 */

namespace Apicp\Controller\Mall;

use Common\Model\PrizeModel;
use Common\Service\PrizeService;

class PrizeShelvesController extends AbstractController
{
    public function before_action($action = '')
    {
        // 开启自动获取
        $this->autoGetData = true;
        // 获取的数据规则
        $this->field = [
            'ia_id' => [
                'require' => true,
                'cn' => '奖品ID',
            ],
            'type' => [
                'require' => true,
                'cn' => '操作类型',
                'area' => [PrizeModel::ON_SALE, PrizeModel::OFF_SALE],
            ],
        ];

        return parent::before_action($action);
    }

    public function Index()
    {
        $prizeServ = new PrizeService();
        try {
            $prizeServ->update_by_conds(['ia_id' => $this->data['ia_id']], ['on_sale' => $this->data['type']]);
        } catch (\Exception $e) {
            \Think\Log::record(PrizeModel::ON_SALE ? '上架' : '下架' . '奖品,ID:(' . $this->data['ia_id'] . ')' . var_export($e, true));
            // 接口返回抛错 需求如此
            // 查询序号
            $prizeArr = $prizeServ->list_by_conds(['ia_id' => $this->data['ia_id']]);
            $sequenceList = array_column($prizeArr, 'sequence');
            E(L('_ERR_BATCH_OPERATE_ERROR', ['ids' => implode(',', $sequenceList)]));

            return false;
        }

        return true;
    }
}
