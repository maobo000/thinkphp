<?php
/**
 * Created by PhpStorm.
 * User: qingyun
 * Date: 19/5/20
 * Time: 下午9:05
 */

namespace app\admin\controller;


use app\admin\model\category;

use think\Controller;

class Index extends  Controller
{
    public function index(){
        return $this->fetch();
    }

    public function console(){
        return $this->fetch();
    }

    public function addCategory()
    {
        $res = $this->request;
        if ($res->isGet()) {
            $pid = $res->param('id', 0);

            if (empty($pid)) {
                $this->assign('parentName', '顶级分类');
            } else {
                $parentName = category::where('id', $pid)->value('name');

                if (!$parentName) {
                    $this->error('非法操作');
                }

                $this->assign('parentName',$parentName);
            }
            $this->assign('pid', $pid);
            return $this->fetch();
        }

        if ($res->isPost()) {
            $name = $res->param('name');
            $pid = $res->param('pid', 0);

            if (mb_strlen($name, 'utf-8') > 10 || mb_strlen($name, 'utf-8') < 2) {
                $this->error('分类长度范围在2-10之间');
            }

            $where = ['pid' => $pid, 'name' => $name];
            if (category::where($where)->find()) {
                $this->error('该分类已存在');
            }

            if ($pid == 0) {
                $level = 0;
                $path = '0-';
            } else {
                $parent = category::where('id', $pid)->find();
                if (empty($parent)) {
                    $this->error('非法操作');
                }
                $level = $parent->level + 1;
                $path = $parent->path . $pid . '-';
            }
            $data = [
                'name'=>$name,
                'pid' => $pid,
                'level' => $level,
                'path' => $path,
                'type' => $res->param('type')
                ];
            if (category::create($data)) {
                $this->success('成功',url('admin/index/categoryList'));
            } else {
                $this->error('失败');
            }
        }
    }


    public function categoryList()
    {
        if ($this->request->isAjax()){
            $pid = $this->request->param('id', 0);
            $list = category::where('pid', $pid)->select();

            $str = '';
            foreach ($list as $v){
                $space = '';
                for ($i=0; $i< $v['level']; $i++){
                    $space .= '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
                $url = url('admin/Index/addCategory', ['id'=>$v['id']]);

                $tmp = explode('-', trim($v['path'], '0-'));
                $cls = '';
                foreach ($tmp as $vv){
                    $cls .= 'x'.$vv.' ';
                }

                $str .= <<<DDDD
                    <tr class="x{$pid}">
                        <td>{$v['id']}</td>
                        <td>{$space}|--{$v['name']}</td>
                        <td><a href="{$url}">添加</a></td>
                        <td><a data-id="{$v['id']}" class="point-e children" data-op="plus"><i class="fa fa-plus"></i></a></td>
                    </tr>
DDDD;
            }

            return $str;

        }else{
            $list = category::where('pid', 0)->select();
            $this->assign('list', $list);
            return $this->fetch();
        }
    }
    public function categoryTree()
    {
        $all = category::select()->toArray();

        $new = $this->toTree($all);

        $this->assign('data', json_encode($new));

        return $this->fetch();

    }
    protected function toTree($data, $pid = 0)
    {
        $newData = [];

        foreach ($data as $v){

            if ($v['pid']      == $pid){
                $v['text']     = $v['name'];
                $v['children'] = $this->toTree($data, $v['id']);
                $newData[]     = $v;
            }
        }
        return $newData;
    }

}