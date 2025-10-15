<?php namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, 
                                    AuthorizableContract,
                                    CanResetPasswordContract
{

    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
    
    public function getRoleAttribute()
    {
        $firstRole = $this->roles()->first();
        return $firstRole ? $firstRole->name : null;
        //return $this->attributes['role'];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Add role to user.
     *
     * @param mixed $role
     * @return void
     */
    public function attachRole($role)
    {
        // If $role is object, use it ID
        if ($role instanceof Role) {
            $role = $role->id;
        }

        // If $role is name, find ID 
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail()->id;
        }

        // append role to user
        $this->roles()->attach($role);
    }
}
