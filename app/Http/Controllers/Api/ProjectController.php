<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\disagreeManageProjectRequest;
use App\Http\Requests\ProjectEditRequst;
use App\Http\Requests\ProjectRequest;
use App\Http\Requests\ProjectStatusRequest;
use App\Http\Requests\ProjectTeamRequest;
use App\Http\Requests\RejectProjectRequest;
use App\Http\Requests\ReviewProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Requests\UpdateProjectTimeRequest;
use App\Http\Resources\ProjectResource;
use App\Models\EditRequest;
use App\Models\Email;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use App\Notifications\CreateMeeting;
use App\Notifications\TeamAcceptProject;
use App\Notifications\TeamRejectProject;
use App\Notifications\TeamRequest;
use App\Notifications\UpdateProjectStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function showPending()
    {
        $projects= Project::with('team.users')
            ->where('status','pending')
            ->where('team_id',null)
            ->get();
        if ($projects){
            return ApiResponse::sendResponse(200,'pending projects retrieved successfully',ProjectResource::collection($projects));
        }
        return ApiResponse::sendResponse(200,'pending projects not retrieved successfully',[]);
    }
    public function showPublic()
    {
        $projects=Project::with('team.users')
            ->where('private',0)
            ->where('team_approved', 1)
            ->get();
        if ($projects){
            return ApiResponse::sendResponse(200,'public projects retrieved successfully',ProjectResource::collection($projects));
        }
        return ApiResponse::sendResponse(200,'public not projects retrieved successfully',[]);
    }
    public function showAll()
    {
        $projects=Project::with('team.users')
            ->where('team_approved', 1)
            ->get();
        if ($projects){
            return ApiResponse::sendResponse(200,'public projects retrieved successfully',ProjectResource::collection($projects));
        }
        return ApiResponse::sendResponse(200,'public not projects retrieved successfully',[]);
    }
    public function myProjects()
    {
        $user = auth()->user();

        $userTeamIds = $user->teams->pluck('id');

        $projects = Project::with('team.users')
            ->where('team_approved', 1)
            ->where(function ($query) use ($user, $userTeamIds) {
                $query->where('client_id', $user->id)
                    ->orWhereIn('team_id', $userTeamIds);
            })
            ->get();

        return ApiResponse::sendResponse(
            200,
            'Projects retrieved successfully',
            ProjectResource::collection($projects)
        );
    }

    public function create(ProjectRequest $request)
    {
        $user=auth()->user();
        $data = $request->validated();
        $data['client_id']=$user->id;

        $path="";
        if ($request->has('document')){

            $imageData = $request->input('document');

            // فصل البيانات عن الترويسة (header)

            if (preg_match('/^data:pdf\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);

                // فك تشفير الصورة
                $imageData = base64_decode($imageData);
                if ($imageData === false) {
                    return ApiResponse::sendResponse(400, 'خطأ في فك تشفير المستند', []);
                }

                // إنشاء اسم فريد للصورة
                $imageName = time() . '_' . random_int(100000000, 999999999) . '.' . $type;

                // تخزين الصورة

                Storage::disk('posts')->put('docs/' . $imageName, $imageData);
                $path = 'docs/' . $imageName;
            } else {
                return ApiResponse::sendResponse(400, 'بيانات الصورة غير صحيحة', []);
            }
        }

        $project=Project::create([
            'project_type'=>$request->project_type,
            'project_description'=>$request->project_description,
            'requirements'=>$request->requirements,
            'document'=>$path,
            'cooperation_type'=>$request->cooperation_type,
            'contact_time'=>$request->contact_time,
            'private'=>$request->private,
            'client_id'=>$user->id
        ]);
        Status::create([
            'name'=>'TO DO',
            'project_id'=>$project->id,
            'order' => 1
        ]);
        Status::create([
            'name'=>'IN PROGRESS',
            'project_id'=>$project->id,
            'order' => 2
        ]);
        Status::create([
            'name'=>'Done',
            'project_id'=>$project->id,
            'order' => 3
        ]);
        if ($project){
            $project->load('team.users');

            return ApiResponse::sendResponse(200,'project created successfully',new ProjectResource($project));
        }
        return ApiResponse::sendResponse(200,'project not created successfully',[]);
    }

    public function update(UpdateProjectRequest $request)
    {
        $user=auth()->user();
        $data = $request->validated();
        $project =Project::find($request->project_id);
        if ($project->client_id ==$user->id){
            $path="";
            if ($request->has('document')){

                $imageData = $request->input('document');

                // فصل البيانات عن الترويسة (header)

                if (preg_match('/^data:pdf\/(\w+);base64,/', $imageData, $type)) {
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                    $type = strtolower($type[1]);

                    // فك تشفير الصورة
                    $imageData = base64_decode($imageData);
                    if ($imageData === false) {
                        return ApiResponse::sendResponse(400, 'خطأ في فك تشفير المستند', []);
                    }

                    // إنشاء اسم فريد للصورة
                    $imageName = time() . '_' . random_int(100000000, 999999999) . '.' . $type;

                    // تخزين الصورة

                    Storage::disk('posts')->put('docs/' . $imageName, $imageData);
                    $path = 'docs/' . $imageName;
                } else {
                    return ApiResponse::sendResponse(400, 'بيانات الصورة غير صحيحة', []);
                }
            }

            DB::table('projects')->where('id',$request->project_id)->update([

                'project_type'=>$request->project_type,
                'project_description'=>$request->project_description,
                'requirements'=>$request->requirements,
                'document'=>$path,
                'cooperation_type'=>$request->cooperation_type,
                'contact_time'=>$request->contact_time,
                'private'=>$request->private,
                'status'=>'pending'
            ]);
            return ApiResponse::sendResponse(200,'project updated Successfully',[]);
        }
        return ApiResponse::sendResponse(200,'you are not project client',[]);
    }

    public function addProjectTeam(ProjectTeamRequest $request)
    {
        $data= $request->validated();
        DB::table('projects')->where('id',$request->project_id)->update([
           'team_id'=>$request->team_id
        ]);
        // Retrieve team manager
        $manager = DB::table('team_user')
            ->join('users', 'team_user.user_id', '=', 'users.id')
            ->where('team_user.team_id', $request->team_id)
            ->where('team_user.is_manager', 1)
            ->select('users.*')
            ->first();
        if ($manager) {
            $project = Project::find($request->project_id);
            $user = User::find($manager->id);

            Notification::send($user, new TeamRequest($project));
        }
        return ApiResponse::sendResponse(200,'project team added successfully',[]);
    }
    //preAssigned projects for project manager(that not take decision to manage or not yet)
    public function PreAssignedProjects()
    {
        $user = auth()->user();

        // 1. Get all team IDs where this user is manager
        $managerTeams = DB::table('team_user')
            ->where('user_id', $user->id)
            ->where('is_manager', 1)
            ->pluck('team_id');

        if ($managerTeams->isEmpty()) {
            return ApiResponse::sendResponse(200, 'No projects assigned to you', []);
        }

        // 2. Get projects assigned to those teams that are NOT yet approved
        $projects = Project::whereIn('team_id', $managerTeams)
            ->where('team_approved', 0)
            ->get();

        return ApiResponse::sendResponse(200, 'PreAsignedProjects projects retrieved successfully', ProjectResource::collection($projects));
    }

    //decision manage project

    public function acceptProject($projectId)
    {
        $user = auth()->user();

        // 1. Find project
        $project = Project::findOrFail($projectId);

        // 3. Ensure current user is the manager of that team
        $isManager = DB::table('team_user')
            ->where('team_id', $project->team_id)
            ->where('user_id', $user->id)
            ->where('is_manager', 1)
            ->exists();

        if (!$isManager) {
            return ApiResponse::sendResponse(403, 'You are not the manager of this team', []);
        }

        // 4. Update project as approved
        $project->update([
            'team_approved' => 1,
            'status'=>'under_study',
        ]);
        $tech =User::where('role_id',7)->first();
        Notification::send($tech,new TeamAcceptProject($project));
        return ApiResponse::sendResponse(200, 'Project accepted successfully', $project);
    }

    //decision not manage project
    public function rejectProject(disagreeManageProjectRequest $request)
    {
        $user = auth()->user();

        $project = Project::findOrFail($request->project_id);

        if (!$project->team_id) {
            return ApiResponse::sendResponse(400, 'This project has no team assigned yet', []);
        }

        $isManager = DB::table('team_user')
            ->where('team_id', $project->team_id)
            ->where('user_id', $user->id)
            ->where('is_manager', 1)
            ->exists();

        if (!$isManager) {
            return ApiResponse::sendResponse(403, 'You are not the manager of this team', []);
        }

        $project->update([
            'team_approved' => 0,
            'team_id'=>null,
        ]);
        $tech =User::where('role_id',7)->first();
        Notification::send($tech,new TeamRejectProject($project));
        return ApiResponse::sendResponse(200, 'Project rejected successfully', $project);
    }

    //assigned projects for project manager(that agreed to manager)
//    public function assignedProjects()
//    {
//        $user = auth()->user();
//
//        // 1. Get all team IDs where this user is manager
//        $managerTeams = DB::table('team_user')
//            ->where('user_id', $user->id)
//            ->where('is_manager', 1)
//            ->pluck('team_id');
//
//        if ($managerTeams->isEmpty()) {
//            return ApiResponse::sendResponse(200, 'No projects assigned to you', []);
//        }
//
//        // 2. Get projects assigned to those teams that are NOT yet approved
//        $projects = Project::whereIn('team_id', $managerTeams)
//            ->where('team_approved', 1)
//            ->get();
//
//        return ApiResponse::sendResponse(200, 'asignedProjects projects retrieved successfully', ProjectResource::collection($projects));
//    }

    public function PmanagerApproved($projectId)
    {
        $project =Project::find($projectId);
        $user= User::find($project->client_id);

        DB::table('projects')->where('id',$projectId)->update([
            'status'=>'approved'
        ]);
        Notification::send($user,new UpdateProjectStatus('approved',"",$project));
        return ApiResponse::sendResponse(200,'project status approved successfully',[]);
    }
    public function PmanagerRejected(RejectProjectRequest $request)
    {
        $project =Project::find($request->project_id);
        $user= User::find($project->client_id);

        DB::table('projects')->where('id',$request->project_id)->update([
            'status'=>'rejected'
        ]);

        Notification::send($user,new UpdateProjectStatus('rejected',$request->email,$project));
        return ApiResponse::sendResponse(200,'project status rejected successfully',[]);
    }
    public function PmanagerEdit(ProjectEditRequst $requst)
    {
        $data =$requst->validated();
        $project =Project::find($requst->project_id);
        $user= User::find($project->client_id);

        DB::table('projects')->where('id',$requst->project_id)->update([
            'status'=>'request_to_edit'
        ]);
        $editRequest = EditRequest::create([
            'message'=>$requst->message,
            'project_id'=>$requst->project_id,
            'from_user_id'=>auth()->user()->id,
            'to_user_id'=>$project->client_id
        ]);
        Notification::send($user,new UpdateProjectStatus('request_to_edit',$requst->message,$project));
        return ApiResponse::sendResponse(200,'project status updated to requestToEdit successfully',[]);
    }
    public function updateProjectTime(UpdateProjectTimeRequest $request)
    {
        Project::where('id',$request->project_id)->update([
            'start'=>$request->start,
            'end'=>$request->end
        ]);
        return ApiResponse::sendResponse(200,'project time updated successfully',[]);
    }
    public function complete(ProjectStatusRequest $request)
    {
        Project::where('id',$request->project_id)->update([
            'status'=>'completed'
        ]);
        $project=Project::find($request->project_id)->first();
        $client= User::find($project->client_id);
        Notification::send($client,new CreateMeeting($project,'your project was completed,please make review to it.'));
        return ApiResponse::sendResponse(200,'project was completed successfully',[]);
    }
    public function review(ReviewProjectRequest $request)
    {
        $user = auth()->user();
        $project = Project::findOrFail($request->project_id);

        if ($project->client_id !== $user->id) {
            return ApiResponse::sendResponse(403, 'You are not authorized to review this project.', []);
        }

        $project->update([
            'review' => $request->review,
        ]);

        return ApiResponse::sendResponse(200, 'Project reviewed successfully.', []);
    }

    public function clientEdit(ProjectEditRequst $request)
    {
        $data = $request->validated();

        $project = Project::findOrFail($request->project_id);

        // Ensure only the project client can request edits
        if (auth()->id() !== $project->client_id) {
            return ApiResponse::sendResponse(403, 'You are not authorized to request edits for this project.', []);
        }

        // Find the project manager (from team_user where is_manager = 1)
        $manager = User::whereHas('teams', function ($q) use ($project) {
            $q->where('teams.id', $project->team_id)
                ->where('team_user.is_manager', 1);
        })->first();

        if (!$manager) {
            return ApiResponse::sendResponse(404, 'Project manager not found.', []);
        }

        // Create edit request
        $editRequest = EditRequest::create([
            'message'      => $request->message,
            'project_id'   => $project->id,
            'from_user_id' => auth()->id(),        // client
            'to_user_id'   => $manager->id,        // manager
        ]);

        // Send notification to the manager
        Notification::send(
            $manager,
            new UpdateProjectStatus('request_to_edit', $request->message, $project)
        );
        return ApiResponse::sendResponse(200, 'Project status updated to request_to_edit successfully', []);
    }
    public function adminPermitDeveloping($projectId)
    {
        Project::where('id', $projectId)->update([
            'status' => 'developing'
        ]);
        $project = Project::findOrFail($projectId);

        $manager = User::whereHas('teams', function ($q) use ($project) {
            $q->where('teams.id', $project->team_id)
                ->where('team_user.is_manager', 1);
        })->first();

        if (!$manager) {
            return ApiResponse::sendResponse(404, 'No project manager found for this project.', []);
        }
        Notification::send($manager, new CreateMeeting($project, 'Admin permitted developing this project')
        );
        return ApiResponse::sendResponse(200, 'Project status turned to developing successfully', []);
    }
}
