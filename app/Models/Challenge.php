<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

//Challengeモデル
class Challenge extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'step_id',
        'substep_id',
        'time',
        'clear_flg',
    ];

    //各モデルとのリレーション
    public function challengeUser()
    {
        return $this->belongsTo(User::class);
    }
    public function challengeSteps()
    {
        return $this->belongsTo(Step::class);
    }
    public function challengeSubsteps()
    {
        return $this->belongsTo(Substep::class);
    }

}
