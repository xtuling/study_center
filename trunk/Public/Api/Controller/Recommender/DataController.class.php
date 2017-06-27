<?php
/**
 * DataController.class.php
 * 推荐数据获取
 * @author Deepseath
 * @version $Id$
 */
namespace Api\Controller\Recommender;

use Common\Model\CommonRecommenderModel;
use Common\Service\CommonRecommenderService;

class DataController extends AbstractController
{

    /**
     * 获取数据的条数
     * @var int
     */
    private $__limit = 6;

    /**
     * 当前获取数据开始行数
     * @var int
     */
    private $__start = 0;

    /**
     * 当前获取的数据类型
     * @see \Common\Model\CommonRecommenderModel
     * @var int
     */
    private $__type = 0;

    /**
     * 构造方法
     */
    public function __construct()
    {
        // 当前请求的类型
        $type = I('type', 0, 'intval');
        // 当前请求的条数
        $limit = I('limit', 6, 'intval');
        // 当前请求的页码
        $page = I('page', 1, 'intval');

        // 定义有效的数据类型列表
        $this->_recommenderService = new CommonRecommenderService();
        // 规范请求类型值
        if ($this->_recommenderService->is_type($type)) {
            $this->__type = $type;
        }

        // 规范请求的数据条数
        if ($limit >= CommonRecommenderModel::LIMIT_MIN && $limit <= CommonRecommenderModel::LIMIT_MAX) {
            $this->__limit = $limit;
        }

        // 请求数据开始的行号
        if ($page > 1) {
            $this->__start = ($page - 1) * $this->__limit;
        }
    }

    /**
     * 获取推荐数据
     * @desc 可获取指定类型的推荐数据用于前端展示
     *
     * @param string type:false:0 获取的数据类型：0=全部；1=banner；2=icon；3=内容推荐
     * @param int limit:false:6 获取的数据条数
     * @param int page:false:1 获取数据的页码，<strong style="color: red">注意：设置该值后，type 参数值不能为0</strong>
     * @return <pre>
     * array(
     *  'banner' => array(
     *      'total' => 9,// 数据总数
     *      'pages' => 2, // 总页码
     *      'list' => array(
     *          array(
     *              'title' => '标题文字', // 标题
     *              'pic' => 'http://img', // 图片 URL
     *              'url' => '/new/html/', // 链接地址
     *              'time' => 1494406601000, // 发布时间戳
     *              'key' => '9f80d3441314eb53856d8ebb42e60a30' // 系统数据唯一键值，用于前端标记之用
     *          ),
     *          array()
     *      )
     *  ),
     *  'icon' => array(
     *      'total' => 9,// 数据总数
     *      'pages' => 2, // 总页码
     *      'list' => array(
     *          array(
     *              'title' => '标题文字', // 标题
     *              'pic' => 'http://img', // 图片 URL
     *              'url' => '/new/html/', // 链接地址
     *              'time' => 1494406601000, // 发布时间戳
     *              'key' => '9f80d3441314eb53856d8ebb42e60a30' // 系统数据唯一键值，用于前端标记之用
     *          ),
     *          array()
     *      )
     *  ),
     *  'article' => array(
     *      'total' => 9,// 数据总数
     *      'pages' => 2, // 总页码
     *      'list' => array(
     *          array(
     *              'title' => '标题文字', // 标题
     *              'pic' => 'http://img', // 图片 URL
     *              'url' => '/new/html/', // 链接地址
     *              'time' => 1494406601000, // 发布时间戳
     *              'key' => '9f80d3441314eb53856d8ebb42e60a30' // 系统数据唯一键值，用于前端标记之用
     *          ),
     *          array()
     *      )
     *  )
     * )
     * </pre>
     */
    public function Index()
    {

        // 遍历所有类型来获取数据
        foreach ($this->_recommenderService->typeList as $_type) {
            if ($this->__type && $_type != $this->__type) {
                // 如果指定了获取具体类型的数据，且不是当前进程的类型，则忽略
                continue;
            }
            // 取出指定类型的数据列表
            $data = $this->_recommenderService->listByType($_type, CommonRecommenderModel::HIDE_NO, $this->__start, $this->__limit);
            $this->_result[$_type] = $this->__format($data);
        }
        unset($_type);

        return $this->_result;
    }

    /**
     * 格式化列表数据
     *
     * @param array $data
     */
    private function __format($data)
    {
        $result = [
            'type' => $data['type'],
            'total' => $data['total'],
            'pages' => $data['pages'],
            'list' => []
        ];
        foreach ($data['list'] as $_data) {
            $result['list'][] = $this->_recommenderService->format($_data);
        }

        return $result;
    }
}
