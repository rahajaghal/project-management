<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function all()
    {
        $roles=Role::where('role','!=','admin')
            ->get();
        if (count($roles)>0){
            return ApiResponse::sendResponse(200,'all Roles Retrieved Successfully',RoleResource::collection($roles));
        }
        return ApiResponse::sendResponse(200,' No Accepted Post Replies Yet',[]);
    }
    public function show()
    {
        $roles=Role::where('role','!=','client')
            ->where('role','!=','admin')
            ->where('role','!=','guest')
            ->where('role','!=','freelancer')
            ->where('role','!=','user')
            ->get();
        if (count($roles)>0){
            return ApiResponse::sendResponse(200,'Roles Retrieved Successfully',RoleResource::collection($roles));
        }
        return ApiResponse::sendResponse(200,' No Accepted Post Replies Yet',[]);
    }
    public function showClient()
    {
        $role=['client','guest','freelancer','user'];
        $roles=Role::whereIn('role',$role)->get();
        if (count($roles)>0){
            return ApiResponse::sendResponse(200,'Roles Retrieved Successfully',RoleResource::collection($roles));
        }
        return ApiResponse::sendResponse(200,'Roles Not Retrieved Successfully',[]);
    }
}
