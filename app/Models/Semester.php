<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Semester extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $table = 'semester';

   protected $fillable = [
        'id',
        'semester_name',
        'academic_year',
        'start_date',
        'end_date',
        'active_status',
        'description',
    ];
    protected static function boot()
    {
        parent::boot();

        // Generate UUID saat membuat record baru
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }
}
