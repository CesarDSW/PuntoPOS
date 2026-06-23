<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $table = 'user_preferences';

    protected $primaryKey = 'user_preference_id';

    protected $fillable = [
        'userr_idfk',
        'theme',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userr_idfk', 'userr_id');
    }
}