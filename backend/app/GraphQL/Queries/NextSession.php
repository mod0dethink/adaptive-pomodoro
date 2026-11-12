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
        // 今は当日補正を入れていないので、今日のセット長は基準セット長と同じ
        $setMinutes = $this->userStates->baseMinutes(Auth::user());

        return [
            'set_minutes' => $setMinutes,
            // 休憩時間はセット長の5分の1
            'break_minutes' => intdiv($setMinutes, 5),
            'adjusted' => false,
        ];
    }
}
