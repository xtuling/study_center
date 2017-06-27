<?php
/**
 * 获取标签
 * Created by PhpStorm.
 * User: 何岳龙
 * Date: 2016年9月1日15:19:55
 */
namespace Apicp\Controller\ChooseMem;

use VcySDK\Service;
use VcySDK\Tag;


class TagController extends AbstractController
{

    /**
     * VcySDK 附件操作类
     * @type Tag
     */
    protected $_tag;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }

        $service = &Service::instance();
        $this->_tag = new Tag($service);

        return true;
    }

    public function Index()
    {

        $search = I("post.search");
        $limit = I("post.limit", 10);
        $page = I("post.page", 1);

      //  $search['tagIds']=array('C5DBEE327F0000015D3119AE48026993');

        // 搜索标签列表
        $tagIds = array();

        // 如果特殊标签存在
        if (!empty($search['tagIds'])) {

            // 去除数组中的空值
            array_filter($search['tagIds']);

            $tagIds = $search['tagIds'];

            sort($tagIds);
        }


        // 获取所有标签
        $tags = $this->_tag->listAll(array('tagIds' => $tagIds, 'pageNum' => $page, 'pageSize' => $limit));

        // 更改输出变量
        $tags['limit'] = $tags['pageSize'];
        $tags['page'] = $tags['pageNum'];

        // 删除变量
        unset($tags['pageSize'], $tags['pageNum']);
        $this->_result = $tags;

        return true;
    }

}
