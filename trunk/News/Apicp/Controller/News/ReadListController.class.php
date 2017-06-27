<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/13
 * Time: 11:48
 */
namespace Apicp\Controller\News;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\NewsHelper;
use Common\Common\User;
use Common\Service\ArticleService;
use Common\Service\ReadService;

class ReadListController extends \Apicp\Controller\AbstractController
{
    /**
     * ReadList
     * @desc 阅读列表
     * @param Int article_id:true 新闻ID
     * @param Int read_type:true 阅读类型（1=未读，2=已读）
     * @param Int page 页码
     * @param Int limit 每页数据条数
     * @return array 阅读列表
     *               array(
                        'article_id' => 123, // 新闻ID
                        'read_type' => 1, // 阅读类型（1=未读，2=已读）
                        'title' => '', // 新闻标题
                        'send_time' => '', // 更新时间
                        'page' => '', // 页码
                        'limit' => '', // 每页数据条数
                        'read_total' => '', // 已读总数
                        'unread_total' => '', // 未读总数
                        'list' => array(
                            'username' => '张三', // 姓名
                            'dp_name' => array('技术部'), // 所属部门
                            'job' => 'PHP', // 职位
                            'mobile' => '15821392414', // 手机号码
                            'created' => '1234566898988', // 阅读时间(毫秒级时间戳)
                        ),
                    );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'article_id' => 'require|integer',
            'read_type' => 'require|integer',
            'page' => 'integer',
            'limit' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 默认值
        $postData['page'] = isset($postData['page']) ? $postData['page'] : Constant::PAGING_DEFAULT_PAGE;
        $postData['limit'] = isset($postData['limit']) ? $postData['limit'] : Constant::PAGING_DEFAULT_LIMIT;

        // 取新闻信息
        $articleServ = new ArticleService();
        $newsInfo = $articleServ->get($postData['article_id']);
        if (empty($newsInfo)) {
            E('_ERR_ARTICLE_NOT_FOUND');
        }

        $list = [];
        $total = 0;
        $readServ = new ReadService();

        // 已读
        $read_total = $readServ->count_by_conds(['article_id' => $postData['article_id']]);
        if ($postData['read_type'] == Constant::READ_STATUS_IS_YES) {
            list($start, $perpage) = page_limit($postData['page'], $postData['limit']);
            $readList = $readServ->list_by_conds(['article_id' => $postData['article_id']], [$start, $perpage]);
            foreach ($readList as $k => $v) {
                $readList[$k]['dp_name'] = unserialize($v['dp_name']);
            }
            $list = $readList;
            $total = $read_total;
        }

        // 未读
        $newsHelper = &NewsHelper::instance();
        list($uids_all, $uids_read, $uids_unread) = $newsHelper->getReadData($postData['article_id']);
        $unread_total = count($uids_unread);
        if ($postData['read_type'] == Constant::READ_STATUS_IS_NO) {
            // 取未读人员信息
            if ($uids_unread) {
                $userServ = &User::instance();
                $userList = $userServ->listByConds(['memUids' => $uids_unread], $postData['page'], $postData['limit']);
                if ($userList) {
                    foreach ($userList['list'] as $v) {
                        $list[] = [
                            'username' => $v['memUsername'],
                            'dp_name' => empty($v['dpName']) ? [] : array_column($v['dpName'], 'dpName'),
                            'job' => $v['memJob'],
                            'mobile' => $v['memMobile'],
                        ];
                    }
                }

            }
            $total = $unread_total;
        }

        $this->_result = [
            'article_id' => $postData['article_id'],
            'read_type' => $postData['read_type'],
            'limit' => $postData['limit'],
            'page' => $postData['page'],
            'title' => $newsInfo['title'],
            'send_time' => $newsInfo['send_time'],
            'read_total' => $read_total,
            'unread_total' => $unread_total,
            'total' => $total,
            'list' => $list,
        ];
    }
}
