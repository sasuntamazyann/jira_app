<?php

namespace App\Http\Requests\Issue;

use App\Models\IssueType;
use App\Services\IssueService;
use App\Services\IssueTypeService;
use Illuminate\Foundation\Http\FormRequest;
use Closure;
use Illuminate\Validation\Rule;

class CreateIssueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'summary' => 'required|string|max:256',
            'description' => 'required|string|max:2048',
            'type' => 'required|exists:issue_types,id',
            'parent' => [Rule::requiredIf(fn () => IssueType::where('id', $this->type)->where('name', 'Subtask')->exists()), 'exists:issues,id']
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (isset($data['parent'])) {
            $data['parentKey'] = (new IssueService())->find($data['parent'])->key;
        }

        return $data;
    }
}
