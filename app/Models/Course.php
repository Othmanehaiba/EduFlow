<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'teacher_id',
        'interest_id',
        'title',
        'description',
        'price',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function interest()
    {
        return $this->belongsTo(Interest::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}