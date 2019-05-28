<?php
namespace app\index\controller;

use app\admin\controller\image;
use app\admin\model\article;
use app\admin\model\category;
use app\admin\model\images;
use think\Controller;

class Index extends Controller
{
    public function news(){

        $id = $this->request->param('id',0);
        $this->assign('id',$id);

        $category = $this->categoryList(1);

        $categories = [];
        foreach ($category as $v){
            $categories[] = $v['id'];
        }

        if ($id){
            $categoryInfo = category::where('id',$id)->find();

            $this->assign('categoryInfo',$categoryInfo);
            $list = article::where('category_id',$id)->where('status',1)
                ->order('create_time desc')->paginate(1);
        }else{
            $this->assign('categoryInfo','');
            $list = article::where('category_id','in',$categories)->where('status',1)
                ->order('create_time desc')->paginate(1);
            print_r($categories);
        }

        $this->assign('list',$list);
        return $this->fetch();


    }

    public function categoryList($id){
        $category = category::where('pid',$id)->select();
        $this->assign('category',$category);
        return $category;
    }

    public function detail(){
        $category = $this->categoryList(1);

        $id = $this->request->param('id');
        $info = article::get($id);
        $this->assign('info',$info);

        $info->setInc('hist');

        return $this->fetch();
    }

    public function about(){
        $id = $this->request->param('id');

        $this->categoryList(4);

        $info = article::where('category_id',$id)->find();

        $this->assign('info',$info);

        $this->assign('id',$id);

        return $this->fetch();
    }

    public function idea(){

        $id = $this->request->param('id',11);
        $this->assign('id',$id);
        $category = $this->categoryList(10);
        if (empty($id)){
            $where = [];
        }else{
            $where['category_id'] = $id;
        }
        $list = images::where($where)->select();
        $this->assign('list',$list);
        $categoryList = category::where('type',2)->select();

        $this->assign('categoryList',$categoryList);
        $images = images::where('category_id',$id)->all();
        $this->assign('images',$images);
        return $this->fetch();

    }
}
