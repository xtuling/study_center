<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/19
 * Time: 19:44
 */
namespace Apicp\Controller\Tag;

use Common\Service\TagService;

class SaveController extends AbstractController
{

    /**
     * 保存标签
     * @author zhonglei
     * @time 2016-09-19 19:46:52
     */
    public function Index_post()
    {
        $tagId = I('post.tag_id', '', 'trim');
        $tagName = I('post.name', '', 'trim');
        $tagOrder = I('post.order', 0, 'Intval');

        if (empty($tagName)) {
            E('_ERR_TAGNAME_IS_NULL');
        }

        $tagServ = new TagService();
        $tagServ->save($tagId, $tagName, $tagOrder);
    }
}
