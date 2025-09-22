<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\EditRequestResource;
use App\Models\EditRequest;
use Illuminate\Http\Request;

class EditRequestController extends Controller
{
    public function show($projectId)
    {
        $user=auth()->user();
        $editRequests= EditRequest::where('project_id',$projectId)
            ->where('to_user_id',$user->id)
            ->get();

            if ($editRequests){
                return ApiResponse::sendResponse(200,'project edit requests retrieved successfully',EditRequestResource::collection($editRequests));
            }
        return ApiResponse::sendResponse(200,'project edit requests not retrieved successfully',[]);
    }
}
