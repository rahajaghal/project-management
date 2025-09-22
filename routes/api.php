<?php

use App\Http\Controllers\Api\AboutUsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\DraftController;
use App\Http\Controllers\Api\EditRequestController;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\MeetingsController;
use App\Http\Controllers\Api\NotificationsController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReplyController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SprintController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\WhyUsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::controller(AuthController::class)->group(function (){
    Route::post('/auth/{provider}',  'socialAuth');
    Route::post('/register','register');
    Route::post('/login','login');
    Route::post('/logout','logout')->middleware('auth:sanctum');
    Route::get('/user/profile','profile')->middleware('auth:sanctum');
    Route::get('/sent/token','setToken');
    Route::post('/update/employee/profile','updateProgrammerProfile')->middleware('auth:sanctum');
    Route::post('/update/client/profile','updateClientProfile')->middleware('auth:sanctum');
    Route::post('/update/client/profile/for/app','updateClientProfileForApp')->middleware('auth:sanctum');
    Route::post('/update/freelancer/profile','updateFreelancerProfile')->middleware('auth:sanctum');
    Route::post('/update/user/profile','updateUserProfile')->middleware('auth:sanctum');
    Route::post('/admin/login','admin_login');
    //filter
    Route::get('/show/users','showUsers')->middleware(['auth:sanctum','user_show','check_approved']);
    Route::get('/show/clients','showClients');
    Route::get('/toggle/user/approved/{userId}','markApproved')->middleware(['auth:sanctum','check_hr','check_approved']);
    Route::post('/auth/google/token',  'loginWithGoogleToken');
    Route::post('/auth/google/token/{$provider}',  'socialLoginWithToken');
});
Route::controller(RoleController::class)->group(function (){
    Route::get('/show/all/roles','all');
    Route::get('/show/roles','show');
    Route::get('/show/role/client','showClient');
});
Route::controller(PostController::class)->group(function (){
    Route::post('/get/image','getImage');
    Route::post('/get/pdf','getPdf');
    Route::post('/create/post','create')->middleware(['auth:sanctum','check_hr','check_approved']);
    Route::get('/delete/post/{post_id}','delete')->middleware(['auth:sanctum','check_hr','check_approved']);
    Route::get('/show/active/posts','showActive');
    Route::get('/show/not/active/posts','showNotActive');
    Route::get('/un/activate/post/{postId}','updateStatus')->middleware(['auth:sanctum','check_hr','check_approved']);
});
Route::controller(ReplyController::class)->group(function (){
    Route::post('/create/reply','create');
    Route::get('/show/post/replies/{postId}','show')->middleware(['auth:sanctum','check_hr','check_approved']);
    Route::put('/accept/reply/{replyId}','accept')->middleware(['auth:sanctum','check_hr','check_approved']);
    Route::get('/show/accepted/post/replies/{postId}','showAccepted')->middleware(['auth:sanctum','check_hr','check_approved']);
});
Route::controller(ContactUsController::class)->group(function (){
    Route::post('/create/contactUs','create')->middleware(['auth:sanctum','check_client','check_approved']);
    Route::get('/show/contactUs','show');
    Route::get('/mark/contact/seen/{contactId}','markSeen')->middleware(['auth:sanctum','check_technical_manager','check_approved']);
    Route::get('/mark/contact/approved/{contactId}','markApproved')->middleware(['auth:sanctum','check_technical_manager','check_approved']);
});
Route::controller(ServiceController::class)->group(function (){
    Route::post('/create/service','create')->middleware(['auth:sanctum','check_marketing','check_approved']);
    Route::get('/delete/service/{serviceId}','delete')->middleware(['auth:sanctum','check_marketing','check_approved']);
    Route::get('/show/services','show');
    Route::post('/update/service','update')->middleware(['auth:sanctum','check_marketing','check_approved']);
});
Route::controller(AboutUsController::class)->group(function (){
    Route::post('/create/about','create')->middleware(['auth:sanctum','check_marketing','check_approved']);
    Route::post('/update/about','update')->middleware(['auth:sanctum','check_marketing','check_approved']);
    Route::get('/show/about','show');
});
Route::controller(WhyUsController::class)->group(function (){
    Route::post('/create/whyUS','create')->middleware(['auth:sanctum','check_marketing','check_approved']);
    Route::post('/update/whyUS','update')->middleware(['auth:sanctum','check_marketing','check_approved']);
    Route::get('/delete/whyUs/{whyUsId}','delete')->middleware(['auth:sanctum','check_marketing','check_approved']);
    Route::get('/show/whyUs','show');
});
Route::controller(ContractController::class)->group(function (){
    Route::post('/create/draft','createDraft')->middleware(['auth:sanctum','project_manager','check_approved']);
    Route::get('/show/contracts','show')->middleware(['auth:sanctum','check_contract','check_approved']);
    Route::get('/show/contract-manager/contracts','contractManagerContracts')->middleware(['auth:sanctum','contract_manager','check_approved']);
    Route::post('/contract-manager/update/contract','update')->middleware(['auth:sanctum','contract_manager','check_approved']);
    Route::post('/contract-manager/approve/contract','contractManagerApprove')->middleware(['auth:sanctum','contract_manager','check_approved']);
    Route::post('/project-manager/approve/contract','projectManagerApprove')->middleware(['auth:sanctum','project_manager','check_approved']);
    Route::post('/project-manager/update/contract','projectManagerUpdate')->middleware(['auth:sanctum','project_manager','check_approved']);
    Route::get('/show/client/contracts','clientContracts')->middleware(['auth:sanctum','check_client','check_approved']);
    Route::post('/client/request/edit_contract','requestEdit')->middleware(['auth:sanctum','check_client','check_approved']);
    Route::post('/add/sign','addSignature')->middleware(['auth:sanctum','check_client','check_approved']);
    Route::get('/show/admin/contracts','adminContracts')->middleware(['auth:sanctum','is_admin','check_approved']);
    Route::post('/ceo/add/signature','addSignature2')->middleware(['auth:sanctum','is_admin','check_approved']);
});
Route::controller(TeamController::class)->group(function (){
    Route::get('/show/teams','show');
    Route::post('/create/team','create')->middleware(['auth:sanctum','check_technical']);
    Route::post('/add/members','addMembers')->middleware(['auth:sanctum','check_technical']);
});
Route::controller(ProjectController::class)->group(function (){
    Route::get('/show/public/projects','showPublic');
    Route::get('/show/all/projects','showAll')->middleware(['auth:sanctum','is_employee']);
    Route::get('/show/my/projects','myProjects')->middleware('auth:sanctum');
    Route::post('/create/project','create')->middleware(['auth:sanctum','is_client']);
    Route::post('/update/project','update')->middleware(['auth:sanctum','is_client']);
    Route::get('/delete/project/{projectId}','delete')->middleware(['auth:sanctum','is_client']);
    Route::get('/show/pending/projects','showPending')->middleware(['auth:sanctum','check_technical']);
    Route::post('/specify/project/team','addProjectTeam')->middleware(['auth:sanctum','check_technical']);
    Route::get('/pre-assigned-projects/for/project-manager','PreAssignedProjects')->middleware('auth:sanctum');
    Route::get('/Pmanager/accept/project/{projectId}','acceptProject')->middleware('auth:sanctum');
    Route::post('/Pmanager/reject/project','rejectProject')->middleware('auth:sanctum');
    Route::get('/approve/project/{projectId}','PmanagerApproved')->middleware(['auth:sanctum','project_manager']);
    Route::get('/reject/project','PmanagerRejected')->middleware(['auth:sanctum','project_manager']);
    Route::post('/edit/project/request','PmanagerEdit')->middleware(['auth:sanctum','project_manager']);
    Route::post('/update/project/time','updateProjectTime')->middleware(['auth:sanctum','project_manager']);
    Route::post('/complete/project','complete')->middleware(['auth:sanctum','project_manager']);
    Route::post('/review/project','review')->middleware(['auth:sanctum','is_client']);
    Route::post('/client/request/project/edit','clientEdit')->middleware('auth:sanctum');
    Route::get('/admin/permit/developing/project/{projectId}','adminPermitDeveloping')->middleware(['auth:sanctum','is_admin','check_approved']);
});
Route::controller(EditRequestController::class)->group(function (){
    Route::get('/show/latest/edit/request/{projectId}','show')->middleware('auth:sanctum');
});
Route::controller(SprintController::class)->group(function (){
    Route::get('/show/sprint/{sprintId}','show')->middleware('auth:sanctum');
    Route::post('/create/sprint','create')->middleware(['auth:sanctum','project_manager']);
    Route::post('/update/sprint','update')->middleware(['auth:sanctum','project_manager']);
    Route::get('/show/project/sprints/{projectId}','projectSprints')->middleware('auth:sanctum');
    Route::get('/delete/sprint/{sprintId}','delete')->middleware('auth:sanctum');
    Route::post('/start/sprint','start')->middleware(['auth:sanctum','project_manager']);
    Route::get('/show/project/un-complete/sprints/{projectId}','unCompleteSprints');
    Route::post('/complete/sprint/{sprintId}','complete')->middleware(['auth:sanctum','project_manager']);
    Route::get('/show/project/started/sprints/{projectId}','startedSprints')->middleware('auth:sanctum');
});
Route::controller(TaskController::class)->group(function (){
    Route::get('/show/task/{taskId}','show')->middleware('auth:sanctum');
    Route::post('/create/task','create')->middleware('auth:sanctum');
    Route::get('/show/project/tasks/{projectId}','projectTasks')->middleware('auth:sanctum');
    Route::get('/show/sprint/tasks/{sprintId}','sprintTasks')->middleware('auth:sanctum');
    Route::get('/delete/task/{taskId}','delete')->middleware('auth:sanctum');
    Route::post('/update/task','update')->middleware('auth:sanctum');
    Route::post('/show/project/employee/tasks','myProjectTasks')->middleware('auth:sanctum');
    Route::get('/show/employee/tasks','myTasks')->middleware('auth:sanctum');
    Route::get('/project/{project_id}/board','getBoard')->middleware('auth:sanctum');
    Route::get('/show/project/pending-sprints/tasks/{project_id}','projectPendingSprintsTasks')->middleware('auth:sanctum');
    Route::get('/show/project/backlog/tasks/{project_id}','projectBacklogTasks')->middleware('auth:sanctum');
    Route::post('/create/backlog/tasks/sprint', 'backlogTasksSprint')->middleware('auth:sanctum');
});
Route::controller(StatusController::class)->group(function (){
    Route::post('/show/project/status','show')->middleware('auth:sanctum');
    Route::post('/create/status','create')->middleware(['auth:sanctum','project_manager']);
    Route::post('/delete/status','delete')->middleware(['auth:sanctum','project_manager']);
    Route::post('/update/status','reorderStatus')->middleware(['auth:sanctum','project_manager']);
});

Route::controller(NotificationsController::class)->group(function (){
    Route::get('/show/unread/notifications','showUnreadNotifications')->middleware('auth:sanctum');
    Route::get('/show/notification/{id}','showNotification')->middleware('auth:sanctum');
});

Route::controller(MeetingsController::class)->group(function (){
    Route::get('/show/project/meetings/{projectId}','show');
    Route::post('/create/meeting','create')->middleware(['auth:sanctum','project_manager']);
    Route::post('/delete/meeting','delete')->middleware(['auth:sanctum','project_manager']);
    Route::post('/update/meeting','update')->middleware(['auth:sanctum','project_manager']);
});
