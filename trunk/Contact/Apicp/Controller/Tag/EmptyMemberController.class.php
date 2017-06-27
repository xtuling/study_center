<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/29
 * Time: 16:14
 */
namespace Apicp\Controller\Tag;

use Common\Service\TagService;

class EmptyMemberController extends AbstractController
{

    /**
     * 清空标签成员
     * @author zhonglei
     */
    public function Index_post()
    {
        $tagId = I('post.tag_id', '', 'trim');

        if (empty($tagId)) {
            E('_ERR_TAGID_IS_NULL');
        }

        $tagServ = new TagService();
        $tagServ->emptyMember($tagId);
    }
}
