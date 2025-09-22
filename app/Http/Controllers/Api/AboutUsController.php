<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AboutUsRequest;
use App\Http\Requests\UpdateAboutUsRequest;
use App\Http\Resources\AboutUsResource;
use App\Models\AboutUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AboutUsController extends Controller
{
    public function show()
    {
        $about=AboutUs::first();
        if ($about){
            return ApiResponse::sendResponse(200,'about retrieved successfully',new AboutUsResource($about));
        }
        return ApiResponse::sendResponse(200,'about not retrieved successfully',[]);
    }
    public function create(AboutUsRequest $request)
    {
        $data=$request->validated();
        $about=AboutUs::create($data);
        if ($about) return ApiResponse::sendResponse(200,'about Created Successfully',new AboutUsResource($about));

    }
    public function update(UpdateAboutUsRequest $request)
    {
        DB::table('about_us')->where('id',$request->about_id)->update([
            'work_time'=>$request->work_time,
            'site'=>$request->site,
        ]);
        return ApiResponse::sendResponse(200,'about updated Successfully',[]);
    }
}
