<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

// create() で一度に書き込んでよい項目（これ以外は書き込めない）
#[Fillable(['user_id', 'planned_minutes', 'actual_minutes', 'rating', 'started_at', 'ended_at'])]
class FocusSession extends Model
{
    // 作った直後のオブジェクトにも、DB の初期値と同じ値を持たせる
    protected $attributes = [
        'break_skipped' => false,
    ];

    // DB から取り出すとき、日時は日付オブジェクト、break_skipped は true/false に変換する
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'break_skipped' => 'boolean',
        ];
    }
}
