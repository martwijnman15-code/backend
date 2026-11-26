<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['contact_name', 'phone_number', 'mail_address', 'company_name', 'user_id'];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
