<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Apicp\Controller\Editor;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController
{
    /**
     * 重写输出方法
     *
     * @param mixed  $data 输出数据
     * @param string $type 输出类型
     * @param int    $code 返回状态
     *
     * @see \Think\Controller\RestController::_response()
     */
    protected function _response($data = null, $type = 'json', $code = 200)
    {
        $callback = I('get.callback', '', 'trim');
        $result = null;

        if (strlen($callback) > 0) {
            if (preg_match('/^[\w_]+$/', $callback)) {
                $result = sprintf('%s(%s)', htmlspecialchars($callback), json_encode($this->_result));
            } else {
                $result = json_encode([
                    'state' => 'callback参数不合法',
                ]);
            }
        } else {
            $result = is_array($this->_result) ? json_encode($this->_result) : $this->_result;
        }

        $this->sendHttpStatus($code);
        $this->setContentType($type);
        exit($result);
    }
}
