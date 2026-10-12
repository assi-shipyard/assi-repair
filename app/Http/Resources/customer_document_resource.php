<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class customer_document_resource extends JsonResource
{
    // JsonResource requires this framework-defined method name.
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
