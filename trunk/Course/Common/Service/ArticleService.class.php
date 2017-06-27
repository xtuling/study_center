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
use Common\Model\ArticleModel;
use Common\Common\Msg;

class ArticleService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new ArticleModel();
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

    /**
     * @desc 课程新闻推荐
     * @author tangxingguo
     * @param int $articleId 课程ID
     * @return mixed
     */
    public function addCourseRpc($articleId)
    {
        // 草稿不推送
        $articleInfo = $this->get($articleId);
        if ($articleInfo['article_status'] == Constant::ARTICLE_STATUS_DRAFT) {
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
     * @param int $articleId 课程ID
     * @param array $articleInfo 更新的课程信息
     * @return mixed
     */
    public function updateCourseRpc($articleId, $articleInfo)
    {
        // 草稿不推送
        if ($articleInfo['article_status'] == Constant::ARTICLE_STATUS_DRAFT) {
            return false;
        }

        // 将课程ID，数据标识加入最新的课程信息
        $info = $this->get($articleId);
        if (empty($info)) {
            return false;
        }
        $articleInfo['article_id'] = $articleId;
        $articleInfo['data_id'] = $info['data_id'];

        // 格式化RPC参数
        $param_arr = $this->formatRpcParams($articleInfo, 1);

        // 推送
        $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Recommender/ArticleUpdate');
        $res = Rpc::phprpc($url)->invoke('Index', $param_arr);

        return $res;
    }

    /**
     * @desc 删除课程推荐
     * @author tangxingguo
     * @param int $articleId 课程ID
     * @return mixed
     */
    public function delCourseRpc($articleId)
    {

        $articleInfo = $this->get($articleId);
        if (empty($articleInfo)) {
            return false;
        }

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
     * @param array $articleInfo 课程信息
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
                    'app'=>'Course',
                    // 被推荐数据所属的分类Id，可以为空，但必须提供该参数
                    'dataCategoryId' => $articleInfo['class_id'],
                    // 被推荐数据的原始数据 Id
                    'dataId' => $articleInfo['article_id'],
                    // 文章标题
                    'title' => $articleInfo['article_title'],
                    // 文章摘要
                    'summary' => $articleInfo['summary'],
                    // 封面图片附件 ID
                    'attachId' => $articleInfo['cover_id'],
                    // 封面图片 url
                    'pic' => $articleInfo['cover_url'],
                    // 文章链接
                    'url' => 'Course/Frontend/Index/Detail/Index?article_id=' . $articleInfo['article_id'] . '&data_id=' . $articleInfo['data_id'] . '&article_type=' . $articleInfo['article_type'],
                    // 文章发布时间戳，不设置或者为空，则以推荐时间为准
                    'dateline' => $articleInfo['update_time'],
                ];
                break;
            // 删除
            case 2:
                $param_arr = [
                    // 被推荐数据所在应用模块目录标识名
                    'app'=>'Course',
                    // 被推荐数据所属的分类Id，可以为空，但必须提供该参数
                    'dataCategoryId' => $articleInfo['class_id'],
                    // 被推荐数据的原始数据 Id
                    'dataId' => $articleInfo['article_id'],
                ];
                break;
        }

        return $param_arr;
    }
}
