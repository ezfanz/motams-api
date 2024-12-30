<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'password',
        'fullname',
        'nokp',
        'phone',
        'unit',
        'jawatan',
        'gred',
        'kump_khidmat',
        'ketua_jbtn',
        'telegram_id',
        'encrypt',
        'remember_token',
        'role_id',
        'department_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['last_login_at' => 'datetime'];

    public function getDeletedAtColumn()
    {
        return 'is_deleted';
    }

    protected static function booted()
    {
        static::addGlobalScope('is_deleted', function ($builder) {
            $builder->where('is_deleted', '=', 0);
        });
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Single role relationship using role_id.
     */
    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }  

                                                                        
    public function hasRole($roleName)
    {
        return $this->role && $this->role->diskripsi === $roleName;
    }

    public function assignRole($roleName)
    {
        $role = Role::where('diskripsi', $roleName)->first();

        if ($role) {
            $this->role_id = $role->id;
            $this->save();
        }
    }

    public function removeRole()
    {
        $this->role_id = null;
        $this->save();
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'user_id', 'id');
    }
}
