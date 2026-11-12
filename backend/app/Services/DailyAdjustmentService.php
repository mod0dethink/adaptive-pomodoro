<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DailyAdjustmentService
{
    // 自己評価の点数（その日の調子を、普段と比べるために数値にする）
    private const SCORES = ['HARD' => 0, 'JUST' => 1, 'MORE' => 2];

    private const STEP_MINUTES = 5;

    // 補正をかけるのは、その日の2セット目が終わってから
    private const REQUIRED_SETS = 2;

    // 前日までの記録が、この日数たまるまでは補正しない
    private const REQUIRED_DAYS = 14;

    // 普段の平均から、標準偏差のこの個数分以上離れたら「はっきり悪い／良い」
    private const THRESHOLD = 1.5;

    // 標準偏差の下限（過去の評価がほぼ同じ人に、わずかな違いで補正がかからないようにする）
    private const MIN_DEVIATION = 0.5;

    // 日の区切りは日本時間の午前4時（4時前のセットは、前日の続きとして扱う）
    public function dayOf(CarbonInterface $startedAt): string
    {
        return $startedAt->copy()->setTimezone('Asia/Tokyo')->subHours(4)->toDateString();
    }

    // 指定した日の当日補正（-5 / 0 / +5分）を返す。その日より前の日だけを「普段」とする
    public function adjustmentFor(Collection $sessions, string $day): int
    {
        $daySets = $sessions->filter(fn ($session) => $this->dayOf($session->started_at) === $day)->count();

        if ($daySets < self::REQUIRED_SETS) {
            return 0;
        }

        $averages = $this->dailyAverages($sessions);
        $past = $averages->filter(fn ($average, $date) => $date < $day);

        if ($past->count() < self::REQUIRED_DAYS) {
            return 0;
        }

        // 過去の日ごとの平均から「普段」の平均とばらつき（母標準偏差）を出す
        $mean = $past->avg();
        $deviation = max(sqrt($past->map(fn ($average) => ($average - $mean) ** 2)->avg()), self::MIN_DEVIATION);

        // その日の平均が、普段から標準偏差何個分離れているか（小数の誤差を見込んで、わずかに甘く比べる）
        $distance = ($averages[$day] - $mean) / $deviation;

        if ($distance >= self::THRESHOLD - 1e-9) {
            return self::STEP_MINUTES;
        }

        if ($distance <= -self::THRESHOLD + 1e-9) {
            return -self::STEP_MINUTES;
        }

        return 0;
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
