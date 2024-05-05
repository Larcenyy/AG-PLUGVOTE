<?php

namespace Azuriom\Plugin\Vote\Requests;

use Azuriom\Http\Requests\Traits\ConvertCheckbox;
use Illuminate\Foundation\Http\FormRequest;

class RewardRequest extends FormRequest
{
    use ConvertCheckbox;

    /**
     * The attributes represented by checkboxes.
     *
     * @var array<int, string>
     */
    protected array $checkboxes = [
        'need_online', 'is_enabled', 'double_accept',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'image' => ['nullable', 'image'],
            'image_bonus' => ['nullable', 'image'],
            'servers.*' => ['required', 'exists:servers,id'],
            'chances' => ['required', 'numeric', 'between:0,100'],
            'money' => ['nullable', 'numeric', 'min:0'],
            'money_bonus' => ['nullable', 'numeric', 'min:0'],
            'need_online' => ['filled', 'boolean'],
            'commands' => ['sometimes', 'nullable', 'array'],
            'commands_bonus' => ['sometimes', 'nullable', 'array'],
            'roles_authorized' => ['sometimes', 'nullable', 'array'],
            'monthly_rewards' => ['sometimes', 'nullable', 'array'],
            'is_enabled' => ['filled', 'boolean'],
            'double_accept' => ['filled', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeCheckboxes();

        if (! $this->filled('money')) {
            $this->merge(['money' => 0]);
        }

        if (! $this->filled('money_bonus')) {
            $this->merge(['money_bonus' => 0]);
        }

        $rewards = array_filter($this->input('monthly_rewards', []));

        $this->merge([
            'commands' => array_filter($this->input('commands', [])),
            'commands_bonus' => array_filter($this->input('commands_bonus', [])),
            'roles_authorized' => is_array($this->input('roles_authorized')) ? array_map(fn ($val) => (int) $val, $this->input('roles_authorized')) : [(int)$this->input('roles_authorized')],
            'monthly_rewards' => array_map(fn ($val) => (int) $val, $rewards),
        ]);
    }
}
