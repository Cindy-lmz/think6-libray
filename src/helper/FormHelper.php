<?php
declare (strict_types=1);

namespace think\admin\helper;

use think\admin\Helper;
use think\db\Query;

/**
 * 表单视图管理器
 * Class FormHelper
 * @package think\admin\helper
 */
class FormHelper extends Helper
{

    /**
     * 逻辑器初始化
     * @param string|Query $dbQuery
     * @param string $template 模板名称
     * @param string $field 指定数据主键
     * @param array $where 额外更新条件
     * @param array $data 表单扩展数据
     * @return array|boolean|mixed|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function init($dbQuery, string $template = '', string $field = '', array $where = [], array $data = [])
    {
        $query = $this->buildQuery($dbQuery);
        $field = $field ?: ($query->getPk() ?: 'id');
        $value = input($field, $data[$field] ?? null);
        if ($this->app->request->isGet()) {
            if ($value !== null) {
                $find = $query->where([$field => $value])->where($where)->find();
                if (!empty($find) && is_array($find)) $data = array_merge($data, $find);
            }
            if (false !== $this->class->callback('_form_filter', $data)) {
                // $this->class->fetch($template, ['vo' => $data]);
                $this->class->success($template.'信息请求成功',['vo' => $data]);
            } else {
                return $data;
            }
        } elseif ($this->app->request->isPost()) {
            $data = array_merge($this->app->request->post(), $data);
            if (false !== $this->class->callback('_form_filter', $data, $where)) {
                $result = data_save($query, $data, $field, $where) !== false;
                if (false !== $this->class->callback('_form_result', $result, $data)) {
                    if ($result !== false) {
                        $this->class->success('恭喜, 数据保存成功！',$result);
                    } else {
                        $this->class->error('抱歉，数据保存失败, 请稍候再试！','',1010);
                    }
                }
                return $result;
            }
        }
    }

}
