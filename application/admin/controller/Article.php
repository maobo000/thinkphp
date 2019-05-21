<?php
/**
 * Created by PhpStorm.
 * User: qingyun
 * Date: 19/5/21
 * Time: 下午5:11
 */

namespace app\admin\controller;



use app\admin\model\category;
use think\Controller;

class Article extends Controller
{
    public function add()
    {
        $res = $this->request;

        if ($res->isPost()){
            $data = $res->only(['title', 'category_id', 'anthor', 'content', 'status']);

            $rule = [
                'title' => 'require|length:1,50',
                'category_id' => 'require|min:1',
                'author' => 'length:2,10',
                'content' => 'require|length:10,65535',
                'status' => 'in:0,1'
            ];

            $msg = [
                'title.require' => '文章标题为必填项',
                'title.length' => '文章标题应在1-50字之间',
                'category_id.require' => '请选择正确的分类信息',
                'category_id.min' => '请选择正确的分类信息',
                'author.length' => '署名长度应在2-10个字之间',
                'content.require' => '文章内容为必填项',
                'content.length' => '文章内容过短或者过长',
                'status.in' => '文章状态有误'
            ];

            $check = $this->validate($data, $rule, $msg);

            if ($check !== true) {
                $this->error($check);
            }
            $data['aid'] = session('adminLoginInfo')->id;

            if (\app\admin\model\article::create($data)) {
                $this->success('添加成功', url('admin/Article/lists'));
            } else {
                $this->error('添加失败');
            }

        }


        if ($res->isGet()) {
            $all = category::where('pid', 0)->all();
            $this->assign('all', $all);
            return $this->fetch();
        }


    }

    public function ajaxCategory(){
        $pid = $this->request->param('id',0);
        $data = category::where('pid',$pid)->select();
        return json($data);
    }

    public function lists(){
        $list =\app\admin\model\article::with('category')->order('create_time DESC')->paginate(2);
        $this->assign('list',$list);
        return $this->fetch();
    }


    public function changeStatus(){
        $id =$this->request->param('id');
        if (empty($id)){
            return $this->error('非法操作');
        }

        $object = \app\admin\model\article::get($id);

        if (empty($object)){
            return $this->error('非法操作');
        }

        $object->status = abs($object->status - 1);

        if ($object->save()){
            return $this->success('成功','',$object->status);

        }else{
            return $this->error('失败');
        }
    }
}
