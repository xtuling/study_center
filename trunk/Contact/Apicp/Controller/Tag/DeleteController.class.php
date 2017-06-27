<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/20
 * Time: 10:24
 */
namespace Apicp\Controller\Tag;

use Common\Service\TagService;

class DeleteController extends AbstractController
{

    /**
     * 删除标签
     * @author zhonglei
     * @time 2016-09-20 10:24:45
     */
    public function Index_post()
    {
        $tagId = I('post.tag_id', '', 'trim');

        if (empty($tagId)) {
            E('_ERR_TAGID_IS_NULL');
        }

        $tagServ = new TagService();
        $tagServ->delete($tagId);
    }
}
