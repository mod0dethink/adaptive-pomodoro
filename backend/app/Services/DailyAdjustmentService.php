<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DailyAdjustmentService
{
    // 自己評価の点数（その日の調子を、普段と比べるために数値にする）
    private const SCORES = ['HARD' => 0, 'JUST' => 1, 'MORE' => 2];

    // 日の区切りは日本時間の午前4時（4時前のセットは、前日の続きとして扱う）
    public function dayOf(CarbonInterface $startedAt): string
    {
        return $startedAt->copy()->setTimezone('Asia/Tokyo')->subHours(4)->toDateString();
    }

    // 日ごとに、その日の1〜2セット目の評価の平均を出す（例: ['2026-10-22' => 1.5]、日付の古い順）
    public function dailyAverages(Collection $sessions): Collection
    {
        return $sessions
            ->sortBy([['started_at', 'asc'], ['id', 'asc']])
            ->groupBy(fn ($session) => $this->dayOf($session->started_at))
            ->map(fn ($daySessions) => $daySessions->take(2)->avg(fn ($session) => self::SCORES[$session->rating]));
    }
}
