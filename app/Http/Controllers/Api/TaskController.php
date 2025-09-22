<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\BacklogSprintRequest;
use App\Http\Requests\MyTasksRequest;
use App\Http\Requests\ProjectBoardRequest;
use App\Http\Requests\TaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\CountStatusResource;
use App\Http\Resources\SprintResource;
use App\Http\Resources\SprintTaskResource;
use App\Http\Resources\StatusResource;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Status;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;

class TaskController extends Controller
{
    public function show($taskId)
    {
        $user = auth()->user();
        $task = Task::find($taskId);
        $project = Project::find($task->project_id);
        $team = $project->team;
        $teamMembers = $team->users()->pluck('users.id');

        if ($teamMembers->contains($user->id)) {
            return ApiResponse::sendResponse(200, 'task retrieved successfully', new TaskResource($task));
        }
        return ApiResponse::sendResponse(200, 'task not retrieved successfully', []);
    }

    public function create(TaskRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();
        $project = Project::find($request->project_id);
        $team = $project->team;
        $teamMembers = $team->users()->pluck('users.id');

        if ($teamMembers->contains($user->id)) {
            $task = Task::create($data);
            if ($task) {
                return ApiResponse::sendResponse(200, 'task created successfully', new TaskResource($task));
            }
            return ApiResponse::sendResponse(200, 'task not created successfully', []);
        }
        return ApiResponse::sendResponse(200, 'task  not created successfully,you are not allowed,team members allowed', []);
    }

    public function projectTasks($projectId)
    {
        $user = auth()->user();
        $project = Project::find($projectId);

        $team = $project->team;
        $teamMembers = $team->users()->pluck('users.id');

        if ($teamMembers->contains($user->id)) {
            $projectTasks = $project->tasks;
            if ($projectTasks) {
                return ApiResponse::sendResponse(200, 'project tasks retrieved successfully', TaskResource::collection($projectTasks));
            }
            return ApiResponse::sendResponse(200, 'project tasks not retrieved successfully', []);
        }
        return ApiResponse::sendResponse(200, 'project tasks not retrieved successfully,you are not allowed,project team is allowed', []);

    }

    public function sprintTasks($sprintId)
    {
        $user = auth()->user();

        $sprint = Sprint::find($sprintId);
        $team = $sprint->project->team;
        $teamMembers = $team->users()->pluck('users.id');
        if ($teamMembers->contains($user->id)) {
            return ApiResponse::sendResponse(200, 'sprint Tasks retrieved successfully', TaskResource::collection($sprint->tasks));
        }
        return ApiResponse::sendResponse(200, 'sprint  not deleted successfully,you are not allowed,project manager allowed', []);
    }

    public function delete($taskId)
    {
        $user = auth()->user();
        $task = Task::find($taskId);
        $team = $task->project->team;
        $teamMembers = $team->users()->pluck('users.id');

        if ($teamMembers->contains($user->id)) {
            DB::table('tasks')->where('id', $taskId)->delete();
            return ApiResponse::sendResponse(200, 'task deleted successfully', []);
        }
        return ApiResponse::sendResponse(200, 'task not deleted successfully,you are not allowed,project team does', []);
    }

    public function update(UpdateTaskRequest $request)
    {
        $user = auth()->user();
        $task = Task::find($request->task_id);
        $team = $task->project->team;
        $teamMembers = $team->users()->pluck('users.id');

        if ($teamMembers->contains($user->id)) {
            DB::table('tasks')->where('id', $request->task_id)->update([

                'title' => $request->title,
                'description' => $request->description,
                'project_id' => $request->project_id,
                'sprint_id' => $request->sprint_id,
                'status_id' => $request->status_id,
                'user_id' => $request->user_id,
                'priority' => $request->priority,
                'completion' => $request->completion,
                'start' => $request->start,
                'end' => $request->end,
            ]);
            return ApiResponse::sendResponse(200, 'task updated successfully', []);
        }
        return ApiResponse::sendResponse(200, 'task not updated successfully,you are not allowed,project team does', []);
    }

    public function myProjectTasks(MyTasksRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();

        $myTasks = QueryBuilder::for(Task::class)
            ->allowedFilters(['sprint_id', 'priority', 'status_id'])
            ->where('project_id', $request->project_id)
            ->where('user_id', $user->id)
            ->get();
        if (count($myTasks) > 0) {
            return ApiResponse::sendResponse(200, 'myTasks Retrieved successfully', TaskResource::collection($myTasks));
        }
        return ApiResponse::sendResponse(200, 'myTasks not Retrieved successfully', []);
    }

