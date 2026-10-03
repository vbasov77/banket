<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClickPhone extends Model
{
    protected $table = 'click_phone';
    protected $primaryKey = 'id';

    const UPDATED_AT = null;

    protected $fillable = ['subj_id', 'ip']; // <-- добавили ip

    public function subj()
    {
        return $this->belongsTo(Subj::class, 'subj_id', 'id');
    }
}
