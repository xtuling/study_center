<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/12
 * Time: 10:46
 */
namespace Common\Service;

use Com\Rpc;
use Common\Common\Constant;
use Common\Common\Msg;
use Common\Model\ArticleModel;
use Common\Model\ClassModel;

class ArticleService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new ArticleModel();
        $this->_class = new ClassModel();
    }

    /**
     * 格式化数据
     * @author liyifei
     * @param array $article 新闻数据
     * @return array
     */
    public function formatData($article)
    {
        // unset多余数据
        $unsetParam = [
            'ea_id',
            'ea_name',
            'top_time',
            'update_time',
            'unread_total',
            'comment_total',
            'like_total',
            'domain',
            'status',
            'created',
            'updated',
            'deleted',
        ];

        foreach ($article as $k => $v) {
            if (in_array($k, $unsetParam)) {
                unset($article[$k]);
            }
        }

        $article['parent_id'] = 0;
        $article['parent_name'] = '';

        // 返回父分类ID,方便前端定位
        $class = $this->_class->get($article['class_id']);

        if ($class) {
            $parent_class = $this->_class->get($class['parent_id']);

            if ($parent_class) {
                $article['parent_id'] = $parent_class['class_id'];
                $article['parent_name'] = $parent_class['class_name'];
            }
        }

        return $article;
    }

    /**
     * @desc 新增新闻推荐
     * @author tangxingguo
     * @param int $articleId 新闻ID
     * @return mixed
     */
    public function addNewsRpc($articleId)
    {
        // 预发布与草稿不推送
        $articleInfo = $this->get($articleId);
        if (in_array($articleInfo['news_status'], [Constant::NEWS_STATUS_DRAFT, Constant::NEWS_STATUS_READY_SEND])) {
            return false;
        }

        // 格式化RPC参数
        $param_arr = $this->formatRpcParams($articleInfo, 1);

        // 推送
        $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Recommender/ArticleNew');
        $res = Rpc::phprpc($url)->invoke('Index', $param_arr);

        return $res;
    }

    /**
     * @desc 更新新闻推荐
     * @author tangxingguo
     * @param Int $articleId 新闻ID
     * @param array $articleInfo 新闻信息
     * @return mixed
     */
    public function updateNewsRpc($articleId, $articleInfo)
    {
        // 预发布与草稿不推送
        if (in_array($articleInfo['news_status'], [Constant::NEWS_STATUS_DRAFT, Constant::NEWS_STATUS_READY_SEND])) {
            return false;
        }

        $info = $this->get($articleId);
        if (empty($info)) {
            return false;
        }
        $articleInfo['article_id'] = $articleId;

        // 格式化RPC参数
        $param_arr = $this->formatRpcParams($articleInfo, 1);

        // 推送
        $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Recommender/ArticleUpdate');
        $res = Rpc::phprpc($url)->invoke('Index', $param_arr);

        return $res;
    }

    /**
     * @desc 删除新闻推荐
     * @author tangxingguo
     * @param int $articleId 新闻ID
     * @return mixed
     */
    public function delNewsRpc($articleId)
    {
        $articleInfo = $this->get($articleId);

        // 格式化RPC参数
        $param_arr = $this->formatRpcParams($articleInfo, 2);

        // 推送
        $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Recommender/ArticleDelete');
        $res = Rpc::phprpc($url)->invoke('Index', $param_arr);

        return $res;
    }

    /**
     * @desc 格式化RPC数据
     * @author tangxingguo
     * @param array $articleInfo 新闻信息
     * @param int $paramType 数据类型（1=添加、更新；2=删除）
     * @return array RPC参数
     */
    private function formatRpcParams($articleInfo, $paramType)
    {
        $param_arr = [];
        switch ($paramType) {
            // 更新或添加
            case 1:
                $param_arr = [
                    // 被推荐数据所在应用模块目录标识名
                    'app'=>'News',
                    // 被推荐数据所属的分类Id，可以为空，但必须提供该参数
                    'dataCategoryId' => $articleInfo['class_id'],
                    // 被推荐数据的原始数据 Id
                    'dataId' => $articleInfo['article_id'],
                    // 文章标题
                    'title' => $articleInfo['title'],
                    // 文章摘要
                    'summary' => $articleInfo['summary'],
                    // 封面图片附件 ID
                    'attachId' => $articleInfo['cover_id'],
                    // 封面图片 url
                    'pic' => $articleInfo['cover_url'],
                    // 文章链接
                    'url' => 'News/Frontend/Index/Detail/Index?article_id=' . $articleInfo['article_id'],
                    // 文章发布时间戳，不设置或者为空，则以推荐时间为准
                    'dateline' => $articleInfo['send_time'],
                ];
                break;
            // 删除
            case 2:
                $param_arr = [
                    // 被推荐数据所在应用模块目录标识名
                    'app'=>'News',
                    // 被推荐数据所属的分类Id，可以为空，但必须提供该参数
                    'dataCategoryId' => $articleInfo['class_id'],
                    // 被推荐数据的原始数据 Id
                    'dataId' => $articleInfo['article_id'],
                ];
                break;
        }

        return $param_arr;
    }


    /**
     * 创建数据标识
     * @author zhonglei
     * @return string
     */
    public function buildDataID()
    {
        $data_id = md5(sprintf('%s_%s_%s', QY_DOMAIN, APP_DIR, MILLI_TIME));
        $count = $this->count_by_conds(['data_id' => $data_id]);

        if ($count == 0) {
            return $data_id;
        }

        return $this->buildDataID();
    }

}
