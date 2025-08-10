<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'activity', 
        'model',
        'data'
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public static function log($name, $activity, $model = null, $data = null)
    {
        return self::create([
            'name' => $name,
            'activity' => $activity,
            'model' => $model,
            'data' => $data
        ]);
    }
}
