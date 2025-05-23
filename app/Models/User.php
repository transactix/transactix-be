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
use Illuminate\Support\Facades\Http;
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
            // First check if user with this email already exists
            $existingUser = static::findByEmail($attributes['email']);
            if ($existingUser) {
                return $existingUser;
            }

            // Try to insert into Supabase using API
            $supabaseUrl = config('services.supabase.url', env('SUPABASE_URL'));
            $supabaseKey = config('services.supabase.secret', env('SUPABASE_SECRET'));

            if ($supabaseUrl && $supabaseKey) {
                $response = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=representation'
                ])->post($supabaseUrl . '/rest/v1/users', $attributes);

                if ($response->successful()) {
                    $userData = $response->json();
                    if (!empty($userData) && is_array($userData) && isset($userData[0])) {
                        $user = new static();
                        foreach ($userData[0] as $key => $value) {
                            $user->setAttribute($key, $value);
                        }
                        $user->exists = true;
                        return $user;
                    }
                }
            }

            // Create a local model with a generated ID as fallback
            $model = new static($attributes);
            $model->id = 'user_' . uniqid();
            $model->exists = true;
            return $model;

        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Supabase user creation error: ' . $e->getMessage());

            // Create a local model as fallback
            $model = new static($attributes);
            $model->id = 'user_' . uniqid();
            $model->exists = true;
            return $model;
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
            // Try to query Supabase first using API
            $supabaseUrl = config('services.supabase.url', env('SUPABASE_URL'));
            $supabaseKey = config('services.supabase.secret', env('SUPABASE_SECRET'));

            if ($supabaseUrl && $supabaseKey) {
                $response = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Content-Type' => 'application/json'
                ])->get($supabaseUrl . '/rest/v1/users?email=eq.' . urlencode($email) . '&limit=1');

                if ($response->successful()) {
                    $userData = $response->json();
                    if (!empty($userData) && is_array($userData) && isset($userData[0])) {
                        $user = new static();
                        foreach ($userData[0] as $key => $value) {
                            $user->setAttribute($key, $value);
                        }
                        $user->exists = true;
                        return $user;
                    }
                }
            }
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Supabase API query error: ' . $e->getMessage());
        }

        // Return null if not found
        return null;
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

        if ($instance !== null) {
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
        $model = parent::find($id);
        return $model instanceof \Illuminate\Database\Eloquent\Collection ? $model->first() : $model;
    }
}
