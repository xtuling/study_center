<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Apicp\Controller;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController
{
    protected $_require_login = false;
    /** 定义数据含义: 是 */
    const MEAN_TRUE = 1;
    /** 定义数据含义: 否 */
    const MEAN_FALSE = 2;

    /**
     * 提交的字段  [判断不断完善中... 2016-12-09 11:17:40]
     * 'require' => true, // 是否必填
     * 'default' => '', // 默认数据 (没有则 '')
     * 'verify' => 'strval', // 验证方法 (没有则 null)
     * 'cn' => '名称', // 字段名 (比如为空时的报错提示里用)
     * 'maxLength' => PrizeModel::MAX_NAME_COUNT // 最长限制
     * 'area' => [1, 2] // in_array 数据范围约束
     * 'regexp' => '' // 正则匹配
     */
    protected $field = [];
    // 提交的数据
    protected $data = [];
    // 使用自动获取验证方法
    protected $autoGetData = false;

    public function before_action($action = '')
    {
        $beforeAction = parent::before_action($action);

        if ($this->autoGetData) {
            // 自动获取提交值
            $this->getData();
            // 校验
            $this->verifyData();
        }

        return $beforeAction;
    }

    /**
     * 验证数据
     * @return bool
     */
    protected function verifyData()
    {
        $error = [];
        foreach ($this->field as $name => $todo) {
            // 判断长度限制
            if (isset($todo['maxLength']) && !empty($this->data[$name]) &&
                (is_array($this->data[$name]) ? count($this->data[$name]) : mb_strlen($this->data[$name], 'utf-8')) > $todo['maxLength']) {
                $error['maxLength'][] = $todo['cn'] . '(' . $todo['maxLength'] . ')';
                continue;
            }
            // 判断数据范围
            if (isset($todo['area']) && !empty($this->data[$name]) &&  !in_array($this->data[$name], $todo['area'])) {
                $error['area'][] = $todo['cn'];
                continue;
            }
            // 正则验证
            if (isset($todo['regexp']) && !empty($this->data[$name]) && !preg_match($todo['regexp'], $this->data[$name])) {
                $error['regexp'][] = $todo['cn'];
                continue;
            }
        }

        // 为了合并报错
        if (!empty($error['maxLength'])) {
            E(L('_ERR_OVER_MAX_COUNT', ['name' => implode(',', $error['maxLength'])]));
            return false;
        }
        if (!empty($error['area'])) {
            E(L('_ERR_OVER_DATA_AREA', ['name' => implode(',', $error['area'])]));
            return false;
        }
        if (!empty($error['regexp'])) {
            E(L('_ERR_OVER_DATA_REGEXP', ['name' => implode(',', $error['regexp'])]));
            return false;
        }

        return true;
    }

    /**
     * 获取提交数据
     * @return array|bool
     */
    protected function getData()
    {
        $emptyKey = [];
        foreach ($this->field as $name => $todo) {
            // 获取提交数据
            $data = I('post.' . $name,
                !empty($todo['default']) ? $todo['default'] : '',
                !empty($todo['verify']) ? $todo['verify'] : null);
            // 验证必填项
            if (empty($data) && !isset($todo['default'])) {
                if ($todo['require']) {
                    $emptyKey[] = $todo['cn'];
                }
                continue;
            }
            $this->data[$name] = $data;
        }

        if (!empty($emptyKey)) {
            E(L('_ERR_CANNOT_EMPTY', ['name' => implode('|', $emptyKey)]));
            return false;
        }

        return true;
    }

    /**
     * 获取分页数据
     * @param String $pageKey 当前页的键值名
     * @param String $limitKey 每页数量的键值名
     * @return array
     * + 开始数
     * + 每页数量
     */
    protected function getPageOption($pageKey, $limitKey)
    {
        list($start, $perpage, $page) = page_limit($this->data[$pageKey], $this->data[$limitKey]);
        unset($this->data[$pageKey]);
        unset($this->data[$limitKey]);

        return [$start, $perpage, $page];
    }
}
