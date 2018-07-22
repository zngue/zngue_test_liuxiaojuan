<?php

namespace app\api\controller;
use think\Controller;
use think\File;
use think\Request;
use app\common\Api\UploadApi;


class Upload extends Controller
{


    //上传
  public function uploadImg(){
      $way = getSystemConfig('web_conf')['file_upload_type'];//图片上传方式
      if($way==1){//七牛云上传
        $this->qiniuUploadImage();
      }else{//普通上传
        $this->commonUploadImage();
      }
  }
  //普通上传--图片
  public function commonUploadImage ($filename='file') {
      $file = request()->file($filename);
      $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/images');
      if($info){
        returnJson('100','上传成功',$info->getSaveName());
      }
      returnJson('-100','上传失败','');
  }
  //普通上传--头像
  public function commonUploadface(){
      $file = request()->file('file');
      $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/face');
      if($info){
        returnJson('100','上传成功',$info->getSaveName());
      }
      returnJson('-100','上传失败','');
  }

  //七牛云上传 单图
  public function qiniuUploadImage ($file='file') {
      $image = new UploadApi ();
      $info = $image->up2QiNiuYun($file);
      if($info){
        returnJson('100','上传成功',$info);
      }
      returnJson('-100','上传失败','');
  }

   public function uploadImages ($filename='fileList') {
       $file = request()->file($filename);
         $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/images');
       if($info){
              returnJson('100','上传成功',$info->getSaveName());
        }else{
            returnJson('-100','上传失败','');
        }
    }
    public function qiNiuUpload($file='fileList'){
          $image = new UploadApi ();
          $savePath = $image->up2QiNiuYun($file);
          return json($savePath);
    }

}