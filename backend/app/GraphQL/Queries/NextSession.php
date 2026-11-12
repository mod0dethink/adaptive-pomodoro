<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Services\UserStateService;
use Illuminate\Support\Facades\Auth;

final readonly class NextSession
{
    public function __construct(private UserStateService $userStates) {}

    /** @param  array{}  $args */
    public function __invoke(null $_, array $args): array
    {
        $user = Auth::user();
        $setMinutes = $this->userStates->todaySetMinutes($user);

        // 10〜60分に収めた結果も含めて、実際に基準セット長からどれだけ変わったか
        $adjustment = $setMinutes - $this->userStates->baseMinutes($user);

        return [
            'set_minutes' => $setMinutes,
            // 休憩時間はセット長の5分の1
            'break_minutes' => intdiv($setMinutes, 5),
            'adjusted' => $adjustment !== 0,
            'adjustment_minutes' => $adjustment,
        ];
    }
}
