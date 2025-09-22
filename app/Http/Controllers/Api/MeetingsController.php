<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteMeetingRequest;
use App\Http\Requests\MeetingRequest;
use App\Http\Requests\UpdateMeetingRequest;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\User;
use App\Notifications\CreateMeeting;
use App\Notifications\UpdateProjectStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MeetingsController extends Controller
{
    public function show($projectId)
    {
        $meetings= Meeting::where('project_id',$projectId)->get();
        return ApiResponse::sendResponse(200,'meetings retrieved successfully',$meetings);
    }
    public function create(MeetingRequest $request)
    {
        $data =$request->validated();
        $meeting=Meeting::create([
            'date'=>$request->date,
            'meeting_type'=>$request->meeting_type,
            'project_id'=>$request->project_id,
        ]);

        $project=Project::find($request->project_id)->first();
        $client= User::find($project->client_id);
        Notification::send($client,new CreateMeeting($project,'meetings with project manager was created for your project: '));
        return ApiResponse::sendResponse(200,'meeting date created successfully',[]);
    }
    public function update(UpdateMeetingRequest $request)
    {
        $data =$request->validated();
        Meeting::where('id',$request->meeting_id)->update([
           'date'=>$request->date,
           'meeting_type'=>$request->meeting_type
        ]);

        $project=Project::find($request->project_id)->first();
        $client= User::find($project->client_id);
        Notification::send($client,new CreateMeeting($project,'meetings with project manager was updated for your project: '));
        return ApiResponse::sendResponse(200,'meeting updated successfully',[]);
    }
    public function delete(DeleteMeetingRequest $request)
    {
        DB::table('meetings')->where('id',$request->meeting_id)->delete();

        $project=Project::find($request->project_id)->first();
        $client= User::find($project->client_id);
        Notification::send($client,new CreateMeeting($project,'meetings with project manager was deleted for your project: '));
        return ApiResponse::sendResponse(200,'meeting deleted successfully',[]);
    }
}
