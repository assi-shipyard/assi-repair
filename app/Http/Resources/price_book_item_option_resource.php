<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class price_book_item_option_resource extends JsonResource
{
    // JsonResource requires this framework-defined method name.
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->unique_id,
            'text' => $this->code.' — '.$this->name,
            'name' => $this->name,
            'group' => $this->group,
            'unit' => $this->unit,
            'calculation_rule' => $this->calculation_rule,
            'unit_price' => $this->unit_price,
            'minimum_quantity' => $this->has_minimum_charge ? $this->minimum_quantity : null,
        ];
    }
}
