<?php
/**
 * Created by IntelliJ IDEA.
 * 奖品详情
 * User: zhoutao
 * Date: 2016/12/07
 * Time: 上午14:27
 */

namespace Api\Controller\Mall;

use Common\Common\Attach;
use Common\Model\ConvertModel;
use Common\Model\PrizeModel;
use Common\Service\ConvertService;
use Common\Service\PrizeService;

class PrizeDetailController extends AbstractController
{
    public function before_action($action = '')
    {
        // 开启自动获取
        $this->autoGetData = true;
        $this->field = [
            'ia_id' => [
                'require' => true,
                'verify' => 'intval',
                'cn' => '奖品ID',
            ],
        ];

        return parent::before_action($action);
    }

    public function Index()
    {
        // 获取奖品数据
        $prizeServ = new PrizeService();
        $this->_result = $prizeServ->get($this->data['ia_id']);
        // 已删除
        if (empty($this->_result)) {
            E('_ERR_PRIZE_CONVERT_IS_DELETED');
            return false;
        }
        // 已下架
        if ($this->_result['on_sale'] == PrizeModel::OFF_SALE) {
            E('_ERR_PRIZE_CONVERT_IS_OFFSALE');
            return false;
        }
        // 可见范围
        if (!$this->verifyArea($this->_result)) {
            E('_ERR_PRIZE_CONVERT_NOT_IN_AREA');
            return false;
        };

        // 获取已经兑换的数据
        $convertServ = new ConvertService();
        $this->_result['exchanged_times'] = $convertServ->count_by_conds([
            'ia_id' => $this->data['ia_id'],
            'uid' => $this->uid,
            'convert_status' => [
                ConvertModel::CONVERT_STATUS_AGREE,
                ConvertModel::CONVERT_STATUS_ING
            ]
        ]);

        // 处理图片
        $this->getPicture();
        // 过滤不需要的字段
        $this->filterField();

        return true;
    }

    /**
     * 过滤不需要的字段
     */
    protected function filterField()
    {
        $field = [
            'status',
            'created',
            'updated',
            'deleted',
            'range_mem',
            'range_dep',
            'domain',
            'is_all',
            'ia_id',
            'sequence'
        ];
        foreach ($field as $key) {
            unset($this->_result[$key]);
        }

        return true;
    }

    /**
     * 处理图片
     * @return bool
     */
    protected function getPicture()
    {
        // 获取图片地址
        $attServ = new Attach();
        $this->_result['picture'] = explode(',', $this->_result['picture']);
        $attArr = $attServ->listAttachUrl($this->_result['picture']);
        // 重构图片数据结构
        $this->_result['picture'] = [];
        foreach ($attArr as $item) {
            $this->_result['picture'][] = $item['atAttachment'];
        }

        return true;
    }
}
