<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'base_minutes', 'streak_count'])]
class UserState extends Model
{
    // 主キーは id ではなく user_id（ユーザー1人につき1行）。自動で増える番号ではない
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    // この表には created_at の列がないので、作成日時は使わない（updated_at だけ使う）
    const CREATED_AT = null;
}
