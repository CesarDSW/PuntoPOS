<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
   use Notifiable;

   protected $table = 'userr';
   protected $primaryKey = 'userr_id';

   public $timestamps = false;

   protected $fillable = [
       'name_user',
       'phone',
       'email',
       'name_company',
       'password',
       'rol_idfk',
       'company_idfk',
       'state',
   ];

   protected $hidden = [
    'password',
    'remember_token',
   ];

}
