<?php

namespace App\Traits;

trait ResolveTrait
{
    /**
     * Initialize the trait by appending the encrypted_{primaryKey} attribute
     * to the $appends array, ensuring it's always serialized.
     */
    public function initializeResolveTrait(): void
    {
        $encryptedKey = 'encrypted_' . $this->getKeyName();

        if (!in_array($encryptedKey, $this->appends, true)) {
            $this->appends[] = $encryptedKey;
        }
    }

    /**
     * Accessor for the encrypted primary key.
     * Always returns the same ciphertext for the same primary key.
     *
     * Example:
     *  $encrypted = $user->encrypted_id; // e.g. 'AbC12345'
     */
    public function getEncryptedIdAttribute(): string
    {
        return encryptDecrypt((string) $this->getKey(), 'encrypt');
    }

    /**
     * Resolve the model instance for route-model binding using the encrypted ID.
     * Decrypts the incoming value back to the original primary key, then queries by it.
     *
     * Example:
     *  In routes/web.php:
     *  Route::get('/users/{user}', [UserController::class, 'show']);
     *  Laravel will decrypt 'AbC12345' and fetch the User model automatically
     * 
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return static::where($this->getKeyName(), encryptDecrypt($value, 'decrypt'))->firstOrFail();
    }

    // /**
    //  * Instruct Eloquent to use 'encrypted_id' for implicit route binding key.
    //  *
    //  * Example:
    //  *  Generating a URL uses the encrypted attribute:
    //  *  route('users.show', $user); // '/users/AbC12345'
    //  */
    // public function getRouteKeyName(): string
    // {
    //     return 'encrypted_id';
    // }
}