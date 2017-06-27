<?php
/**
 * ArticleUpdateController.class.php
 * 更新文章推荐 RPC 接口
 * @author Deepseath
 * @version $Id$
 */
namespace Rpc\Controller\Recommender;

use Common\Service\CommonRecommenderService;
use Common\Model\CommonRecommenderModel;
use Think\Log;

/**
 * 更新文章推荐 RPC 接口
 * @uses 调用方法：\Com\Rpc::phprpc(rpc 接口 url)->invoke(接口方法名, 需要传入的参数数组key-value);
 */
class ArticleUpdateController extends AbstractController
{

    /**
     * 更新文章推荐信息
     * @desc 【RPC】更新文章推荐信息接口
     * @param string app:true 被推荐数据所在应用模块目录标识名
     * @param string dataCategoryId:true 被推荐数据所属的分类Id，可以为空，但必须提供该参数
     * @param string dataId:true 被推荐数据的原始数据 Id。<strong style="color: red">注意：请确保被推荐数据 app、dataCategoryId、dataId 联合值为唯一</strong>
     * @param string title:false: 文章标题
     * @param string summary:false: 文章摘要
     * @param string attachId:false: 封面图片附件 ID
     * @param string pic:false: 封面图片 url
     * @param string url:false: 文章链接
     * @param integer dateline:false:0 文章发布时间戳，不设置或者为空，则以推荐时间为准
     * @return boolean
     */
    public function Index()
    {
        if (!$this->_checkKeyParams()) {
            return false;
        }

        $keys = [
            'title',
            'summary',
            'attachId',
            'pic',
            'url',
            'dateline'
        ];
        foreach ($keys as $_key) {
            if (!isset($this->_params[$_key])) {
                $this->_params[$_key] = '';
            }
        }

        if (empty($this->_params['app']) && empty($this->_params['dataId']) && empty($this->_params['dataCategoryId'])) {
            Log::record('推荐的数据标记为空');
            return $this->_set_error('_ERR_RECOMMENDER_ARTICLE_NULL_90003');
        }

        $recommenderService = new CommonRecommenderService();
        // 判断推荐的数据是否已存在
        $exists = $recommenderService->getDuplicate(CommonRecommenderModel::TYPE_ARTICLE, $this->_params['app']
            , $this->_params['dataId'], $this->_params['dataCategoryId']);
        if (!empty($exists)) {
            $recommenderId = $exists['recommender_id'];
        } else {
            Log::record('该数据尚未被推荐过，更新改为新增推荐：app=' . $this->_params['app'] . '; dataId=' . $this->_params['dataId']
                . '; dataCategoryId=' . $this->_params['dataCategoryId']);
            $recommenderId = 0;
        }

        $data = [
            'type' => CommonRecommenderModel::TYPE_ARTICLE,
            'displayorder' => CommonRecommenderModel::VALUE_DISPLAYORDER_MIN,
            'hide' => CommonRecommenderModel::HIDE_NO,
            'system' => CommonRecommenderModel::SYSTEM_NO,
            'title' => $this->_params['title'],
            'attach_id' => $this->_params['attachId'],
            'pic' => $this->_params['pic'],
            'url' => $this->_params['url'],
            'description' => $this->_params['summary'],
            'app_dir' => $this->_params['app'],
            'app_identifier' => APP_IDENTIFIER,
            'data_id' => $this->_params['dataId'],
            'data_category_id' => $this->_params['dataCategoryId'],
            'data' => [],
            'dateline' => !$this->_params['dateline'] ? MILLI_TIME : $this->_params['dateline'],
            'adminer_id' => '',
            'adminer' => ''
        ];

        $recommenderService->remmenderUpdate($data, $recommenderId);

        // 设置其排序号为 ID
        if ($recommenderId) {
            $recommenderService->update($recommenderId, [
                'displayorder' => $recommenderId
            ]);
        }

        $data['recommender_id'] = $recommenderId;
        Log::record('<!-- 更新推荐数据：');
        Log::record($data);
        Log::record('结束更新推荐数据 -->');

        return true;
    }
}
