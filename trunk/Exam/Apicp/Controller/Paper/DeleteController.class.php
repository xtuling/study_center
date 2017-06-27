<?php
/**
 * 删除试卷
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:29:40
 * @version $Id$
 */

namespace Apicp\Controller\Paper;

use Common\Service\PaperService;
use Common\Service\PaperTempService;
use Common\Service\RightService;
use Common\Service\SnapshotService;
use Think\Exception;

class DeleteController extends AbstractController
{

    /**
     * @var  PaperService  实例化试卷表对象
     */
    protected $paper_serv;

    /**
     * @var  RightService  实例化权限表对象
     */
    protected $right_serv;

    /**
     * @var  SnapshotService  实例化试卷快照表对象
     */
    protected $snapshot_serv;

    /**
     * @var  PaperTempService  实例化试卷临时备选题目储存表对象
     */
    protected $paper_temp_serv;


    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->paper_serv = new PaperService();
        $this->right_serv = new RightService();
        $this->snapshot_serv = new SnapshotService();
        $this->paper_temp_serv = new PaperTempService();

        return true;
    }

    public function Index_post()
    {

        $params = I('post.ep_ids');
        $ep_ids = array_column($params, 'ep_id');

        // 试卷ID不能为空
        if (empty($ep_ids) || !is_array($ep_ids)) {

            E('_ERR_EP_ID_EMPY');

            return false;
        }

        try {
            $this->paper_serv->start_trans();

            // 删除试卷
            $this->paper_serv->delete($ep_ids);
            // 删除试卷权限
            $this->right_serv->delete_by_conds(
                array(
                    'epc_id' => $ep_ids,
                    'er_type' => PaperService::RIGHT_PAPER
                )
            );
            // 删除试卷快照
            $this->snapshot_serv->delete_by_conds(array('ep_id' => $ep_ids));
            // 删除试卷临时备选题目
            $this->paper_temp_serv->delete_by_conds(array('ep_id' => $ep_ids));

            $this->paper_serv->commit();
        } catch (Exception $e) {

            $this->_d->rollback();
            E('_ERR_DELETE_TOPIC_FAILED');

            return false;
        }

        // 循环删除推荐
        foreach ($ep_ids as $v) {

            $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Recommender/ArticleDelete');

            $data_send = array(
                'app' => 'exam',
                'dataCategoryId' => '',
                'dataId' => $v,
            );

            \Com\Rpc::phprpc($url)->invoke('Index', $data_send);
        }

        return true;

    }

}
