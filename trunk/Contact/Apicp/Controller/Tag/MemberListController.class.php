<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/21
 * Time: 10:07
 */
namespace Apicp\Controller\Tag;

use Common\Service\TagService;

class MemberListController extends AbstractController
{

    /**
     * 获取标签成员列表
     *
     * @author zhonglei
     */
    public function Index_post()
    {

        $tagId = I('post.tag_id', '', 'trim');
        $keyword = I('post.keyword', '', 'trim');
        $page = I('post.page', 1, 'Intval');
        $limit = I('post.limit', 20, 'Intval');

        if (empty($tagId)) {
            E('_ERR_TAGID_IS_NULL');
        }

        $tagServ = new TagService();
        $this->_result = $tagServ->memberList([
            'tagIds' => [$tagId],
            'tagUserName' => $keyword
        ], $page, $limit);

        return true;
    }
}
