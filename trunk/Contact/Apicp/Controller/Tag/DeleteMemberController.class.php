<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/21
 * Time: 15:12
 */
namespace Apicp\Controller\Tag;

use Common\Service\TagService;

class DeleteMemberController extends AbstractController
{

    /**
     * 删除标签成员
     * @author zhonglei
     */
    public function Index_post()
    {
        $tagId = I('post.tag_id', '', 'trim');
        $uids = I('post.uids', '', 'trim');
        $dept_ids = I('post.dept_ids', 0, 'trim');

        if (empty($tagId)) {
            E('_ERR_TAGID_IS_NULL');
        }

        if ((is_array($uids) && $uids) || (is_array($dept_ids) && $dept_ids)) {
            $tagServ = new TagService();
            $tagServ->deleteMember($tagId, $uids, $dept_ids);
        } else {
            E('_ERR_UID_DEPTID_IS_NULL');
        }
    }
}
