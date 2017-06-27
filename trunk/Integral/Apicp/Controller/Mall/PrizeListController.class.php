<?php
/**
 * Created by IntelliJ IDEA.
 * 奖品列表
 * User: zhoutao
 * Date: 2016-12-06
 * Time: 16:48:56
 */

namespace Apicp\Controller\Mall;

use Common\Common\Attach;
use Common\Model\PrizeModel;
use Common\Service\PrizeService;

class PrizeListController extends AbstractController
{
    public function before_action($action = '')
    {
        // 开启自动获取
        $this->autoGetData = true;
        $this->field = [
            'name' => [
                'require' => false,
                'verify' => 'strval',
                'cn' => '奖品名称',
            ],
            'on_sale' => [
                'require' => false,
                'verify' => 'intval',
                'cn' => '奖品状态',
                'area' => [PrizeModel::ON_SALE, PrizeModel::OFF_SALE],
            ],
            'page' => [
                'require' => false,
                'default' => 1,
                'verify' => 'intval',
            ],
            'limit' => [
                'require' => false,
                'default' => 15,
                'verify' => 'intval',
            ],
        ];

        return parent::before_action($action);
    }

    public function Index()
    {
        // 分页参数
        list($start, $perpage, $this->_result['page']) = $this->getPageOption('page', 'limit');
        $prizeServ = new PrizeService();
        // 奖品列表
        $this->_result['list'] = $prizeServ->getPrizeList($this->data, [$start, $perpage], ['sequence' => 'ASC', 'updated' => 'DESC']);
        $this->_result['total'] = $prizeServ->countPrizeList($this->data);
        // 处理列表中的附件
        $this->dealAttach();
        // 处理返回值
        $this->dealResult();

        return true;
    }

    /**
     * 处理列表中的附件
     * @return bool
     */
    protected function dealAttach()
    {
        // 获取列表图片ID
        $attIdArr = [];
        foreach ($this->_result['list'] as &$item) {
            $item['picture'] = explode(',', $item['picture']);
            $item['picture'] = $item['picture'][0];
            $attIdArr[] = $item['picture'];
        }

        // 获取图片地址
        $attServ = new Attach();
        $attArr = $attServ->listAttachUrl($attIdArr);
        foreach ($this->_result['list'] as &$item) {
            if (isset($attArr[$item['picture']])) {
                $item['picture'] = $attArr[$item['picture']]['atAttachment'];
            } else {
                $item['picture'] = '';
            }
        }

        return true;
    }

    /**
     * 处理返回值
     * @return bool
     */
    protected function dealResult()
    {
        // 去除不需要的字段
        $filterField = ['range_mem', 'range_dep', 'status', 'created', 'updated', 'deleted', 'is_all', 'desc', 'domain'];
        foreach ($this->_result['list'] as &$item) {
            foreach ($filterField as $key) {
                unset($item[$key]);
            }
        }

        return true;
    }
}
