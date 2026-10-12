<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Models\PriceBookItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class search_price_items_request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'group' => ['nullable', Rule::in(array_keys(PriceBookItem::GROUPS))],
        ];
    }
}
