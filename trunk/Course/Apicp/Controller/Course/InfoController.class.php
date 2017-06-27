<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/27
 * Time: 11:41
 */
namespace Apicp\Controller\Course;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\ArticleService;
use Common\Service\ArticleSourceService;
use Common\Service\ClassService;
use Common\Service\ExamService;
use Common\Service\RightService;
use Common\Service\SourceService;

class InfoController extends \Apicp\Controller\AbstractController
{
    /**
     * Info
     * @author tangxingguo
     * @desc 课程详情
     * @param int article_id:true 课程ID
     * @return array 课程详情
                    array(
                        'article_id' => 123, // 课程ID
                        'data_id' => 'b3ddbc502e307665f346cbd6e52cc10d', // 数据标识
                        'article_title' => '好好学', // 课程标题
                        'cm_id' => 1, // 能力模型ID
                        'cm_name' => '超能力', // 能力模型名称
                        'source' => array(// 素材
                            array(
                                'source_id' => 1,// 素材ID（数据库真实素材ID，接口交互都用这个）
                                'source_key' => 'N123456',// 素材标识（展示给用户看的‘素材ID’用这个字段）
                                'source_title' => '协议书',// 素材标题
                            ),
                        ),
                        'cover_id' => 'b3ddbc502e307665f346cbd6e52cc10d', // 封面图片ID
                        'cover_url' => 'http://qy.vchangyi.org', // 封面图片地址
                        'summary' => '零食增加卫龙系列', // 摘要
                        'class_id' => 2, // 分类ID
                        'class_name' => '内部课程', // 分类名称
                        'p_id' => 1, // 父类分类ID
                        'p_name' => '分类名', // 父类分类名称
                        'pp_id' => 2, // 父类的父类分类ID
                        'pp_name' => '内部课程', // 父类的父类分类名称
                        'ea_name' => '张三', // 创建者
                        'content' => '介绍介绍课程', // 课程介绍
                        'right' => array( // 权限
                            'is_all' => 1, // 是否全公司(1=否，2=是)
                            'tag_list' => array(// 标签
                                array(
                                    'tag_id' => '3CDBB2867F0000012C7F8D28432943BB',// 标签ID
                                    'tag_name' => 'liyifei001',// 标签名
                                ),
                            ),
                            'dp_list' => array(// 部门
                                array(
                                    'dp_id' => 'B65085507F0000017D3965FCB20CA747',// 部门ID
                                    'dp_name' => '一飞冲天',// 部门名
                                ),
                            ),
                            'user_list' => array(// 人员
                                array(
                                    'uid' => 'B4B3BA5B7F00000173E870DA6ADFEA2A',// 人员UID
                                    'username' => '缘来素雅',// 人员姓名
                                    'face' => 'http://shp.qpic.cn/bizmp/gdZUibR6BHrmiar6pZ6pLfRyZSVaXJicn2CsvKRdI9gccXRfP2NrDvJ8A/'// 人员头像
                                ),
                            ),
                            'job_list' => array(// 职位
                                array(
                                    'job_id' => '62C316437F0000017AE8E6ACC7EFAC22',// 职位ID
                                    'job_name' => '攻城狮',// 职位名称
                                ),
                            ),
                            'role_list' => array(// 角色
                                array(
                                    'role_id' => '62C354B97F0000017AE8E6AC4FD6F429',// 角色ID
                                    'role_name' => '国家元首',// 角色名称
                                ),
                            ),
                        ),
                        'is_secret' => 1, // 是否保密（1=不保密，2=保密）
                        'is_share' => 1, // 允许分享（1=不允许，2=允许）
                        'is_notice' => 1, // 消息通知（1=不开启，2=开启）
                        'is_comment' => 1, // 评论功能（1=不开启，2=开启）
                        'is_like' => 1, // 点赞功能（1=不开启，2=开启）
                        'is_recommend' => 1, // 首页推荐（1=不开启，2=开启）
                        'is_exam' => 1, // 是否开启测评（1=未开启；2=已开启）
                        'is_step' => 1, // 是否开启闯关（1=未开启；2=已开启）
                        'tags' => '哈哈 啊啊', // 课程标签
                        'et_list' => array( // 题目列表
                            array(
                                'et_id' => 123, // 题目ID
                                'title' => '送分题', // 题目名称
                                'et_type' => '选择题', // 题目类型
                            )
                        ),
                    )
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'article_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;
        $articleId = $postData['article_id'];

        // 取课程
        $articleServ = new ArticleService();
        $article = $articleServ->get($articleId);
        if (empty($article)) {
            E('_ERR_ARTICLE_DATA_NOT_FOUND');
        }

        // 取权限
        $rightServ = new RightService();
        $article['right'] = $rightServ->getData(['article_id' => $articleId]);

        // 取分类信息
        $parent = [];
        $this->_getParent($article['class_id'], $parent);
        $class = [
            'class_name' => '',
            'p_id' => '',
            'p_name' => '',
            'pp_id' => '',
            'pp_name' => '',
        ];
        foreach ($parent as $k => $v) {
            switch ($k) {
                // 当前分类信息
                case 0:
                    $class['class_name'] = $v['class_name'];
                    break;
                // 父类分类信息
                case 1:
                    $class['p_id'] = $v['class_id'];
                    $class['p_name'] = $v['class_name'];
                    break;
                // 父类的父类信息
                case 2:
                    $class['pp_id'] = $v['class_id'];
                    $class['pp_name'] = $v['class_name'];
                    break;
            }
        }
        $article = array_merge($article, $class);

        // 取素材
        $article['source'] = [];
        $articleSourceServ = new ArticleSourceService();
        // 排序
        $order_option = ['`order`' => 'asc'];
        $sourceList = $articleSourceServ->list_by_conds(['article_id' => $articleId], [], $order_option);
        if ($sourceList) {
            // 取素材ID
            $sourceIds = array_column($sourceList, 'source_id');
            // 取素材信息
            $sourceServ = new SourceService();
            $sources = $sourceServ->list_by_conds(['source_id in (?)' => $sourceIds]);
            // 排序
            if (!empty($article['source_ids'])) {
                $source_ids = unserialize($article['source_ids']);
                $sources = array_combine_by_key($sources, 'source_id');
                $temp = [];
                foreach ($source_ids as $source_id) {
                    $temp[] = isset($sources[$source_id]) ? $sources[$source_id] : [];
                }
                $sources = array_values($temp);
            }
            $article['source'] = $sources;
        }

        // 取考试题目
        if ($article['is_exam'] == Constant::ARTICLE_IS_CHECK_TRUE) {
            $etList = [];
            if (!empty($article['et_ids'])) {
                $examServ = new ExamService();
                $etIds = unserialize($article['et_ids']);
                $etList = $examServ->listById($etIds);
            }
            $article['et_list'] = $etList;
        }

        // RPC取能力模型信息
        $url = convertUrl(QY_DOMAIN . '/Contact/Rpc/Competence/Detail');
        $data = [
            'cm_id' => $article['cm_id'],
        ];
        $detail = \Com\Rpc::phprpc($url)->invoke('index', $data);
        if (!empty($detail) && is_array($detail)) {
            $article['cm_name'] = $detail['cm_name'];
        } else {
            $article['cm_name'] = '';
        }

        $this->_result = $article;
    }

    /**
     * 取父类分类信息
     * @param int $classId 分类ID
     * @param array $classInfo 分类信息
     */
    private function _getParent($classId, &$classInfo = [])
    {
        static $classList;
        if (empty(($classList))) {
            $classServ = new ClassService();
            $classList = $classServ->list_all();
            $classList = array_combine_by_key($classList, 'class_id');
        }

        $class = isset($classList[$classId]) ? $classList[$classId] : [];
        if (empty($class)) {
            return;
        }
        $classInfo[] = $class;
        if ($class['parent_id'] > 0) {
            $this->_getParent($class['parent_id'], $classInfo);
        } else {
            return;
        }
    }
}
