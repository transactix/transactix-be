<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends SupabaseModel
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
     * @return static|null
     */
    public static function create(array $attributes, bool $useServiceRole = true)
    {
        // Hash the password if it's not already hashed
        if (isset($attributes['password']) && !Hash::isHashed($attributes['password'])) {
            $attributes['password'] = Hash::make($attributes['password']);
        }

        return parent::create($attributes, $useServiceRole);
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
        $instance = new static;
        $result = \App\Facades\Supabase::query(
            $instance->table,
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
