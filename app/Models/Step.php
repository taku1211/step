<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

//Stepモデル
class Step extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'category_main',
        'category_sub',
        'content',
        'time_aim',
        'step_number',
        'iamge_path'
    ];

    //各モデルとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function substeps()
    {
        return $this->hasMany(Substep::class);
    }
    public function challengeStep()
    {
        return $this->hasMany(Challenge::class);
    }

    //ページネーションの1ページ当たりの要素数を指定
    protected $perPage = 8;
}
