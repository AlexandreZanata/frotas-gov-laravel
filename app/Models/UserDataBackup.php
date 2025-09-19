<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDataBackup extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'deleted_user_name',
        'deleted_user_email',
        'report_summary',
        'pdf_content',
    ];
}
