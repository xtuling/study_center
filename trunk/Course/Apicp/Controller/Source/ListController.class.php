<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/27
 * Time: 11:01
 */
namespace Apicp\Controller\Source;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\SourceService;
use Common\Service\ArticleSourceService;

class ListController extends \Apicp\Controller\AbstractController
{
    /**
     * List
     * @author liyifei
     * @desc 素材列表接口
     * @param Int page:1 当前页(默认第一页)
     * @param Int limit:20 当前页条数
     * @param String source_title 素材名称
     * @param String source_key 素材标识
     * @param String source_status 素材状态（1=转码中；2=正常）
     * @param String source_type 素材类型（1=图文素材；2=音图素材；3=视频素材；4=文件素材；5=外部素材）
     * @param String keyword 搜索关键字（素材ID、素材标题,同时搜索）
     * @param String ea_name 创建人名称
     * @param Int start_time 更新开始时间(毫秒级时间戳)
     * @param Int end_time 更新结束时间(毫秒级时间戳)
     * @return array 列表信息

     *          array(

     *              'total' => 100, // 总条数

     *              'page' => 1, // 当前页

     *              'limit' => 20, // 当前页条数

     *              'list' => array( // 列表数据

     *                  'source_id' => 1, // 素材主键（页面不展示）

     *                  'source_title' => '电商冲击,实体店靠什么赢', // 素材标题

     *                  'source_status' => 1, // 素材状态（1=转码中；2=正常）

     *                  'source_key' => 'P17042611409', // 素材ID（页面展示）

     *                  'source_type' => 1, // 素材类型（1=图文素材；2=音图素材；3=视频素材；4=文件素材；5=外部素材）

     *                  'used_total' => 2, // 素材被使用次数

     *                  'ea_name' => '爱居兔', // 创建者

     *                  'update_time' => 1493264288000, // 最后更新时间

     *              )

     *          )

     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'page' => 'integer',
            'limit' => 'integer',
            'source_title' => 'max:64',
            'source_type' => 'integer|in:1,2,3,4,5',
            'source_status' => 'integer|between:1,2',
            'ea_name' => 'max:50',
            'start_time' => 'integer',
            'end_time' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;
        $postData['keyword'] = I('post.keyword', '', 'trim');
        $postData['source_key'] = I('post.source_key', '', 'trim');

        // 素材列表
        $sourceServ = new SourceService();
        $list = $sourceServ->listSource($postData);
        if ($list) {
            $source_ids = array_column($list, 'source_id');
            // 素材被用次数
            $asServ = new ArticleSourceService();
            $asList = $asServ->list_by_conds(['source_id' => $source_ids]);
            foreach ($list as $k => $v) {
                $list[$k]['used_total'] = 0;
                foreach ($asList as $as) {
                    if ($v['source_id'] == $as['source_id']) {
                        $list[$k]['used_total'] += 1;
                    }
                }
            }
        }

        // 数据总数
        $total = $sourceServ->countSource($postData);

        $this->_result = [
            'page' => $postData['page'],
            'limit' => $postData['limit'],
            'total' => $total,
            'list' => $list,
        ];
    }
}
