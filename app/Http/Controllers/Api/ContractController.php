<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContractRequest;
use App\Http\Requests\ProjectManagerApproveContractRequest;
use App\Http\Requests\ProjectManagerUpdateContractRequest;
use App\Http\Requests\RequestEditContract;
use App\Http\Requests\RequestEditContractRequest;
use App\Http\Requests\SetSignRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Spatie\FlareClient\Api;
use Spatie\QueryBuilder\QueryBuilder;
use function Laravel\Prompts\table;
use function PHPUnit\Framework\returnArgument;

class ContractController extends Controller
{
    public function show()
    {
        $contracts=QueryBuilder::for(Contract::class)
            ->allowedFilters('status')
            ->where('contract_manager_status',1)
            ->where('project_manager_status',1)
            ->latest()
            ->get();
        if (count($contracts) >=1){
            return ApiResponse::sendResponse(200,'contracts retrieved successfully',ContractResource::collection($contracts));
        }
        return ApiResponse::sendResponse(200,'contracts not retrieved successfully',[]);
    }
    public function clientContracts()
    {
        $user= auth()->user();
        $clientProjects=$user->projects->pluck('id');
        $contracts=QueryBuilder::for(Contract::class)
            ->allowedFilters('status')
            ->whereIn('project_id',$clientProjects)
            ->where('contract_manager_status',1)
            ->where('project_manager_status',1)
            ->latest()
            ->get();

        if (count($contracts) >=1){
            return ApiResponse::sendResponse(200,'client contracts retrieved successfully',ContractResource::collection($contracts));
        }
        return ApiResponse::sendResponse(200,'client contracts not retrieved successfully',[]);
    }

    public function createDraft(ContractRequest $request)
    {

        $data = $request->validated();
        $path = "";

        $cvData = $request->input('contract');

        // فصل البيانات عن الترويسة (header)

        if (preg_match('/^data:pdf\/(\w+);base64,/', $cvData, $typeCv)) {
            $cvData = substr($cvData, strpos($cvData, ',') + 1);
            $typeCv = strtolower($typeCv[1]);

            // فك تشفير الصورة
            $cvData = base64_decode($cvData);
            if ($cvData === false) {
                return ApiResponse::sendResponse(400, 'خطأ في فك تشفير contract', []);
            }

            // إنشاء اسم فريد للصورة
            $cvName = time() . '_' . random_int(100000000, 999999999) . '.' . $typeCv;

            // تخزين الصورة

            Storage::disk('posts')->put('contracts/' . $cvName, $cvData);
            $pathCv = 'contracts/' . $cvName;
        } else {
            return ApiResponse::sendResponse(400, 'بيانات contract غير صحيحة', []);
        }

        $data['contract'] = $pathCv; // استخدام 'file' بدلاً من 'image'
        $draft = Contract::create($data);


        if ($draft){
            DB::table('projects')->where('id',$request->project_id)->update([
                'status'=>'creating_contract'
            ]);
            return ApiResponse::sendResponse(200,'your Contract Created Successfully',new ContractResource($draft));
        }
        return ApiResponse::sendResponse(200, 'contract not created successfully',[]);
    }
    public function contractManagerContracts()
    {
        $contracts= Contract::where('contract_manager_status','pending')->get();
        if ($contracts){
            return ApiResponse::sendResponse(200,'contract Manager Contracts retrieved successfully',ContractResource::collection($contracts));
        }
        return ApiResponse::sendResponse(200,'contract Manager Contracts not retrieved successfully',[]);
    }
    public function update(UpdateContractRequest $request)
    {
        $data = $request->validated();
        $path = "";

        $cvData = $request->input('contract');

        // فصل البيانات عن الترويسة (header)

        if (preg_match('/^data:pdf\/(\w+);base64,/', $cvData, $typeCv)) {
            $cvData = substr($cvData, strpos($cvData, ',') + 1);
            $typeCv = strtolower($typeCv[1]);

            // فك تشفير الصورة
            $cvData = base64_decode($cvData);
            if ($cvData === false) {
                return ApiResponse::sendResponse(400, 'خطأ في فك تشفير contract', []);
            }

            // إنشاء اسم فريد للصورة
            $cvName = time() . '_' . random_int(100000000, 999999999) . '.' . $typeCv;

            // تخزين الصورة

            Storage::disk('posts')->put('contracts/' . $cvName, $cvData);
            $pathCv = 'contracts/' . $cvName;
        } else {
            return ApiResponse::sendResponse(400, 'بيانات contract غير صحيحة', []);
        }

        DB::table('contracts')->where('id',$request->contract_id)->update([
            'contract'=>$pathCv,
            'contract_manager_status'=>1,
            'project_manager_status'=>0,
            'need_edit'=>0,
            'client_edit_request'=>""
    ]);

        return ApiResponse::sendResponse(200, 'Cmanager update contract successfully',[]);
    }

