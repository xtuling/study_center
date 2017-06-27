<?php
/**
 * Created by IntelliJ IDEA.
 * 微信端奖品列表
 * User: zs_anything
 * Date: 2016/12/07
 * Time: 上午14:27
 */

namespace Api\Controller\Mall;

use Common\Common\Attach;
use Common\Common\Department;
use Common\Service\PrizeService;

class PrizeListController extends AbstractController
{

    public function Index()
    {
        $page = I('post.page', 1);
        $limit = I('post.limit', 10);

        list($start, $limit, $page) = page_limit($page, $limit);

        $loginUserInfo = $this->_login->user;

        $rangeDepStr = $this->__getUserDepartments($loginUserInfo);

        $params = array(
            'rangeMem' => $loginUserInfo['memUid'],
            'rangeDep' => $rangeDepStr
        );

        $prizeService = new PrizeService();
        $data = $prizeService->getWxPrizePageList($params, array($start, $limit));

        // 封装图片url
        $this->formatAttrUrl($data);

        // 格式化库存
        $this->formatReserve($data);

        $total = $prizeService->countWxPrize($params);

        $this->_result = [
            'list' => $data,
            'page' => $page,
            'total' => $total
        ];

        return true;
    }

    /**
     * 获取当前登录用户所属部门, 包括所有上级部门
     * @param $loginUserInfo
     * @return string
     */
    private function __getUserDepartments($loginUserInfo)
    {
        $departmentUtil = new Department();
        $userDepartments = $departmentUtil->list_dpId_by_uid($loginUserInfo['memUid'], true);

        $departmentArray = array();
        foreach ($userDepartments as $value) {
            $departmentArray = array_merge($departmentArray, $value);
        }

        $rangeDepStr = implode('|', array_unique($departmentArray));

        return $rangeDepStr;
    }

    /**
     * 封装奖品图片url
     * @param $data
     * @return mixed
     */
    private function formatAttrUrl(&$data)
    {

        $attIdArr = [];
        foreach ($data as &$item) {
            $item['picture'] = explode(',', $item['picture']);
            $item['picture'] = $item['picture'][0];
            $attIdArr[] = $item['picture'];
        }

        $attachUtil = new Attach();
        $attArr = $attachUtil->listAttachUrl($attIdArr);

        foreach ($data as &$item) {
            if (isset($attArr[$item['picture']])) {
                $item['picture'] = $attArr[$item['picture']]['atAttachment'];
            } else {
                $item['picture'] = '';
            }
        }

        return $data;
    }

    /**
     * 格式化库存 大于9999 显示9999+
     * @param $data
     * @return mixed
     */
    public function formatReserve(&$data)
    {
        foreach ($data as &$obj) {
            if ($obj['reserve'] > 9999) {
                $obj['reserve'] = "9999+";
            }
        }
        return $data;
    }
}
