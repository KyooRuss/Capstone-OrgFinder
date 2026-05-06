<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationTestimonial extends Model
{
    protected $fillable = ['organization_id', 'testimonial', 'author', 'order_index'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
