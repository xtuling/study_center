<?php
/**
 * Created by IntelliJ IDEA.
 * 奖品删除
 * User: zhoutao
 * Date: 2016-12-06
 * Time: 16:48:56
 */

namespace Apicp\Controller\Mall;

use Common\Service\PrizeService;

class PrizeDelController extends AbstractController
{
    public function before_action($action = '')
    {
        // 开启自动获取
        $this->autoGetData = true;
        $this->field = [
            'ia_id' => [
                'require' => true,
                'cn' => '要删除的奖品ID',
            ]
        ];

        return parent::before_action($action);
    }

    public function Index()
    {
        $prizeServ = new PrizeService();
        try {
            $prizeServ->delete_by_conds(['ia_id' => $this->data['ia_id']]);
        } catch (\Exception $e) {
            \Think\Log::record('删除奖品,ID:(' . $this->data['ia_id'] . ')' . var_export($e, true));
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
