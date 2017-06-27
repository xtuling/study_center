<?php
/**
 * Created by IntelliJ IDEA.
 * 奖品添加
 * User: zhoutao
 * Date: 2016-12-06
 * Time: 16:48:56
 */

namespace Apicp\Controller\Mall;

use Common\Model\PrizeModel;
use Common\Service\PrizeService;

class PrizeAddController extends AbstractController
{
    public function before_action($action = '')
    {
        // 开启自动获取
        $this->autoGetData = true;
        // 获取的数据规则
        $this->field = [
            'ia_id' => [
                'require' => false,
                'verify' => 'intval',
                'cn' => '要编辑的奖品ID',
            ],
            'name' => [
                'require' => true,
                'verify' => 'strval',
                'cn' => '名称',
                'maxLength' => PrizeModel::MAX_NAME_COUNT
            ],
            'sequence' => [
                'require' => true,
                'verify' => 'intval',
                'cn' => '序号',
            ],
            'on_sale' => [
                'require' => false,
                'verify' => 'intval',
                'cn' => '状态',
            ],
            'reserve' => [
                'require' => true,
                'verify' => 'intval',
                'cn' => '库存',
            ],
            'integral' => [
                'require' => true,
                'verify' => 'intval',
                'cn' => '所需积分',
                'maxLength' => PrizeModel::MAX_INTEGRAL_LEN
            ],
            'times' => [
                'require' => false,
                'verify' => 'intval',
                'cn' => '每人限兑次数',
            ],
            'picture' => [
                'require' => true,
                'cn' => '图片',
                'maxLength' => PrizeModel::MAX_PICTURE_NUMBER
            ],
            'desc' => [
                'require' => true,
                'verify' => 'strval',
                'cn' => '介绍',
            ],
            'range_mem' => [
                'require' => false,
                'cn' => '人员范围',
            ],
            'range_dep' => [
                'require' => false,
                'cn' => '部门范围',
            ],
        ];

        return parent::before_action($action);
    }

    public function Index()
    {
        // 处理
        $this->dealData();

        if (!empty($this->data['ia_id'])) {
            // 更新
            $prizeServ = new PrizeService();
            $iaId = $this->data['ia_id'];
            unset($this->data['ia_id']);
            $prizeServ->update($iaId, $this->data);
        } else {
            // 排序兼容
            $this->data['updated'] = MILLI_TIME;
            // 入库
            $prizeServ = new PrizeService();
            $prizeServ->insert($this->data);
        }

        return true;
    }

    /**
     * 处理数据
     * @return bool
     */
    protected function dealData()
    {
        $this->data['picture'] = implode(',', $this->data['picture']);
        $this->data['range_mem'] = implode(',', $this->data['range_mem']);
        $this->data['range_dep'] = implode(',', $this->data['range_dep']);
        // 如果没有设置范围 则全公司
        if (empty($this->data['range_mem']) && empty($this->data['range_dep'])) {
            $this->data['range_mem'] = '';
            $this->data['range_dep'] = '';
            $this->data['is_all'] = self::MEAN_TRUE;
        } else {
            $this->data['is_all'] = self::MEAN_FALSE;
        }

        return true;
    }
}
