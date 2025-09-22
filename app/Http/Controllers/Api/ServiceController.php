<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\FlareClient\Api;

class ServiceController extends Controller
{
    public function show()
    {
        $services=Service::all();
        if ($services){
            return ApiResponse::sendResponse(200,'services retrieved successfully',ServiceResource::collection($services));
        }
        return ApiResponse::sendResponse(200,'services not retrieved successfully',[]);
    }
    public function create(ServiceRequest $request)
    {
        $data=$request->validated();

//        $image = time() . '_' . random_int(100000000, 999999999) . '.' . $request->file('image')->getClientOriginalExtension();
//        $path=$request->file('image')->storeAs('services',$image,'posts');
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

                Storage::disk('posts')->put('services/' . $imageName, $imageData);
                $path = 'services/' . $imageName;
            } else {
                return ApiResponse::sendResponse(400, 'بيانات الصورة غير صحيحة', []);
            }
        }

        $data['image']=$path;
        $service=Service::create($data);
        if ($service) return ApiResponse::sendResponse(200,'your service Created Successfully',new ServiceResource($service));
    }
    public function update(UpdateServiceRequest $request)
    {
        $data=$request->validated();

//        $image = time() . '_' . random_int(100000000, 999999999) . '.' . $request->file('image')->getClientOriginalExtension();
//        $path=$request->file('image')->storeAs('services',$image,'posts');
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

                Storage::disk('posts')->put('services/' . $imageName, $imageData);
                $path = 'services/' . $imageName;
            } else {
                return ApiResponse::sendResponse(400, 'بيانات الصورة غير صحيحة', []);
            }
        }


        DB::table('services')->where('id',$request->service_id)->update([
            'title'=>$request->title,
            'description'=>$request->description,
            'image'=>$path,
        ]);
        return ApiResponse::sendResponse(200,'service updated Successfully',[]);
    }
    public function delete($serviceId)
    {
        $oldPath=DB::table('services')->where('id',$serviceId)->pluck('image');
        $oldPath=$oldPath[0];
        $oldPath= public_path(asset($oldPath));
        #----------
        // $oldPath = str_replace('public/', 'public_html/', $oldPath);

        if (file_exists($oldPath)){
            unlink($oldPath);
        }
        #------------

        DB::table('services')->where('id',$serviceId)->delete();
        return ApiResponse::sendResponse(200,'Service Deleted Successfully',[]);
    }
}
