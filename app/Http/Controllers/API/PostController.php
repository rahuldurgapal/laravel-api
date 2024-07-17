<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController as BaseControler;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;

class PostController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['posts']= Post::all();
        return $this->sendResponse($data,'all Post information');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg,gif',
        ]);

        if($validate->fails()) {
            $this->sendError('Validation Error',$validate->errors()->all(),401);
        }

        $img = $request->image;
        $ext = $img->getClientOriginalExtension();
        $imageName=time(). '.'.$ext;
        $img->move(public_path().'/uploads',$imageName);

        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imageName
        ]);

       $this->sendResponse($post,'Post Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data['post'] = Post::select()->where(['id'=>$id])->get();

        $this->sendResponse($data,'Your single post');

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validate = Validator::make(
            $request->all(),
            [
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg,gif',
        ]);

        if($validate->fails()) {
            $this->sendError('Validation Error',$validate->errors()->all(),401);
        }

        $postImage = Post::select('id','image')->where('id',$id)->get();

        if($request->image != '') {
            $path = public_path().'/uploads';

            if($postImage[0]->image!='' && $postImage[0]->image!=null) {
                $old_file = $path.$postImage[0]->image;

                if(file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            $img = $request->image;
            $ext = $img->getClientOriginalExtension();
            $imageName=time(). '.'.$ext;
            $img->move(public_path().'/uploads',$imageName);
        } else{
            $imageName = $post->image;
        }

        $post = Post::where(['id'=>$id])->update([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imageName
        ]);

        $this->sendResponse($post,'Post Updated Successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $imagePath = Post::select('image')->where('id',$id)->get();
        $path = public_path().'/uploads/'.$imagePath[0]['image'];

        unlink($path);

        $post = Post::where('id',$id)->delete();

        $this->sendResponse($post,'Post Deleted Successfuly');
    }
}