    public function projectManagerUpdate(ProjectManagerUpdateContractRequest $request)
    {
        $data = $request->validated();
        $path = "";

        $cvData = $request->input('contract');

        // فصل البيانات عن الترويسة (header)

        if (preg_match('/^data:pdf\/(\w+);base64,/', $cvData, $typeCv)) {
            $cvData = substr($cvData, strpos($cvData, ',') + 1);
            $typeCv = strtolower($typeCv[1]);

            // فك تشفير الصورة
            $cvData = base64_decode($cvData);
            if ($cvData === false) {
                return ApiResponse::sendResponse(400, 'خطأ في فك تشفير contract', []);
            }

            // إنشاء اسم فريد للصورة
            $cvName = time() . '_' . random_int(100000000, 999999999) . '.' . $typeCv;

            // تخزين الصورة

            Storage::disk('posts')->put('contracts/' . $cvName, $cvData);
            $pathCv = 'contracts/' . $cvName;
        } else {
            return ApiResponse::sendResponse(400, 'بيانات contract غير صحيحة', []);
        }

        DB::table('contracts')->where('id',$request->contract_id)->update([
            'contract'=>$pathCv,
            'contract_manager_status'=>0,
            'project_manager_status'=>1,
            'need_edit'=>0,
            'client_edit_request'=>""
        ]);

        return ApiResponse::sendResponse(200, 'Pmanager update contract successfully',[]);
    }
    public function projectManagerApprove(ProjectManagerApproveContractRequest $request)
    {
        DB::table('contracts')->where('id',$request->contract_id)->update([
            'project_manager_status'=>1
        ]);

        return ApiResponse::sendResponse(200, ' project manager approve contract successfully',[]);
    }
    public function contractManagerApprove(ProjectManagerApproveContractRequest $request)
    {
        DB::table('contracts')->where('id',$request->contract_id)->update([
            'contract_manager_status'=>1
        ]);

        return ApiResponse::sendResponse(200, 'contract manager approve contract successfully',[]);
    }

    public function addSignature(SetSignRequest $request)
    {
        $request->validated();
        $signaturePath="";
        $imageData = $request->input('signature');

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

            Storage::disk('posts')->put('signatures/' . $imageName, $imageData);
            $signaturePath = 'signatures/' . $imageName;
        } else {
            return ApiResponse::sendResponse(400, 'بيانات الصورة غير صحيحة', []);
        }

        // معالجة الصورة لإزالة الخلفية البيضاء
        $this->removeWhiteBackground(public_path($signaturePath));

        $contract = Contract::findOrFail($request->contract_id);
        $contractPath = $contract->pluck('contract')[0];

        $pdf = new Fpdi();
        $filePath = public_path($contractPath);
        $pageCount = $pdf->setSourceFile($filePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $pdf->AddPage();
            $pdf->useTemplate($templateId);
            if ($pageNo == $pageCount) {
                $pdf->Image(public_path($signaturePath), 10, 250, 50);
            }
        }

        $newFileName = time() . '_signed_' . random_int(100000000, 999999999) . '.pdf';
        $outputPath = public_path('contracts/' . $newFileName);
        $pdf->Output($outputPath, 'F');

