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

        try {
            // Try to insert into Supabase
            $result = Supabase::insert('users', $attributes, true);

            if (!empty($result) && is_array($result) && isset($result[0])) {
                $user = new static($result[0]);
                // Ensure the ID is set
                if (!$user->id && isset($result[0]['id'])) {
                    $user->id = $result[0]['id'];
                }
                return $user;
            }

            // If Supabase insert fails or returns unexpected format, fall back to Eloquent
            return parent::create($attributes);
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Supabase user creation error: ' . $e->getMessage());

            // Check if the error is due to duplicate email
            if (strpos($e->getMessage(), 'duplicate key value') !== false && strpos($e->getMessage(), 'email') !== false) {
                // Try to find the user by email
                $user = static::where('email', $attributes['email'])->first();
                if ($user) {
                    return $user;
                }
            }

            // Fall back to Eloquent
            return parent::create($attributes);
        }
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
        try {
            // Try to query Supabase first
            $result = Supabase::query(
                'users',
                [
                    'where' => ['email' => $email],
                    'limit' => 1
                ],
                true
            );

            if (!empty($result) && is_array($result) && isset($result[0])) {
                $user = new static($result[0]);
                // Ensure the ID is set
                if (!$user->id && isset($result[0]['id'])) {
                    $user->id = $result[0]['id'];
                }
                return $user;
            }
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Supabase query error: ' . $e->getMessage());
        }

        // Fall back to Eloquent if Supabase query fails or returns no results
        return static::where('email', $email)->first();
    }

    /**
     * Get the first record matching the attributes or create it.
     *
     * @param array $attributes
     * @param array $values
     * @return static
     */
    public static function firstOrCreate(array $attributes, array $values = [])
    {
        $instance = static::where('email', $attributes['email'])->first();

        if (!is_null($instance)) {
            return $instance;
        }

        return static::create(array_merge($attributes, $values));
    }

    /**
     * Find a model by its primary key.
     *
     * @param mixed $id
     * @return static|null
     */
    public static function find($id)
    {
        try {
            // Try to query Supabase first
            $result = Supabase::query(
                'users',
                [
                    'where' => ['id' => $id],
                    'limit' => 1
                ],
                true
            );

            if (!empty($result) && is_array($result) && isset($result[0])) {
                $user = new static($result[0]);
                // Ensure the ID is set
                if (!$user->id && isset($result[0]['id'])) {
                    $user->id = $result[0]['id'];
                }
                return $user;
            }
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Supabase query error: ' . $e->getMessage());
        }

        // Fall back to Eloquent if Supabase query fails or returns no results
        return parent::find($id);
    }
}
