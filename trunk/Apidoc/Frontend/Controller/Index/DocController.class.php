<?php
/**
 * 主页
 */
namespace Frontend\Controller\Index;

use Common\Common\ApiDoc;
use Common\Service\MethodService;

class DocController extends AbstractController
{

    /**
     * @return bool
     */
    public function Index()
    {

        $method = I('get.method');
        if (empty($method)) {
            die('缺少参数: method');
        }

        $file = I('get.file');
        $methodService = new MethodService();
        $fileData = $methodService->makeFile($file);

        list($className, $methodName) = explode('.', $method);

        // 包含文件
        $fileMethods = @include_once($fileData['fileName']);
        // 通过类名获取方面数据
        $methodService->getMethodData($fileData['className'], $file, $fileMethods);

        // 获取返回结果
        $rMethod = new \ReflectionMethod($className, $methodName);
        $docComment = $rMethod->getDocComment();
        // 解析注释
        $comments = ApiDoc::instance()->parseComment($docComment);

        // 生成返回数据格式
        $returnExample = array(
            'errcode' => 0,
            'errmsg' => 'ok',
            'requestId' => '',
            'errsdkcode' => '',
            'timestamp' => NOW_TIME,
            'result' => ApiDoc::instance()->createParamExample($comments['return'])
        );
        $paramExample = ApiDoc::instance()->createParamExample($comments['param']);

        $this->assign('comments', $comments);
        $this->assign('paramExample', var_export($paramExample, true));
        $this->assign('paramJson', rjson_encode($paramExample, JSON_PRETTY_PRINT));
        $this->assign('returnExample', var_export($returnExample, true));
        $this->assign('returnJson', rjson_encode($returnExample, JSON_PRETTY_PRINT));
        $this->assign('method', $method);
        $this->assign('file', $file);
        $this->assign('apiUrl', $methodService->file2api($file, $methodName));

        $this->_output('Index/Doc');
        return true;
    }

}
