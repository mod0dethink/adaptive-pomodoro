<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserState;
use Illuminate\Support\Collection;

class UserStateService
{
    private const INITIAL_MINUTES = 25;

    private const STEP_MINUTES = 5;

    private const MIN_MINUTES = 10;

    private const MAX_MINUTES = 60;

    // 保存されている基準セット長を返す（まだ記録がなく user_states がない人は25分）
    public function baseMinutes(User $user): int
    {
        return UserState::find($user->id)?->base_minutes ?? self::INITIAL_MINUTES;
    }

    // ユーザーの記録から基準セット長と連続回数を作り直し、user_states に保存する
    public function recalculate(User $user): void
    {
        // 同じ開始日時の記録でも順番がぶれないよう、id でも並べる
        $sessions = $user->focusSessions()->orderBy('started_at')->orderBy('id')->get();

        // 記録が1件もなければ user_states は持たない（基準セット長25分として扱う）
        if ($sessions->isEmpty()) {
            UserState::where('user_id', $user->id)->delete();

            return;
        }

        UserState::updateOrCreate(['user_id' => $user->id], $this->calculate($sessions));
    }

    // 古い記録から順に自己評価を見て、階段法で基準セット長を決める
    public function calculate(Collection $sessions): array
    {
        $base = self::INITIAL_MINUTES;
        $streak = 0;

        foreach ($sessions as $session) {
            if ($session->rating === 'MORE') {
                $streak++;

                // 「まだいけた」が2回連続したら +5分にして、連続回数を0に戻す
                if ($streak >= 2) {
                    $base = min(self::MAX_MINUTES, $base + self::STEP_MINUTES);
                    $streak = 0;
                }
            } elseif ($session->rating === 'HARD') {
                $base = max(self::MIN_MINUTES, $base - self::STEP_MINUTES);
                $streak = 0;
            } else {
                $streak = 0;
            }
        }

        return ['base_minutes' => $base, 'streak_count' => $streak];
    }
}
