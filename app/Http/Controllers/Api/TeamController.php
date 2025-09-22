<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddMemberRequest;
use App\Http\Requests\TeamRequest;
use App\Http\Resources\TeamUserResource;
use App\Models\Team;
use Illuminate\Http\Request;
use Spatie\FlareClient\Api;

class TeamController extends Controller
{
    public function show()
    {
        $teams=Team::all();
        if ($teams){
            return ApiResponse::sendResponse(200,'teams retrieved successfully', TeamUserResource::collection($teams));
        }
        return ApiResponse::sendResponse(200,'teams not retrieved successfully', []);
    }
    public function create(TeamRequest $request)
    {
        $data=$request->validated();
        $team=Team::create([
            'name'=>$request->name
        ]);
        $team->users()->attach($request->team_manager, ['is_manager' => true]);
        $team->users()->attach($request->team_members, ['is_manager' => false]);
        return ApiResponse::sendResponse(200,'team created successfully', new TeamUserResource($team));
    }
    public function addMembers(AddMemberRequest $request)
    {
        $data=$request->validated();
        $team=Team::findOrFail($request->team_id)->first();
        $team->users()->attach($request->team_members, ['is_manager' => false]);
        return ApiResponse::sendResponse(200,'team created successfully', new TeamUserResource($team));
    }
}
