<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */

    protected $statusText;
    protected $statusCode;

    public function __construct($resource, $statusCode = 200, $statusText = "success")
    {
        parent::__construct($resource);
        $this->statusText = $statusText;
        $this->statusCode = $statusCode;
    }
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            "data" => $this->collection,
            "success" => $this->statusCode,
            "title" => $this->statusText,
            "count" => $this->collection->count()
        ];
    }
}
