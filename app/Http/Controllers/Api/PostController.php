<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function getImage(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'filename'=>['required']
        ]);
        if ($validator->fails()){
            return  ApiResponse::sendResponse(200,'register validation errors',$validator->messages()->all());
        }
        $path = public_path($request->filename);

        if (!file_exists($path)) {
            return response()->json(['error' => 'Image not found'], 404);
        }
        return response()->file($path, [
            'Content-Type' => mime_content_type($path),
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
    public function showActive()
    {
        $posts=Post::where('status',1)->get();
        if (count($posts) >=1){
            return ApiResponse::sendResponse(200,'active posts retrieved successfully',PostResource::collection($posts));
        }
        return ApiResponse::sendResponse(200,'active posts not  retrieved successfully',[]);
    }
    public function showNotActive()
    {
        $posts=Post::where('status',0)->get();
        if (count($posts) >=1){
            return ApiResponse::sendResponse(200,'unactive posts retrieved successfully',PostResource::collection($posts));
        }
        return ApiResponse::sendResponse(200,'unactive posts was not retrieved successfully',[]);
    }
    public function create(PostRequest $request)
    {
//        $image = time() . '_' . random_int(100000000, 999999999) . '.' . $request->file('image')->getClientOriginalExtension();
//
//        $path=$request->file('image')->storeAs('posts',$image,'posts');
        $data=$request->validated();
        $path = "";
        if ($request->has('image')) {
            $imageData = $request->input('image');

            // فصل البيانات عن الترويسة (header)

            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);

                // فك تشفير الصورة
                $imageData = base64_decode($imageData);
                if ($imageData === false) {
                    return ApiResponse::sendResponse(400, 'خطأ في فك تشفير الصورة', []);
                }

                // إنشاء اسم فريد للصورة
                $imageName = time() . '_' . random_int(100000000, 999999999) . '.' . $type;

                // تخزين الصورة

                Storage::disk('posts')->put('posts/' . $imageName, $imageData);
                $path = 'posts/' . $imageName;
            } else {
                return ApiResponse::sendResponse(400, 'بيانات الصورة غير صحيحة', []);
            }
        }

        $data['image']=$path;
        $post=Post::create($data);
        if ($post) return ApiResponse::sendResponse(200,'your Post Created Successfully',new PostResource($post));
    }
    public function updateStatus($postId)
    {
        DB::table('posts')->where('id',$postId)->update([
            'status'=>0
        ]);
        return ApiResponse::sendResponse(200,'status updated to (not active) Successfully',[]);
    }
    public function delete($post_id)
    {
        $oldPath=DB::table('posts')->where('id',$post_id)->pluck('image');
        $oldPath=$oldPath[0];
        $oldPath= public_path(asset($oldPath));
        #----------
        // $oldPath = str_replace('public/', 'public_html/', $oldPath);

        if (file_exists($oldPath)){
            unlink($oldPath);
        }
        #------------

        DB::table('posts')->where('id',$post_id)->delete();

        return ApiResponse::sendResponse(200,'Post Deleted Successfully',[]);
    }
}
