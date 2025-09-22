<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientAppRequest;
use App\Http\Requests\ClientProfileRequest;
use App\Http\Requests\FreelancerProfileRequest;
use App\Http\Requests\ProgrammmerProfileRequest;
use App\Http\Resources\ClientResource;
use App\Http\Resources\UserResource;
use App\Models\ContactUs;
use App\Models\Project;
use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Laravel\Socialite\Facades\Socialite;
use Spatie\FlareClient\Api;
use Spatie\QueryBuilder\QueryBuilder;


class AuthController extends Controller
{
//    public function loginWithGoogleToken(Request $request)
//    {
//        $request->validate([
//            'access_token' => 'required|string',
//        ]);
//
//        try {
//            $accessToken = $request->input('access_token');
//
//            $googleUser = Socialite::driver('google')->stateless()->userFromToken($accessToken);
//
//            $finduser = User::where('social_id', $googleUser->id)->first();
//
//            if ($finduser) {
//                Auth::login($finduser);
//                return response()->json($finduser, 200);
//            } else {
//                $newUser = User::create([
//                    'name' => $googleUser->name,
//                    'nickname' => $googleUser->nickname ?? null,
//                    'avatar' => $googleUser->getAvatar(),
//                    'email' => $googleUser->email,
//                    'social_id' => $googleUser->id,
//                    'social_type' => 'google',
//                    'token' => $googleUser->token,
//                    'refresh_token' => $googleUser->refreshToken ?? null,
//                ]);
//
//                Auth::login($newUser);
//                return response()->json($newUser, 201);
//            }
//        } catch (Exception $e) {
//            return response()->json(['error' => $e->getMessage()], 500);
//        }
//    }

