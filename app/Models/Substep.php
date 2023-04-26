<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

//Substepモデル
class Substep extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'time_aim',
        'order',
    ];


    //各モデルとのリレーション
    public function step()
    {
        return $this->belongsTo(Step::class)->withTrashed();
    }
    public function User()
    {
        return $this->belongsTo(User::class);
    }
    public function challengeSubStep()
    {
        return $this->hasMany(Challenge::class);
    }
}
