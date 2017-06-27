<?php
/**
 * 获取激励试卷列表接口
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-06-01 17:47:52
 * @version $Id$
 */

namespace Apicp\Controller\Encourage;

use Common\Service\PaperService;
use Common\Service\CategoryService;

class PaperListController extends AbstractController
{

    /**
     * @var  PaperService  实例化试卷表对象
     */
    protected $paper_serv;

    /**
     * @var  CategoryService 实例化试卷分类表对象
     */
    protected $cate_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->paper_serv = new PaperService();
        $this->cate_serv = new CategoryService();

        return true;
    }

    public function Index_post()
    {
        $ec_id = I('ec_id', 0, 'intval');

        // 分类ID不能为空
        if (!$ec_id) {

            E('_EMPTY_EC_ID');

            return false;
        }

        // 分类是否存在
        if (!$category = $this->cate_serv->get($ec_id)) {

            E('_ERR_CATEGORY_NOT_FOUND');

            return false;
        }

        $paper_list = $this->paper_serv->list_by_conds(array(
            'ec_id' => $ec_id,
            'cate_status' => PaperService::EC_OPEN_STATES,
            'exam_status' => PaperService::PAPER_PUBLISH,
            'end_time > ?' => MILLI_TIME
        ), null, array(), 'ep_id,ep_name');

        foreach ($paper_list as $k => &$v) {

            $v['ep_id'] = intval($v['ep_id']);
        }

        $this->_result = array(
            'list' => !empty($paper_list) ? $paper_list : array(),
        );

        return true;

    }

}
