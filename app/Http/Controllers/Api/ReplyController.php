<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReplyRequest;
use App\Http\Resources\ReplyResource;
use App\Models\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReplyController extends Controller
{
    public function create(ReplyRequest $request)
    {
        $data=$request->validated();

//        $cv = time() . '_' . random_int(100000000, 999999999) . '.' . $request->file('cv')->getClientOriginalExtension();
//        $path=$request->file('cv')->storeAs('cvs',$cv,'posts');
        $cvData = $request->input('cv');

        // فصل البيانات عن الترويسة (header)

        if (preg_match('/^data:pdf\/(\w+);base64,/', $cvData, $typeCv)) {
            $cvData = substr($cvData, strpos($cvData, ',') + 1);
            $typeCv = strtolower($typeCv[1]);

            // فك تشفير الصورة
            $cvData = base64_decode($cvData);
            if ($cvData === false) {
                return ApiResponse::sendResponse(400, 'خطأ في فك تشفير cv', []);
            }

            // إنشاء اسم فريد للصورة
            $cvName = time() . '_' . random_int(100000000, 999999999) . '.' . $typeCv;

            // تخزين الصورة

            Storage::disk('posts')->put('cvs/' . $cvName, $cvData);
            $pathCv = 'cvs/' . $cvName;
        } else {
            return ApiResponse::sendResponse(400, 'بيانات cv غير صحيحة', []);
        }

        $data['cv']=$pathCv;
        $reply=Reply::create($data);
        if ($reply) return ApiResponse::sendResponse(200,'your Reply Created Successfully',new ReplyResource($reply));
        return ApiResponse::sendResponse(200,'your Reply Not Created Successfully',[]);
    }
    public function show($postId)
    {
        $replies=Reply::where('post_id',$postId)->get();
        if (count($replies)>0){
            return ApiResponse::sendResponse(200,'Post Replies Retrieved Successfully',
                ReplyResource::collection($replies));
        }
        return ApiResponse::sendResponse(200,' No Post Replies Yet',[]);
    }
    public function accept($replyId)
    {
        DB::table('replies')->where('id',$replyId)->update([
            'pre_accept'=>1,
        ]);
        return ApiResponse::sendResponse(200,'Reply Pre Accepted Successfully',[]);
    }
    public function showAccepted($postId)
    {
        $replies=Reply::where('post_id',$postId)->where('pre_accept',1)->get();
        if (count($replies)>0){
            return ApiResponse::sendResponse(200,'Accepted Post Replies Retrieved Successfully',
                ReplyResource::collection($replies));
        }
        return ApiResponse::sendResponse(200,' No Accepted Post Replies Yet',[]);
    }
}
