<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\FocusSession;
use App\Services\UserStateService;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

// createSession mutation の処理を行うクラス
final readonly class CreateSession
{
    public function __construct(private UserStateService $userStates) {}

    /** @param  array{planned_minutes: int, actual_minutes: int, rating: string, started_at: string, ended_at: string}  $args */
    public function __invoke(null $_, array $args): FocusSession
    {
        // ログイン中のユーザーの記録として作る
        // user_idはクライアントから受け取れないようにする（任意の名前で記録を作れてしまうから）
        $session = new FocusSession(['user_id' => Auth::id(), ...$args]);

        if ($session->hasInvalidRating()) {
            throw new Error('途中で終了したセットでは「まだいけた」を選べません');
        }

        $session->save();

        // 記録が増えたので、基準セット長を作り直す
        $this->userStates->recalculate(Auth::user());

        return $session;
    }
}
