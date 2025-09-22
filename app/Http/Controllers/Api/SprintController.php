<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\SprintRequest;
use App\Http\Requests\startSprintRequest;
use App\Http\Requests\UpdateSprintRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SprintResource;
use App\Http\Resources\WhyUsResource;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\Team;
use App\Models\WhyUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SprintController extends Controller
{
//    public function create(SprintRequest $request)
//    {
//        $data= $request->validated();
//        //teamManager
//        $user= auth()->user();
//        $project=Project::find($request->project_id);
//        $team =Team::find($project->team->id);
//        $teamManager = $team->users()->where('user_id', $user->id)->first();
//        if ($teamManager->pivot->is_manager ==1){
//            $sprint=Sprint::create($data);
//            if ($sprint) return ApiResponse::sendResponse(200,'sprint Created Successfully',new SprintResource($sprint));
//            return ApiResponse::sendResponse(200,'sprint Not Created Successfully',[]);
//        }
//        return ApiResponse::sendResponse(200,'you are not allowed,this project manager does',[]);
//    }
    public function show($sprintId)
    {
        $user=auth()->user();
        $sprint=Sprint::find($sprintId);

        $team =  $sprint->project->team;
        $teamMembers = $team->users()->pluck('users.id');
        if ($teamMembers->contains($user->id)){
            return ApiResponse::sendResponse(200,'sprint retrieved successfully',new SprintResource($sprint));
        }
        return ApiResponse::sendResponse(200,'sprint  not retrieved successfully,you are not allowed,team members allowed',[]);
    }

    public function create(SprintRequest $request)
    {
        $data= $request->validated();
        $user= auth()->user();

        $sprint=Sprint::create($data);
        if ($sprint) return ApiResponse::sendResponse(200,'sprint Created Successfully',new SprintResource($sprint));
        return ApiResponse::sendResponse(200,'sprint Not Created Successfully',[]);
    }
    public function projectSprints($projectId)
    {
        $user= auth()->user();
        $project=Project::find($projectId);
        $team =Team::find($project->team->id);
        $teamMembers = $team->users()->pluck('users.id');

        if ($teamMembers->contains($user->id)){
            $sprints =Sprint::where('project_id',$projectId)->get();
            if ($sprints){
                return ApiResponse::sendResponse(200,'project sprints Retrieved successfully',SprintResource::collection($sprints));
            }
            return ApiResponse::sendResponse(200,'project sprints Not Retrieved successfully',[]);
        }
        return ApiResponse::sendResponse(200,'you are not from project team members',[]);
    }

    public function delete($sprintId)
    {
        $user=auth()->user();
        $sprint=Sprint::find($sprintId);
        $projectTeam =  $sprint->project->team;
        $teamManager = $projectTeam->users()->where('user_id', $user->id)->first();
        if ($teamManager->pivot->is_manager ==1){
            DB::table('sprints')->where('id',$sprintId)->delete();
            return ApiResponse::sendResponse(200,'sprint deleted successfully',[]);
        }
        return ApiResponse::sendResponse(200,'sprint  not deleted successfully,you are not allowed,project manager allowed',[]);
    }
    public function update(UpdateSprintRequest $request)
    {
        $data=$request->validated();
        DB::table('sprints')->where('id',$request->sprint_id)->update([
            'label'=>$request->label,
            'description'=>$request->description,
            'goal'=>$request->goal,
            'start'=>$request->start,
            'end'=>$request->end,
            'project_id'=>$request->project_id,
            'status'=>$request->status,
        ]);
        return ApiResponse::sendResponse(200,'sprint updated Successfully',[]);
    }
    public function start(startSprintRequest $request)
    {
        DB::table('sprints')->where('id',$request->sprint_id)->update([
            'status'=>'started'
        ]);
        return ApiResponse::sendResponse(200,'sprint started successfully',[]);
    }

    public function unCompleteSprints($projectId)
    {
        $sprints = Sprint::where('project_id',$projectId)->where('status','!=', 'completed')->get();
        if ($sprints){
            return ApiResponse::sendResponse(200,'project unCompleteSprints retrieved successfully',SprintResource::collection($sprints));
        }
        return ApiResponse::sendResponse(200,'project unCompleteSprints not retrieved successfully',[]);
    }
//    public function complete(Request $request, $sprintId)
//    {
//        $sprint = Sprint::findOrFail($sprintId);
//
//        $request->validate([
//            'action' => 'required|in:null,new,existing',
//            'new_sprint_label' => 'required_if:action,new|string|max:255',
//            'existing_sprint_id' => 'required_if:action,existing|exists:sprints,id',
//            'project_id'=>'required|exists:projects,id',
//        ]);
//
//        DB::transaction(function () use ($request, $sprint) {
//            $incompleteTasks = Task::where('sprint_id', $sprint->id)
//                ->where('completion', '<', 100)
//                ->get();
//
//            if ($request->action === 'null') {
//                // Option 1: Detach from sprint
//                Task::whereIn('id', $incompleteTasks->pluck('id'))->update(['sprint_id' => null]);
//
//            } elseif ($request->action === 'new') {
//                // Option 2: Create new sprint and assign
//                $newSprint = Sprint::create([
//                    'label' => $request->new_sprint_label,
//                    'description' => $sprint->description,
//                    'goal' => $sprint->goal,
//                    'start' => now()->toDateString(),
//                    'end' => now()->addWeeks(2)->toDateString(),
//                    'project_id' => $sprint->project_id,
//                    'status' => 'pending',
//                ]);
//
//                Task::whereIn('id', $incompleteTasks->pluck('id'))->update(['sprint_id' => $newSprint->id]);
//
//            } elseif ($request->action === 'existing') {
//                // Option 3: Move to existing sprint
//                Task::whereIn('id', $incompleteTasks->pluck('id'))->update(['sprint_id' => $request->existing_sprint_id]);
//            }
//
//            $sprint->update(['status' => 'completed']);
//        });
//
//        return  ApiResponse::sendResponse(200,'Sprint completed and tasks handled.',[]);
//
//    }
    public function complete(Request $request, $sprintId)
    {
        $sprint = Sprint::findOrFail($sprintId);

        $request->validate([
            'action' => 'required|in:null,new,existing,auto',
            'new_sprint_label' => 'required_if:action,new|string|max:255',
            'existing_sprint_id' => 'required_if:action,existing|exists:sprints,id',
            'project_id' => 'required|exists:projects,id',
        ]);

        DB::transaction(function () use ($request, $sprint) {
            $incompleteTasks = Task::where('sprint_id', $sprint->id)
                ->where('completion', '<', 100)
                ->get();

            if ($request->action === 'auto') {
                if ($incompleteTasks->isEmpty()) {
                    // All tasks are complete, safe to complete sprint
                    $sprint->update(['status' => 'completed']);
                } else {
                    abort(400, 'Sprint has incomplete tasks. Please choose how to handle them.');
                }

            } elseif ($request->action === 'null') {
                Task::whereIn('id', $incompleteTasks->pluck('id'))->update(['sprint_id' => null]);

            } elseif ($request->action === 'new') {
                $newSprint = Sprint::create([
                    'label' => $request->new_sprint_label,
                    'description' => $sprint->description,
                    'goal' => $sprint->goal,
                    'start' => now()->toDateString(),
                    'end' => now()->addWeeks(2)->toDateString(),
                    'project_id' => $sprint->project_id,
                    'status' => 'pending',
                ]);
                Task::whereIn('id', $incompleteTasks->pluck('id'))->update(['sprint_id' => $newSprint->id]);

            } elseif ($request->action === 'existing') {
                Task::whereIn('id', $incompleteTasks->pluck('id'))->update(['sprint_id' => $request->existing_sprint_id]);
            }

            // Only update sprint status if it wasn't already updated in 'auto'
            if ($request->action !== 'auto') {
                $sprint->update(['status' => 'completed']);
            }
        });

        return ApiResponse::sendResponse(200, 'Sprint completed and tasks handled.', []);
    }
    public function startedSprints($projectId)
    {
        $sprints = Sprint::where('project_id',$projectId)->where('status', 'started')->get();
        if ($sprints){
            return ApiResponse::sendResponse(200,'project startedSprints retrieved successfully',SprintResource::collection($sprints));
        }
        return ApiResponse::sendResponse(200,'project startedSprints not retrieved successfully',[]);
    }

}
