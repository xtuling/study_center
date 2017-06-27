<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/13
 * Time: 14:57
 */

namespace Apicp\Controller\Tag;

use Common\Service\TagService;

class ListController extends AbstractController
{

    /**
     * 获取标签列表
     * @author zhonglei
     * @time   2016-09-13 16:24:02
     */
    public function Index_post()
    {

        $tagServ = new TagService();

        // 获取1000条畅移创建的标签
        $list = $tagServ->listAll(array(), [
            'permissionType' => TagService::TAG_FILTER_DISABLED,
            'tagOwnType' => TagService::TAG_TYPE_CY,
            'pageSize' => 1000,
        ]);

        $this->_result = [
            'list' => $list
        ];
    }
}