    public function socialLoginWithToken(Request $request, $provider)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        if (!in_array($provider, ['google', 'github'])) {
            return response()->json(['error' => 'Unsupported provider'], 422);
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->userFromToken($request->access_token);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token or provider error'], 401);
        }

        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName(),
                'email_verified_at' => now(),
            ]
        );

        $user->providers()->updateOrCreate(
            ['social_type' => $provider, 'social_id' => $socialUser->getId()]
        );

        $token = $user->createToken('Project Management')->plainTextToken;

        return response()->json([
            'token' => $token,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
//        Socialite::driver($provider)->stateless()->userFromToken($accessToken);
    }

//    public function handleProviderCallback($provider)
//    {
//        $validated = $this->validateProvider($provider);
//        if (!is_null($validated)) {
//            return $validated;
//        }
//        try {
//            $user = Socialite::driver($provider)->stateless()->user();
//        } catch (ClientException $exception) {
//            return response()->json(['error' => 'Invalid credentials provided.'], 422);
//        }
//
//        $userCreated = User::firstOrCreate(
//            [
//                'email' => $user->getEmail()
//            ],
//            [
//                'email_verified_at' => now(),
//                'name' => $user->getName(),
//            ]
//        );
//        $userCreated->providers()->updateOrCreate(
//            [
//                'social_type' => $provider,
//                'social_id' => $user->getId(),
//            ],
//
//        );
//        $data['token']=$userCreated->createToken('Project Management')->plainTextToken;
//        $data['name']=$userCreated->name;
//        $data['email']=$userCreated->email;
//        return  ApiResponse::sendResponse(200,'User Account Created Successfully',$data);
//
//    }
//    protected function validateProvider($provider)
//    {
//        if (!in_array($provider, ['github', 'google'])) {
//            return response()->json(['error' => 'Please login using github or google'], 422);
//        }
//    }

//    public function handleProviderCallback($provider)
//    {
//        // Normalize provider name (e.g., Google → google)
//        $provider = strtolower($provider);
//
//        // Validate provider
//        $validated = $this->validateProvider($provider);
//        if (!is_null($validated)) {
//            return $validated;
//        }
//
//        try {
//            $user = Socialite::driver($provider)->stateless()->user();
//        } catch (\Exception $exception) {
//            return response()->json(['error' => 'Invalid credentials provided.'], 422);
//        }
//
//        // Handle missing email (GitHub users may hide it)
//        $email = $user->getEmail() ?? "{$provider}_{$user->getId()}@noemail.com";
//
//        // Create or get user
//        $userCreated = User::firstOrCreate(
//            ['email' => $email],
//            [
//                'email_verified_at' => now(),
//                'name' => $user->getName(),
//            ]
//        );
//
//        // Link provider account
//        $userCreated->providers()->updateOrCreate(
//            [
//                'social_type' => $provider,
//                'social_id'   => $user->getId(),
//            ]
//        );
//
//        // Generate Sanctum token
//        $data = [
//            'token' => $userCreated->createToken('Project Management')->plainTextToken,
//            'name'  => $userCreated->name,
//            'email' => $userCreated->email,
//        ];
//
//        return ApiResponse::sendResponse(200, 'User Account Created Successfully', $data);
//    }

//    protected function validateProvider($provider)
//    {
//        if (!in_array($provider, ['github', 'google'])) {
//            return response()->json(['error' => 'Please login using github or google'], 422);
//        }
//    }
    public function socialAuth(Request $request, $provider)
    {
        $provider = strtolower($provider);

        if (!in_array($provider, ['google', 'github'])) {
            return response()->json(['error' => 'Please login using Google or GitHub'], 422);
        }

        try {
            if ($provider === 'google') {
                // Flutter sends Google ID token
                $idToken = $request->input('token');
                if (!$idToken) {
                    return response()->json(['error' => 'Missing Google token'], 422);
                }
                $providerUser = Socialite::driver('google')->stateless()->userFromToken($idToken);
            } else {
                // Flutter sends GitHub code
                $code = $request->input('code');
                if (!$code) {
                    return response()->json(['error' => 'Missing GitHub code'], 422);
                }
                $providerUser = Socialite::driver('github')->stateless()->userFromToken($code);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid credentials: ' . $e->getMessage()], 401);
        }

        // Handle missing email (GitHub users may hide it)
        $email = $providerUser->getEmail() ?? "{$provider}_{$providerUser->getId()}@noemail.com";

        // Create or get user
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $providerUser->getName(),
                'email_verified_at' => now(),
            ]
        );

        // Link provider account
        $user->providers()->updateOrCreate(
            [
                'social_type' => $provider,
                'social_id'   => $providerUser->getId(),
            ],
            [
                'token' => $request->input('token') ?? $request->input('code'),
                'refresh_token' => null,
            ]
        );

        // Return only needed columns
        $data = [
            'token' => $user->createToken('Project Management')->plainTextToken,
            'name'  => $user->name,
            'email' => $user->email,
        ];

        return ApiResponse::sendResponse(200, 'User Account Created Successfully', $data);
    }



    public function register(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'name'=>['required','string','max:255'],
            'email'=>['required','email','max:255','unique:'.User::class],
            'password'=>['required','confirmed',Rules\Password::default()],
        ],[],[]);
        if ($validator->fails()){
            return ApiResponse::sendResponse(422,'Regiser Validation Error',$validator->errors());
        }
        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
        ]);
        $data['token']=$user->createToken('Project Management')->plainTextToken;
        $data['name']=$user->name;
        $data['email']=$user->email;
        return  ApiResponse::sendResponse(200,'User Account Created Successfully',$data);
    }
    public function login(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'email'=>['required','email','max:255'],
            'password'=>['required'],
        ],[],[
            'email'=>'Email',
            'password'=>'Password',
        ]);
        if ($validator->fails()){
            return ApiResponse::sendResponse(422,'Login Validation Error',$validator->errors());
        }
        if (Auth::attempt(['email'=>$request->email,'password'=>$request->password])){
            $user=Auth::user();
            $data['token']=$user->createToken('Project Management')->plainTextToken;
            $data['name']=$user->name;
            $data['email']=$user->email;

            return  ApiResponse::sendResponse(200,'User Logged In Successfully',$data);
        }else{
            return  ApiResponse::sendResponse(401,'User Credentials Doesnt exist',[]);
        }
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::sendResponse(200,'Logged Out Successfully',[]);
    }
    public function updateProgrammerProfile(ProgrammmerProfileRequest $request)
    {
        $data=$request->validated();
        $path="";
        if ($request->has('image')){

            $imageData = $request->input('image');

            // فصل البيانات عن الترويسة (header)

            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);

                // فك تشفير الصورة
                $imageData = base64_decode($imageData);
                if ($imageData === false) {
                    return ApiResponse::sendResponse(400, 'خطأ في فك تشفير الصورة', []);
                }

                // إنشاء اسم فريد للصورة
                $imageName = time() . '_' . random_int(100000000, 999999999) . '.' . $type;

                // تخزين الصورة

                Storage::disk('posts')->put('users image/' . $imageName, $imageData);
                $path = 'users image/' . $imageName;
            } else {
                return ApiResponse::sendResponse(400, 'بيانات الصورة غير صحيحة', []);
            }
        }

        $cvData = $request->input('cv');

        // فصل البيانات عن الترويسة (header)

        if (preg_match('/^data:pdf\/(\w+);base64,/', $cvData, $typeCv)) {
            $cvData = substr($cvData, strpos($cvData, ',') + 1);
            $typeCv = strtolower($typeCv[1]);

            // فك تشفير الصورة
            $cvData = base64_decode($cvData);
            if ($cvData === false) {
                return ApiResponse::sendResponse(400, 'خطأ في فك تشفير cv', []);
            }

            // إنشاء اسم فريد للصورة
            $cvName = time() . '_' . random_int(100000000, 999999999) . '.' . $typeCv;

            // تخزين الصورة

            Storage::disk('posts')->put('cv/' . $cvName, $cvData);
            $pathCv = 'cv/' . $cvName;
        } else {
            return ApiResponse::sendResponse(400, 'بيانات cv غير صحيحة', []);
        }
