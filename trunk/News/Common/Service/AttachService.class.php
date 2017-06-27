<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/12
 * Time: 10:34
 */
namespace Common\Service;

use Common\Common\Attach;
use Common\Common\Constant;
use Common\Model\AttachModel;
use VcySDK\Service;
use VcySDK\FileConvert;

class AttachService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new AttachModel();
    }

    /**
     * 格式化数据
     * @author liyifei
     * @param int $article_id 新闻ID
     * @return array
     */
    public function formatData($article_id)
    {
        $attachList = $this->list_by_conds(['article_id' => $article_id]);
        if (empty($attachList)) {
            return [];
        }

        $returnData = [];
        foreach ($attachList as $attach) {
            // 截取文件后缀
            $suffix = explode('.', $attach['at_name']);
            $count = count($suffix);
            $at_suffix = $suffix[$count - 1];

            $formatData = [
                'at_id' => $attach['at_id'],
                'at_time' => $attach['at_time'],
                'at_name' => $attach['at_name'],
                'at_size' => $attach['at_size'],
                'at_url' => $attach['at_url'] ? $attach['at_url'] . '&_id=' . $article_id : '',
                'at_convert_url' => $attach['at_convert_url'] ? $attach['at_convert_url'] . '&_id=' . $article_id : '',
                'at_suffix' => $at_suffix,
            ];

            switch ($attach['at_type']) {
                case Constant::ATTACH_TYPE_VIDEO:
                    $returnData['at_video'] = $formatData;
                    break;

                case Constant::ATTACH_TYPE_AUDIO:
                    $returnData['at_audio'] = $formatData;
                    break;

                case Constant::ATTACH_TYPE_FILE:
                    $returnData['at_file'][] = $formatData;
                    break;
            }
        }

        return $returnData;
    }

    /**
     * 格式化数据库中的附件数据
     * @author zhonglei
     * @param array $attachs 附件数据
     * @return array
     */
    public function formatDBData($attachs)
    {
        $data = [];

        // 数据分组
        foreach ($attachs as $attach) {
            $data[$attach['at_type']][] = $attach;
        }

        return $data;
    }

    /**
     * 格式化用户输入的附件数据
     * @author zhonglei
     * @param array $attachs 附件数据
     * @return array
     */
    public function formatPostData($attachs)
    {
        $data = [];

        if (!is_array($attachs) || empty($attachs)) {
            return $data;
        }

        // 数据分组
        foreach ($attachs as $k => $v) {
            if (empty($v)) {
                continue;
            }

            switch ($k) {
                // 视频
                case 'video_at_info':
                    if (!is_array($v) || !isset($v['at_id'], $v['at_name'], $v['at_size'])) {
                        break;
                    }

                    $data[Constant::ATTACH_TYPE_VIDEO] = [
                        $v['at_id'] => [
                            'at_id' => $v['at_id'],
                            'at_name' => $v['at_name'],
                            'at_size' => $v['at_size'],
                        ],
                    ];
                    break;

                // 音频
                case 'audio_at_id':
                    $data[Constant::ATTACH_TYPE_AUDIO] = [
                        $v => [
                            'at_id' => $v,
                        ],
                    ];
                    break;

                // 其它文件
                case 'file_at_ids':
                    if (!is_array($v)) {
                        break;
                    }

                    foreach ($v as $at_id) {
                        $data[Constant::ATTACH_TYPE_FILE][$at_id] = [
                            'at_id' => $at_id,
                        ];
                    }
                    break;
            }
        }

        return $data;
    }

    /**
     * 比较附件数据，并返回需要新增和删除的数据
     * @author zhonglei
     * @param array $attachs_db 数据库中的附件数据
     * @param array $attachs_post 用户输入的附件数据
     * @return array
     */
    public function diffData($attachs_db, $attachs_post)
    {
        $keys = [
            Constant::ATTACH_TYPE_VIDEO,
            Constant::ATTACH_TYPE_AUDIO,
            Constant::ATTACH_TYPE_FILE,
        ];

        // 待删除附件ID数组
        $dels = [];

        // 待新增数据，$adds[附件类型][附件数据]
        $adds = [];

        // 遍历所有附件类型
        foreach ($keys as $key) {
            // 取出at_id
            $attachs_old = isset($attachs_db[$key]) ? array_column($attachs_db[$key], 'at_id') : [];
            $attachs_new = isset($attachs_post[$key]) ? array_keys($attachs_post[$key]) : [];

            if (!empty($attachs_old) || !empty($attachs_new)) {
                // 待删除数据
                $dels = array_merge($dels, array_diff($attachs_old, $attachs_new));

                // 待新增附件ID
                $at_ids = array_diff($attachs_new, $attachs_old);

                foreach ($at_ids as $at_id) {
                    $adds[$key][$at_id] = $attachs_post[$key][$at_id];
                }
            }
        }

        return [$dels, $adds];
    }

    /**
     * 保存附件数据
     * @author zhonglei
     * @param int $article_id 新闻ID
     * @param array $data 附件数组
     * @return bool
     */
    public function saveData($article_id, $data)
    {
        $list = $this->list_by_conds(['article_id' => $article_id]);
        $attachs_db = $this->formatDBData($list);
        $attachs_post = $this->formatPostData($data);
        list($dels, $adds) = $this->diffData($attachs_db, $attachs_post);

        // 删除附件
        if (!empty($dels)) {
            $this->delete_by_conds(['article_id' => $article_id, 'at_id' => $dels]);
        }

        // 新增附件
        foreach ($adds as $k => $data) {
            switch ($k) {
                // 视频不做处理
                case Constant::ATTACH_TYPE_VIDEO:
                    break;

                // 音频、文件从UC获取文件名称、文件大小、Url
                case Constant::ATTACH_TYPE_AUDIO:
                case Constant::ATTACH_TYPE_FILE:
                    $at_ids = array_keys($data);
                    $attachServ = &Attach::instance();
                    $file_list = $attachServ->listAll($at_ids);

                    foreach ($file_list as $at_id => $file) {
                        if (isset($data[$at_id])) {
                            $data[$at_id]['at_name'] = $file['atFilename'];
                            $data[$at_id]['at_time'] = $file['atDuration'];
                            $data[$at_id]['at_size'] = $file['atFilesize'];
                            $data[$at_id]['at_url'] = $file['atAttachment'];
                        }
                    }
                    break;
            }

            $convertServ = new FileConvert(Service::instance());

            // 保存数据
            foreach ($data as $at_id => $v) {
                $v['article_id'] = $article_id;
                $v['at_type'] = $k;

                // 保存成功，文件转换
                if ($this->insert($v) && $k == Constant::ATTACH_TYPE_FILE) {
                    $convertServ->convert([
                        'atIds' => [$at_id],
                        'convertType' => FileConvert::CONVERT_TYPE_HTML,
                        'high' => FileConvert::CONVERT_IS_HIGH_TRUE,
                    ]);
                }
            }
        }

        return true;
    }
}