    public function myTasks()
    {
        $user = auth()->user();

        $myTasks = QueryBuilder::for(Task::class)
            ->allowedFilters(['project_id', 'priority'])
            ->where('user_id', $user->id)
            ->get();
        if (count($myTasks) > 0) {
            return ApiResponse::sendResponse(200, 'myTasks Retrieved successfully', TaskResource::collection($myTasks));
        }
        return ApiResponse::sendResponse(200, 'myTasks not Retrieved successfully', []);
    }
//    public function getBoard(Request $request, $project_id)
//    {
//        $request->merge(['project_id' => $project_id]);
//        $validator =Validator::make($request->all(),[
//            'project_id' => ['required', 'integer', 'exists:projects,id'],
//            'filter.sprint_id' => ['nullable', 'string'],
//        ]);
//        if ($validator->fails()){
//            return ApiResponse::sendResponse(200,'validation error',$validator->messages()->all());
//        }
//
//        $user = auth()->user();
//        $project = Project::findOrFail($project_id);
//        $team = $project->team;
//        $teamMembers = $team->users()->pluck('users.id');
//
//        if (!$teamMembers->contains($user->id)) {
//            return ApiResponse::sendResponse(200, 'You are not allowed, only project team members can access.', []);
//        }
//
//        $rawSprintIds = $request->input('filter.sprint_id');
//        $sprintIds = [];
//
//        if (is_array($rawSprintIds)) {
//            $sprintIds = array_filter($rawSprintIds, fn($id) => is_numeric($id));
//        } elseif (is_string($rawSprintIds)) {
//            $trimmed = trim($rawSprintIds);
//            if ($trimmed !== '' && $trimmed !== '[]') {
//                $sprintIds = array_filter(
//                    explode(',', $trimmed),
//                    fn($id) => is_numeric(trim($id))
//                );
//            }
//        }
//
//        $statuses = Status::where('project_id', $project_id)
//            ->with(['tasks' => function ($q) use ($sprintIds) {
//                if (count($sprintIds) > 0) {
//                    $q->whereIn('sprint_id', $sprintIds);
//                } else {
//                    $q->whereHas('sprint', function ($subQuery) {
//                        $subQuery->where('status', 'started');
//                    });
//                }
//            }])
//            ->orderBy('order')
//            ->get();
//
//        return ApiResponse::sendResponse(
//            200,
//            'Project board with tasks filtered by sprint or started sprints',
//            CountStatusResource::collection($statuses)
//        );
//    }
    public function getBoard(Request $request, $project_id)
    {
        $request->merge(['project_id' => $project_id]);

        $validator = Validator::make($request->all(), [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'filter.sprint_id' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(200, 'validation error', $validator->messages()->all());
        }

        $user = auth()->user();
        $project = Project::findOrFail($project_id);
        $team = $project->team;
        $teamMembers = $team->users()->pluck('users.id');

        if (!$teamMembers->contains($user->id)) {
            return ApiResponse::sendResponse(200, 'You are not allowed, only project team members can access.', []);
        }

        $rawSprintIds = $request->input('filter.sprint_id');
        $sprintIds = [];

        if (is_array($rawSprintIds)) {
            $sprintIds = array_filter($rawSprintIds, fn($id) => is_numeric($id));
        } elseif (is_string($rawSprintIds)) {
            $trimmed = trim($rawSprintIds);
            if ($trimmed !== '' && $trimmed !== '[]') {
                $sprintIds = array_filter(
                    explode(',', $trimmed),
                    fn($id) => is_numeric(trim($id))
                );
            }
        }

        $statuses = Status::where('project_id', $project_id)
            ->with(['tasks' => function ($q) use ($sprintIds) {
                if (count($sprintIds) > 0) {
                    // Load tasks from the specified sprint(s)
                    $q->whereIn('sprint_id', $sprintIds);
                } else {
                    // Load tasks from sprints that are NOT completed
                    $q->whereHas('sprint', function ($subQuery) {
                        $subQuery->where('status', '!=', 'completed');
                    });
                }
            }])
            ->orderBy('order')
            ->get();

        return ApiResponse::sendResponse(
            200,
            'Project board with tasks filtered by sprint or incomplete sprints',
            CountStatusResource::collection($statuses)
        );
    }
    public function projectPendingSprintsTasks($project_id)
    {
        $user = auth()->user();

        // Check project exists
        $project = Project::findOrFail($project_id);

        $team = $project->team;
        $teamMembers = $team->users()->pluck('users.id');

        if (!$teamMembers->contains($user->id)) {
            return ApiResponse::sendResponse(200, 'You are not allowed, only project team members can access.', []);
        }

        // Retrieve pending sprints with their tasks (for this project)
        $sprints = Sprint::where('project_id', $project_id)
            ->where('status', 'pending')
            ->with('tasks')  // eager load tasks relation
            ->get();

        return ApiResponse::sendResponse(
            200,
            'Pending sprints with their tasks retrieved successfully',
            SprintTaskResource::collection($sprints)
        );
    }

    public function projectBacklogTasks($project_id)
    {
        $user = auth()->user();

        // Check project exists
        $project = Project::findOrFail($project_id);
        $team = $project->team;
        $teamMembers = $team->users()->pluck('users.id');

        if (!$teamMembers->contains($user->id)) {
            return ApiResponse::sendResponse(200, 'You are not allowed, only project team members can access.', []);
        }

        // Retrieve backlog tasks: tasks with NULL sprint_id
        $tasks = Task::where('project_id', $project_id)
            ->whereNull('sprint_id')
            ->get();

        return ApiResponse::sendResponse(
            200,
            'Backlog tasks retrieved successfully',
            TaskResource::collection($tasks)
        );
    }

    public function backlogTasksSprint(BacklogSprintRequest $request)
    {

        $data = $request->validated();
        $user = auth()->user();

        // Authorization: Check user belongs to project team
        $project = Project::findOrFail($data['project_id']);
        $teamMembers = $project->team->users()->pluck('users.id');
        if (!$teamMembers->contains($user->id)) {
            return ApiResponse::sendResponse(200, 'You are not allowed, only project team members can create sprints.', []);
        }

        // Create the sprint
        $sprintData = $data;
        unset($sprintData['tasks_ids']);  // Remove tasks_ids before sprint creation
        $sprint = Sprint::create($sprintData);

        // Assign tasks to the sprint
        Task::whereIn('id', $data['tasks_ids'])
            ->where('project_id', $data['project_id'])  // Ensure task belongs to same project
            ->update(['sprint_id' => $sprint->id]);

        return ApiResponse::sendResponse(
            200,
            'Sprint created successfully and backlog tasks assigned.',
            new SprintTaskResource($sprint)
        );
    }
}
