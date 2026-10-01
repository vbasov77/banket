<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObjFeature extends Model
{
    use HasFactory;

    protected $table = 'obj_features';

    public $timestamps = false;

    protected $fillable = [
        'obj_id', 'banquet_note', 'prepayment',
        'textile_package', 'textile_colors',
        'tables', 'parking', 'pier',
        'equipment', 'kids', 'interior', 'location',
    ];

    protected $casts = [
        'textile_package' => 'array',
        'tables'          => 'array',
        'equipment'        => 'array',
        'kids'             => 'array',
        'location'         => 'array',
    ];

    public function obj(): BelongsTo
    {
        return $this->belongsTo(Obj::class, 'obj_id', 'id');
    }
}
