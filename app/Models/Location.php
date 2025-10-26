<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        "name",
        "longitude",
        "latitude"
    ];

    protected $table = "locations";

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
