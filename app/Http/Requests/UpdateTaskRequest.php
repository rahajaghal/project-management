<?php

namespace App\Http\Requests;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    protected  function failedValidation(Validator $validator)
    {
        if ($this->is('api/*')){
            //                                                    errors()                     messages()->all()
            $response=ApiResponse::sendResponse(422,'Validation Errors',$validator->messages()->all());
            throw new ValidationException($validator,$response);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $priority=config('special.priority');
        return [
            'title'=>'nullable|string',
            'description'=>'nullable|string',
            'project_id'=>'required|exists:projects,id',
            'sprint_id'=>'nullable|exists:sprints,id',
            'status_id'=>'required|exists:statuses,id',
            'user_id'=>'required|exists:users,id',
            'priority'=>'required|string|in:' . implode(',',array_keys($priority)),
            'completion'=>'string',
            'start'=>'required|date',
            'end'=>'required|date',
            'task_id'=>'required|exists:tasks,id'
        ];
    }
}