//        $cv = time() . '_' . random_int(100000000, 999999999) . '.' . $request->file('cv')->getClientOriginalExtension();
//        $cvPath=$request->file('cv')->storeAs('cv',$cv,'posts');

        $user=auth()->user();
        DB::table('users')->where('id',$user->id)->update([
            'image'=>$path,
            'cv'=>$pathCv,
            'role_id'=>$request->role_id,
            'phone'=>$request->phone,
        ]);
         return ApiResponse::sendResponse(200,'your Profile Updated Successfully',[]);
    }
//    public function updateClientProfile(ClientProfileRequest $request)
//    {
//        $data=$request->validated();
//
//        $path="";
//        if ($request->hasfile('image')){
//            $image = time() . '_' . random_int(100000000, 999999999) . '.' . $request->file('image')->getClientOriginalExtension();
//            $path=$request->file('image')->storeAs('users image',$image,'posts');
//        }
//
//        $user=auth()->user();
//        DB::table('users')->where('id',$user->id)->update([
//            'image'=>$path,
//            'role_id'=>$request->role_id,
//        ]);
//        return ApiResponse::sendResponse(200,'your Profile Updated Successfully',[]);
//    }

    public function updateClientProfile(ClientProfileRequest $request)
    {
        $data = $request->validated();

        $path = "";
        $pathCv="";
        if ($request->has('image')) {
            $imageData = $request->input('image');

            // فصل البيانات عن الترويسة (header)

            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);

                // فك تشفير الصورة
                $imageData = base64_decode($imageData);
                if ($imageData === false) {
                    return ApiResponse::sendResponse(400, 'خطأ في فك تشفير الصورة', []);
                }

                // إنشاء اسم فريد للصورة
                $imageName = time() . '_' . random_int(100000000, 999999999) . '.' . $type;

                // تخزين الصورة

                Storage::disk('posts')->put('users image/' . $imageName, $imageData);
                $path = 'users image/' . $imageName;
            } else {
                return ApiResponse::sendResponse(400, 'بيانات الصورة غير صحيحة', []);
            }
        }

        if ($request->has('document')) {
            $cvData = $request->input('document');

            // فصل البيانات عن الترويسة (header)

            if (preg_match('/^data:pdf\/(\w+);base64,/', $cvData, $typeCv)) {
                $cvData = substr($cvData, strpos($cvData, ',') + 1);
                $typeCv = strtolower($typeCv[1]);

                // فك تشفير الصورة
                $cvData = base64_decode($cvData);
                if ($cvData === false) {
                    return ApiResponse::sendResponse(400, 'خطأ في فك تشفير document', []);
                }

                // إنشاء اسم فريد للصورة
                $cvName = time() . '_' . random_int(100000000, 999999999) . '.' . $typeCv;

                // تخزين الصورة

                Storage::disk('posts')->put('docs/' . $cvName, $cvData);
                $pathCv = 'docs/' . $cvName;
            } else {
                return ApiResponse::sendResponse(400, 'بيانات docs غير صحيحة', []);
            }
        }
        $user = auth()->user();
        DB::table('users')->where('id', $user->id)->update([
            'image' => $path,
            'role_id' => $request->role_id,
            'phone'=>$request->phone
        ]);
        $project=Project::create([
            'project_type'=>$request->project_type,
            'project_description'=>$request->project_description,
            'requirements' => $request->requirements,
            'document'=>$pathCv,
            'cooperation_type'=>$request->cooperation_type,
            'contact_time'=>$request->contact_time,
            'client_id'=>$user->id,
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

        return ApiResponse::sendResponse(200, 'تم تحديث الملف الشخصي بنجاح', []);
    }
    public function updateClientProfileForApp(ClientAppRequest $request)
    {
        $data = $request->validated();
        DB::table('users')->where('id',auth()->user()->id)->update([
            'role_id'=>$request->role_id
        ]);
        return ApiResponse::sendResponse(200, 'تم تحديث الملف الشخصي بنجاح', []);
    }
    public function updateFreelancerProfile(FreelancerProfileRequest $request)
    {
        $data = $request->validated();

        $path = "";
        if ($request->has('image')) {
            $imageData = $request->input('image');

            // فصل البيانات عن الترويسة (header)

            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);

                // فك تشفير الصورة
                $imageData = base64_decode($imageData);
                if ($imageData === false) {
                    return ApiResponse::sendResponse(400, 'خطأ في فك تشفير الصورة', []);
                }

                // إنشاء اسم فريد للصورة
                $imageName = time() . '_' . random_int(100000000, 999999999) . '.' . $type;

                // تخزين الصورة

                Storage::disk('posts')->put('users image/' . $imageName, $imageData);
                $path = 'users image/' . $imageName;
            } else {
                return ApiResponse::sendResponse(400, 'بيانات الصورة غير صحيحة', []);
            }
        }

        $user = auth()->user();
        DB::table('users')->where('id', $user->id)->update([
            'image' => $path,
            'role_id' => $request->role_id,
            'phone'=>$request->phone,
        ]);
        return ApiResponse::sendResponse(200, 'تم تحديث الملف الشخصي بنجاح', []);
    }
    public function updateUserProfile(FreelancerProfileRequest $request)
    {
        $data = $request->validated();

        $path = "";
        if ($request->has('image')) {
            $imageData = $request->input('image');

            // فصل البيانات عن الترويسة (header)

            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);

                // فك تشفير الصورة
                $imageData = base64_decode($imageData);
                if ($imageData === false) {
                    return ApiResponse::sendResponse(400, 'خطأ في فك تشفير الصورة', []);
                }

                // إنشاء اسم فريد للصورة
                $imageName = time() . '_' . random_int(100000000, 999999999) . '.' . $type;

                // تخزين الصورة

                Storage::disk('posts')->put('users image/' . $imageName, $imageData);
                $path = 'users image/' . $imageName;
            } else {
                return ApiResponse::sendResponse(400, 'بيانات الصورة غير صحيحة', []);
            }
        }

        $user = auth()->user();
        DB::table('users')->where('id', $user->id)->update([
            'image' => $path,
            'role_id' => $request->role_id,
            'phone'=>$request->phone
        ]);
        return ApiResponse::sendResponse(200, 'تم تحديث الملف الشخصي بنجاح', []);
    }
    public function showUsers()
    {
//        $users=User::where('approved',0)->get();
        $users=QueryBuilder::for(User::class)
            ->allowedFilters(['approved','role_id'])
            ->get();
        if (count($users)>0){
            return ApiResponse::sendResponse(200,'users with filter retrieved successfully',UserResource::collection($users));
        }
        return ApiResponse::sendResponse(200,'users with filter not retrieved successfully');
    }

    public function markApproved($userId)
    {
        $user=User::findOrFail($userId);
        $user->approved= !$user->approved;
        $user->save();
        return ApiResponse::sendResponse(200,'User approved Successfully',[]);
    }
    public function profile()
    {
        $user=auth()->user();
        if ($user){
            return ApiResponse::sendResponse(200,'user retrieved successfully',new UserResource($user));
        }
        return ApiResponse::sendResponse(200,'user not retrieved successfully',[]);
    }
    public function showClients()
    {
        $clientRoleId=Role::where('role','client')->pluck('id')->first();
        $clients=User::where('role_id',$clientRoleId)->get();
        if ($clients){
            return ApiResponse::sendResponse(200,'clients retrieved successfully',ClientResource::collection($clients));
        }
        return ApiResponse::sendResponse(200,'clients retrieved successfully',[]);
    }

}
