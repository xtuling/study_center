<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/21
 * Time: 10:19
 */

namespace Api\Controller\Doc;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\RpcFavoriteHelper;
use Common\Service\FileService;
use Common\Service\RightService;

class ListController extends \Api\Controller\AbstractController
{
    /**
     * List
     * @author tangxingguo
     * @desc 文件列表接口
     * @param Int file_id 文件ID（根目录传0）
     * @param String file_name 文件名称关键字
     * @return array
                  array(
                      'file_id' => 2, // 当前文件夹ID
                      'paths' => array( // 文件夹路径列表
                          'file_name' => '吃喝玩乐', // 文件夹名称
                          'file_id' => 1, // 文件夹ID
                      ),
                      'total_file' => 10, // 文件总数
                      'list' => array( // 文件列表
                          'file_id' => 123, // 文件ID
                          'file_name' => '001.pdf', // 文件名称
                          'file_type' => 2, // 文件类型（1=文件夹；2=文件）
                          'at_id' => '8765432345', // 附件ID
                          'at_size' => '123141', // 附件尺寸（单位字节）
                          'at_suffix' => 'pdf', // 文件尾缀
                          'update_time' => 1493264288000, // 最后更新时间
                          'my_is_favorite' => 1, // 我是否收藏（1=未收藏，2=已收藏）
                          'my_is_download' => 1, // 我是否可下载（1=不可下载，2=可下载）
                      ),
                  );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'file_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;
        $postData['file_name'] = I('post.file_name', '', 'trim');
        $user = $this->_login->user;

        // 当前目录信息
        $fileServ = new FileService();
        // 目录下载权限
        $folderDownload = true;
        if ($postData['file_id'] != 0) {
            $file = $fileServ->get($postData['file_id']);
            if (empty($file)) {
                E('_ERR_FILE_DATA_IS_NULL');
            }

            // 文件路径列表
            $paths = $fileServ->getPaths($postData['file_id']);

            // 下载被关闭
            if ($file['is_download'] != Constant::FILE_DOWNLOAD_RIGHT_ON) {
                $folderDownload = false;
            }
        } else {
            $paths = [];
        }

        // 获取人员可查阅的目录列表
        $rightServ = new RightService();
        $readFileList = [0];
        $readList = $rightServ->getReadList($user);
        if (!empty($readFileList)) {
            $readFileList = array_merge($readFileList, array_column($readList, 'file_id'));
        }
        if (!in_array($postData['file_id'], $readFileList)) {
            E('_ERR_FILE_NOT_RIGHT');
        }

        // 获取人员可下载的目录列表
        $downloadList = $rightServ->getDownloadList($user);
        $downloadFileList = [0];
        if (!empty($downloadFileList)) {
            $downloadFileList = array_merge($downloadFileList, array_column($downloadList, 'file_id'));
        }

        // 目录列表数据
        $conds = [
            'is_show' => Constant::FILE_STATUS_IS_SHOW,
            'file_status' => Constant::FILE_STATUS_NORMAL,
        ];
        if (strlen($postData['file_name']) > 0) {
            $conds['file_name like ?'] = '%' . $postData['file_name'] . '%';
        } else {
            $conds['parent_id'] = $postData['file_id'];
        }
        // 排序：首先文件夹，文件更新时间倒叙
        $orders = [
            '`order`' => 'asc',
            'file_type' => 'asc',
            'update_time' => 'desc'
        ];
        $list = $fileServ->list_by_conds($conds, [], $orders);

        // 处理数据
        $rpcFavorite = &RpcFavoriteHelper::instance();
        foreach ($list as $k => $v) {
            switch ($v['file_type']) {
                // 文件夹根据权限判断是否展示
                case Constant::FILE_TYPE_IS_FOLDER:
                    $list[$k]['my_is_download'] = $v['is_download'];
                    if (!in_array($v['file_id'], $readFileList)) {
                        unset($list[$k]);
                    }
                    break;
                // 文件添加是否可下载、文件后缀返回，人员是否有文件对应目录查阅权限
                case Constant::FILE_TYPE_IS_DOC:
                    // 是否可下载
                    $my_is_download = in_array($v['parent_id'], $downloadFileList) && $folderDownload ? Constant::FILE_MY_DOWNLOAD_RIGHT_YES : Constant::FILE_MY_DOWNLOAD_RIGHT_NO;
                    $list[$k]['my_is_download'] = $my_is_download;
                    $list[$k]['at_suffix'] = end(explode('.', $v['file_name']));
                    // 父级目录不在可查阅列表，删除文件
                    if (!in_array($v['parent_id'], $readFileList)) {
                        unset($list[$k]);
                    }

                    // 文件url加入鉴权参数
                    $list[$k]['at_url'] .= '&_id=' . $v['file_id'];
                    $list[$k]['at_convert_url'] .= $v['at_convert_url'] ? '&_id=' . $v['file_id'] : '';
                    break;
            }

            // 跳过不可见的文件夹或文件
            if (!isset($list[$k])) {
                continue;
            }

            // RPC查询收藏结果
            $param = [
                'uid' => $user['memUid'],
                'dataId' => $v['file_id'],
            ];
            $status = $rpcFavorite->getStatus($param);
            $list[$k]['my_is_favorite'] = Constant::FILE_MY_FAVORITE_NO;
            if (isset($status['collection']) && $status['collection'] == RpcFavoriteHelper::COLLECTION_YES) {
                $list[$k]['my_is_favorite'] = Constant::FILE_MY_FAVORITE_YES;
            }
        }

        $this->_result = [
            'file_id' => $postData['file_id'],
            'paths' => $paths,
            'total_file' => empty($list) ? 0 : count($list),
            'list' => empty($list) ? [] : array_values($list),
        ];
    }
}
