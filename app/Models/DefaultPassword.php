<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;

class DefaultPassword extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'password_plain', 'is_active', 'user_id'];

    // Mutator para encriptar a senha sempre que ela for definida
    protected function passwordPlain(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Hash::make($value),
        );
    }
}
