<?php
/**
 * Created by PhpStorm.
 * User: qingyun
 * Date: 19/5/21
 * Time: 下午5:11
 */

namespace app\admin\controller;

use app\admin\model\admin;
use app\admin\model\category;
use think\Controller;

class Article extends Controller
{
    public function add()
    {
        $res = $this->request;
        if ($res->isPost()) {
            //过度参数
            $data = $res->only(['title', 'category_id', 'author', 'content', 'status','thumb','minthumb']);
            //规则
            $rule = [
                'title' => 'require|length:1,50',
                'category_id' => 'require|min:1',
                'author' => 'length:2,10',
                'content' => 'require|length:10,65535',
                'status' => 'in:0,1'
            ];
            //错误信息
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
            //验证内容释放符合规则
            $check = $this->validate($data, $rule, $msg);
            //报出错误提示
            if ($check !== true) {
                $this->error($check);
            }


            $data['aid'] = session('adminLoginInfo')->id;


            //入库保存
            if (\app\admin\model\article::create($data)) {
                $this->success('添加成功', url('admin/Article/lists'));
            } else {
                $this->error('添加失败');
            }

        }

        //get 请求
        if ($res->isGet()) {
            $all = category::where('pid', 0)->all();
            $this->assign('all', $all);
            return $this->fetch();
        }


    }

    public function ajaxCategory()
    {
        //获取id=0时的内容 输出值改为字符串
        $pid = $this->request->param('id', 0);
        $data = category::where('pid', $pid)->select();
        return json($data);
    }

    public function lists()
    {
        //从库里查询内容
        $list = \app\admin\model\article::with('category')->order('create_time DESC')->paginate(2);

        //接受库里的内容并显示
        $this->assign('list', $list);
        return $this->fetch();
    }


    public function changeStatus()
    {
        //获取id
        $id = $this->request->param('id');
        // 如果id为空  就报错
        if (empty($id)) {
            return $this->error('非法操作');
        }
        //重新获取id的值
        $object = \app\admin\model\article::get($id);

        // 如果id为空  就报错
        if (empty($object)) {
            return $this->error('非法操作');
        }

        $object->status = abs($object->status - 1);

        if ($object->save()) {
            return $this->success('成功', '', $object->status);

        } else {
            return $this->error('失败');
        }
    }


    public function delete()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('失败');
        }
        //从库里查询并删除
        $a = \think\Db::table('article')->where('id', $id)->delete();
        if(empty($a)) {
            return $this->error('失败');
        }else{
            return $this->success('成功');
        }
    }

    public function update()
    {
        $re = $this->request;
        if ($re->isPost()) {
            $id = $this->request->param('id');
            $data = $re->only(['title','author', 'content']);
            $rule = [
                'title' => 'require|length:1,50',
//                'category_id' => 'require|min:1',
                'author' => 'length:2,10',
                'content' => 'require|length:10,65535',
//                'status' => 'in:0,1'
            ];
            $msg = [
                'title.require' => '文章标题为必填项',
                'title.length' => '文章标题应在1-50字之间',
//                'category_id.require' => '请选择正确的分类信息',
//                'category_id.min' => '请选择正确的分类信息',
                'author.length' => '署名长度应在2-10个字之间',
                'content.require' => '文章内容为必填项',
                'content.length' => '文章内容过短或者过长',
//                'status.in' => '文章状态有误'
            ];

            $check = $this->validate($data, $rule, $msg);
            if ($check !== true) {
                $this->error($check);
            }
            $cctv = \app\admin\model\article::get($id);
            if ($cctv->save($data)) {
                $this->success('修改成功',url('admin/Article/lists'));
            } else {
                $this->error('修改失败2');
            }

        }
            if ($re->isGet()) {

                $id = $this->request->param('id');
//                $b = \think\Db::table('article')->where('id', $id)->find();

                $list = \app\admin\model\article::get($id)->toArray();
                $this->assign('list', $list);
                return $this->fetch();
            }
    }


    public function ueUploadImg(){

        if ($this->request->isGet()){

            $configData = file_get_contents("static/ui/library/ue/php/config.json");
//            $config = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", $configData), true);

            $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", $configData), true);
            return json_encode($CONFIG);
        }
        if ($this->request->isPost()){
            $image = $this->request->file('upfile');
            $res = $image->validate(['size'=>1048576, 'ext'=>'jpg,png,gif,jpeg'])->move('static/upload');
            if ($res){

                $info =  [
                    "originalName" => $res->getFilename() ,
                    "name" => $res->getSaveName() ,
                    "url" => '/'.$res->getPathname(),
                    "size" => $res->getSize() ,
                    "type" => $res->getExtension() ,
                    "state" => 'SUCCESS'
                ];

                return json_encode($info);
            }
        }

    }


    public function uploadImage(){

        $image = $this->request->file('file');


        $res = $image->validate(['size'=>1048576, 'ext'=>'jpg,png,gif,jpeg'])->move('static/upload/');

        if ($res){
            $path = $res->getPathname();
            $min = $res->getPath().'/min'.$res->getFilename();

            $m = \think\Image::open($path);
            $m->thumb(60, 60, \think\Image::THUMB_CENTER)->save($min);
            return json_encode(['code'=>1, 'thumb'=> $path, 'min'=> $min]);
        }else{
            return json_encode(['code'=>0, 'info'=>$image->getError()]);

        }
    }


}