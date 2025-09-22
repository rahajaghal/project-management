<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactUsRequest;
use App\Http\Resources\ContactUsResource;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class ContactUsController extends Controller
{
    public function create(ContactUsRequest $request)
    {
        $data=$request->validated();
        $contactUs=ContactUs::create([
            'subject'=>$request->subject,
            'description'=>$request->description,
            'phone'=>$request->phone,
            'user_id'=>auth()->user()->id,
        ]);
        if ($contactUs) return ApiResponse::sendResponse(200,'your Post Created Successfully',new ContactUsResource($contactUs));
    }
    public function show()
    {
        $contactUs=QueryBuilder::for(ContactUs::class)
            ->allowedFilters(['seen','tech_approved'])
            ->get();
        if (count($contactUs)>=1){
            return ApiResponse::sendResponse(200,'Contact Us Retrieved Successfully',
                ContactUsResource::collection($contactUs));
        }
        return ApiResponse::sendResponse(200,' No Contact Us Yet',[]);
    }
    public function markSeen($contactId)
    {
        DB::table('contact_us')->where('id',$contactId)->update([
            'seen'=>1,
        ]);
        return ApiResponse::sendResponse(200,'ContactUs seen Successfully',[]);
    }
    public function markApproved($contactId)
    {
        DB::table('contact_us')->where('id',$contactId)->update([
            'tech_approved'=>1,
        ]);
        return ApiResponse::sendResponse(200,'ContactUs approved Successfully',[]);
    }

}
