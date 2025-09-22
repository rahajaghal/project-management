<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Http\Resources\SprintResource;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ProjectManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
//        $user= auth()->user();
//        $project=Project::find($request->project_id);
//        $team =Team::find($project->team->id);
//        $teamManager = $team->users()->where('user_id', $user->id)->first();
//        if ($teamManager->pivot->is_manager ==1){
//            return $next($request);
//        }
//        return response('you are not allowed to do this,this project manager does');

        $user = $request->user();
        $projectId = $request->route('projectId') ?? $request->project_id;

        $project = Project::find($projectId);
        if (!$project || !$project->team_id) {
            return ApiResponse::sendResponse(200,'Project not assigned to a team',[]);
        }

        $isManager = DB::table('team_user')
            ->where('team_id', $project->team_id)
            ->where('user_id', $user->id)
            ->where('is_manager', 1)
            ->exists();

        if (!$isManager) {
            return ApiResponse::sendResponse(200,'You are not the manager of this project team',[]);
        }

        return $next($request);
    }
}
