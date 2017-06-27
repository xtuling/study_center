<?php
/**
 * DeleteController.class.php
 * 删除我的收藏
 * @author Xtong
 * @version $Id$
 */
namespace Api\Controller\Collection;

use Common\Model\CommonCollectionModel;
use Common\Service\CommonCollectionService;

class DeleteController extends AbstractController
{

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        return true;
    }

    /**
     * 删除我的收藏
     *
     * @return array
     */
    public function Index()
    {

        $collection_id = I('post.collection_id');

        $conds = [
            'c_deleted' => CommonCollectionModel::DELETE_YES,
            'collection_id' => $collection_id,
            'uid' => $this->uid
        ];

        $collectionService = new CommonCollectionService();

        $collectionService->delete_by_conds($conds);

        return true;
    }

}
