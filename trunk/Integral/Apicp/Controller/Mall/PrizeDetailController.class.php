<?php
/**
 * Created by IntelliJ IDEA.
 * 奖品详情
 * User: zhoutao
 * Date: 2016-12-06
 * Time: 16:48:56
 */

namespace Apicp\Controller\Mall;

use Common\Common\Attach;
use Common\Common\Department;
use Common\Common\User;
use Common\Model\ConvertModel;
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
        $this->_result = $prizeServ->getWithOutDeleted($this->data['ia_id']);
        if (empty($this->_result)) {
            return true;
        }

        // 获取已经兑换的数据
        $convertServ = new ConvertService();
        $this->_result['exchanged_times'] = $convertServ->count_by_conds([
            'ia_id' => $this->data['ia_id'],
            'convert_status' => ConvertModel::CONVERT_STATUS_AGREE
        ]);

        // 处理人员部门范围
        $this->getArea();
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
            'created',
            'updated',
            'deleted'
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
            $this->_result['picture'][] = [
                'atId' => $item['atId'],
                'atAttachment' => $item['atAttachment']
            ];
        }

        return true;
    }

    /**
     * 处理可见范围
     * @return bool
     */
    protected function getArea()
    {
        // 获取人员
        if (!empty($this->_result['range_mem'])) {
            $memServ = new User();
            $memberArr = $memServ->listByUid(explode(',', $this->_result['range_mem']));
            $this->_result['range_mem'] = [];
            foreach ($memberArr as $member) {
                $this->_result['range_mem'][] = [
                    'memUid' => $member['memUid'],
                    'memFace' => $member['memFace'],
                    'memUsername' => $member['memUsername'],
                ];
            }
        }
        // 获取部门
        if (!empty($this->_result['range_dep'])) {
            $depServ = new Department();
            $depArr = $depServ->listById(explode(',', $this->_result['range_dep']));
            $this->_result['range_dep'] = [];
            foreach ($depArr as $dep) {
                $this->_result['range_dep'][] = [
                    'dpId' => $dep['dpId'],
                    'dpName' => $dep['dpName'],
                ];
            }
        }

        return true;
    }
}
