<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteStatusRequest;
use App\Http\Requests\ProjectStatusRequest;
use App\Http\Requests\StatusRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\StatusResource;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    public function show(ProjectStatusRequest $request)
    {
        $user =auth()->user();
        $project=Project::find($request->project_id);
        $team=  $project->team;
        $teamMembers = $team->users()->pluck('users.id');

        if ($teamMembers->contains($user->id)){
            $projectStatus=Status::where('project_id',$request->project_id)
                ->orderBy('order', 'asc')
                ->get();
            return ApiResponse::sendResponse(200,'task retrieved successfully',StatusResource::collection($projectStatus));
        }

        return ApiResponse::sendResponse(200,'task not retrieved successfully',[]);
    }
    public function create(StatusRequest $request)
    {
        $data= $request->validated();
        $lastOrder = Status::where('project_id', $request->project_id)->max('order') ?? 0;

        $status = Status::create([
            'name' => $request->name,
            'project_id' => $request->project_id,
            'order' => $lastOrder + 1,
        ]);

        if ($status){
            $project= Project::where('id',$request->project_id)->first();
            return ApiResponse::sendResponse(200,'project status created successfully',new StatusResource($status));
        }
        return ApiResponse::sendResponse(200,'project status not created successfully',[]);
    }
    public function delete(DeleteStatusRequest $request)
    {
        $data =$request->validated();
        $statusId=$request->status_id;
        DB::transaction(function () use ($statusId) {
            $status = Status::findOrFail($statusId);
            $projectId = $status->project_id;

            // Get the previous status by order
            $previousStatus = Status::where('project_id', $projectId)
                ->where('order', '<', $status->order)
                ->orderByDesc('order')
                ->first();

            if ($previousStatus) {
                // Reassign tasks to the previous status
                Task::where('status_id', $status->id)
                    ->update(['status_id' => $previousStatus->id]);

            } else {
                // Optional: what to do if no previous status (e.g., abort or set to null)
                throw new \Exception("No previous status found. Cannot delete.");
            }

            // Delete the status
            $status->delete();

        });
        return ApiResponse::sendResponse(200,'status deleted successfully');
    }
    public function reorderStatus(UpdateStatusRequest $request)
    {
        $data= $request->validated();

        DB::transaction(function () use ($request) {
            $status = Status::findOrFail($request->status_id);
            $projectId = $status->project_id;
            $currentOrder = $status->order;
            $newOrder = $request->new_order;

            if ($newOrder == $currentOrder) {
                return; // No need to reorder
            }

            if ($newOrder < $currentOrder) {
                // Moving up: increment others between new and current
                Status::where('project_id', $projectId)
                    ->where('order', '>=', $newOrder)
                    ->where('order', '<', $currentOrder)
                    ->increment('order');
            } else {
                // Moving down: decrement others between current and new
                Status::where('project_id', $projectId)
                    ->where('order', '<=', $newOrder)
                    ->where('order', '>', $currentOrder)
                    ->decrement('order');
            }

            // Update this status
            $status->update(['order' => $newOrder]);
        });

        return ApiResponse::sendResponse(200, 'Status order updated successfully', []);
    }

}
