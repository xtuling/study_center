<?php
/**
 * Created by IntelliJ IDEA.
 * 微信端我的奖品兑换记录列表
 * User: zs_anything
 * Date: 2016/12/07
 * Time: 上午14:27
 */

namespace Api\Controller\Mall;

use Common\Service\ConvertService;
use Common\Common\Attach;

class PrizeConvertListController extends AbstractController
{

    public function Index()
    {
        $page = I('post.page', 1);
        $limit = I('post.limit', 20);

        list($start, $limit, $page) = page_limit($page, $limit);

        $loginUserInfo = $this->_login->user;

        $params = array(
            'memUid' => $loginUserInfo['memUid'],
        );

        $convertService = new ConvertService();

        $total = $convertService->countWxPrizeConvert($params);

        $data = $convertService->getWxPrizeConvertPageList($params, array($start, $limit), array('created' => 'DESC'));

        $this->formatAttrUrl($data);

        $this->_result = [
            'list' => $data,
            'page' => $page,
            'total' => $total
        ];

        return true;
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

}
