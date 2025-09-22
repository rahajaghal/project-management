<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateWhyUsRequest;
use App\Http\Requests\WhyUsRequest;
use App\Http\Resources\WhyUsResource;
use App\Models\WhyUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhyUsController extends Controller
{
    public function show()
    {
        $why=WhyUs::get();
        if (count($why)>0){
            return ApiResponse::sendResponse(200,'why us retrieved successfully',WhyUsResource::collection($why));
        }
        return ApiResponse::sendResponse(200,'why not retrieved successfully',[]);
    }
    public function create(WhyUsRequest $request)
    {
        $data=$request->validated();
        $why=WhyUs::create($data);
        if ($why) return ApiResponse::sendResponse(200,'about Created Successfully',new WhyUsResource($why));
    }
    public function update(UpdateWhyUsRequest $request)
    {
        $data=$request->validated();
        DB::table('why_us')->where('id',$request->why_id)->update([
            'why_us'=>$request->why_us,
        ]);
        return ApiResponse::sendResponse(200,'why us updated Successfully',[]);
    }
    public function delete($whyId)
    {
        DB::table('why_us')->where('id',$whyId)->delete();
        return ApiResponse::sendResponse(200,'about deleted Successfully',[]);
    }
}
