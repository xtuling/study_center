<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/12
 * Time: 10:46
 */
namespace Common\Service;

use Common\Model\ArticleSourceModel;

class ArticleSourceService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new ArticleSourceModel();
    }

    /**
     * 保存课程素材关系
     * @author zhonglei
     * @param int $article_id 课程ID
     * @param array $source_ids_post 素材ID数组
     * @return bool
     */
    public function saveData($article_id, $source_ids_post)
    {
        $article_sources = $this->list_by_conds(['article_id' => $article_id]);
        $source_ids_db = array_column($article_sources, 'source_id');
        $ids_del = array_diff($source_ids_db, $source_ids_post);
        $ids_insert = array_diff($source_ids_post, $source_ids_db);

        // 删除数据
        if (!empty($ids_del)) {
            $this->delete_by_conds(['article_id' => $article_id, 'source_id' => $ids_del]);
        }

        $insert_data = [];

        // 新增数据
        foreach ($ids_insert as $v) {
            $insert_data[] = [
                'article_id' => $article_id,
                'source_id' => $v,
            ];
        }

        if (!empty($insert_data)) {
            $this->insert_all($insert_data);
        }

        return true;
    }
}
