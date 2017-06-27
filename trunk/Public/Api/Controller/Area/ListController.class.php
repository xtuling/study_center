<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 2016/10/18
 * Time: 14:58
 */
namespace Api\Controller\Area;

use Common\Service\AreaService;

class ListController extends AbstractController
{

    protected $_require_login = false;

    /**
     * 获取地区列表
     * @author zhonglei
     */
    public function Index_post()
    {
        $parent_id = I('post.parent_id', 0, 'intval');
        $areaServ = new AreaService();
        $this->_result = $areaServ->list_by_parent($parent_id);
    }
}
