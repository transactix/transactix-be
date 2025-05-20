<?php

namespace App\Models;

use App\Facades\Supabase;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Model implements Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, AuthenticatableTrait, HasApiTokens;

    /**
     * The Supabase table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Create a new user instance.
     *
     * @param array $attributes
     * @return static
     */
    public static function create(array $attributes = [])
    {
        // Hash the password if it's not already hashed
        if (isset($attributes['password']) && !Hash::isHashed($attributes['password'])) {
            $attributes['password'] = Hash::make($attributes['password']);
        }

        // Add timestamps if enabled
        $now = now()->toDateTimeString();
        $attributes['created_at'] = $now;
        $attributes['updated_at'] = $now;

        $result = Supabase::insert('users', $attributes, true);

        if (empty($result)) {
            // Create a new instance with the attributes if Supabase insert fails
            return new static($attributes);
        }

        return new static($result[0]);
    }

    /**
     * Check if the user is an administrator.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a cashier.
     *
     * @return bool
     */
    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    /**
     * Find a user by their email address.
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail(string $email)
    {
        $result = Supabase::query(
            'users',
            [
                'where' => ['email' => $email],
                'limit' => 1
            ],
            true
        );

        if (empty($result)) {
            return null;
        }

        return new static($result[0]);
    }
}