        $contract->contract = 'contracts/' . $newFileName; // تحديث المسار الجديد
        $contract->client_sign= $signaturePath;
        $contract->status=1;
        $contract->save(); // حفظ التغييرات
        DB::table('projects')->where('id',$contract->project_id)->update([
            'status'=>'signed_by_client'
        ]);
        return ApiResponse::sendResponse(200,'contract signed successfully',[]);
    }

    protected function removeWhiteBackground($filePath)
    {
        // تحميل الصورة
        $img = imagecreatefrompng($filePath);
        if (!$img) {
            return;
        }
        $whiteColor = imagecolorallocatealpha($img, 255, 255, 255,127);
        // الحصول على أبعاد الصورة
        $width = imagesx($img);
        $height = imagesy($img);

        // تفعيل الشفافية
        imagealphablending($img, false);
        imagesavealpha($img, true);

        // معالجة كل بكسل لجعل الخلفية البيضاء والفضية شفافة
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = imagecolorat($img, $x, $y);
                $colors = imagecolorsforindex($img, $rgba);

                // إذا كانت الألوان قريبة من الأبيض أو الفضي
                if (($colors['red'] > 200 && $colors['green'] > 200 && $colors['blue'] > 200) ||
                    ($colors['red'] > 200 && $colors['green'] > 200 && $colors['blue'] < 200)) {
                    // تعيين البكسل إلى الأبيض
                    imagesetpixel($img, $x, $y, $whiteColor);
                }
            }
        }
        // حفظ الصورة المعدلة
        imagepng($img, $filePath); // احفظ كصورة PNG
        imagedestroy($img); // تحرير الذاكرة
    }
    public function requestEdit(RequestEditContractRequest $request)
    {
        $data= $request->validated();
        DB::table('contracts')->where('id',$request->contract_id)->update([
            'client_edit_request'=>$request->client_edit_request,
            'need_edit'=>1,
            'contract_manager_status'=>0,
            'project_manager_status'=>0,
        ]);
        return ApiResponse::sendResponse(200,'client requested edit_contract successfully',[]);
    }
    public function adminContracts()
    {
        $contracts=QueryBuilder::for(Contract::class)
            ->allowedFilters('admin_sign')
            ->where('contract_manager_status',1)
            ->where('project_manager_status',1)
            ->where('status',1)
            ->latest()
            ->get();

        if (count($contracts) >=1){
            return ApiResponse::sendResponse(200,'admin contracts retrieved successfully',ContractResource::collection($contracts));
        }
        return ApiResponse::sendResponse(200,'admin contracts not retrieved successfully',[]);
    }

    public function addSignature2(SetSignRequest $request)
    {
        $request->validated();
        $signaturePath="";
        $imageData = $request->input('signature');

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

            Storage::disk('posts')->put('signatures/' . $imageName, $imageData);
            $signaturePath = 'signatures/' . $imageName;
        } else {
            return ApiResponse::sendResponse(400, 'بيانات الصورة غير صحيحة', []);
        }

        // معالجة الصورة لإزالة الخلفية البيضاء
        $this->removeWhiteBackground(public_path($signaturePath));

        $contract = Contract::findOrFail($request->contract_id);
        $contractPath = $contract->pluck('contract')[0];

        $pdf = new Fpdi();
        $filePath = public_path($contractPath);
        $pageCount = $pdf->setSourceFile($filePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $pdf->AddPage();
            $pdf->useTemplate($templateId);
            if ($pageNo == $pageCount) {
                $pdf->Image(public_path($signaturePath), 150, 250, 50);
            }
        }

        $newFileName = time() . '_signed_' . random_int(100000000, 999999999) . '.pdf';
        $outputPath = public_path('contracts/' . $newFileName);
        $pdf->Output($outputPath, 'F');

        $contract->contract = 'contracts/' . $newFileName; // تحديث المسار الجديد
        $contract->admin_sign=1;
        $contract->save(); // حفظ التغييرات
        DB::table('projects')->where('id',$contract->project_id)->update([
            'status'=>'signed_by_manager'
        ]);
        return ApiResponse::sendResponse(200,'contract signed successfully',[]);
    }

}
