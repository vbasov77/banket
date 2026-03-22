<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteSubj extends Model
{
    use HasFactory;

    protected $table = 'favorites_subj';

    public $timestamps = false;

    protected $fillable = ['user_id', 'subj_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Subj::class);
    }
}
