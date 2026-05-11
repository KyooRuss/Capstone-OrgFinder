<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipRequest extends Model
{
    protected $fillable = ['user_id', 'organization_id', 'social_media_link', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
