<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
   use Notifiable, TwoFactorAuthenticatable;

   protected $table = 'userr';
   protected $primaryKey = 'userr_id';

   public $timestamps = false;

   protected $fillable = [
       'name_user',
       'phone',
       'email',
       'google_id',
       'google_email',
       'name_company',
       'password',
       'two_factor_secret',
       'two_factor_recovery_codes',
       'two_factor_confirmed_at',
       'rol_idfk',
       'company_idfk',
       'state',
   ];

   protected $hidden = [
    'password',
    'remember_token',
    'two_factor_secret',
    'two_factor_recovery_codes',
   ];

   public function rol()
   {
       return $this->belongsTo(Rol::class, 'rol_idfk', 'rol_id');
   }
}
