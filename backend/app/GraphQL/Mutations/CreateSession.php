<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\FocusSession;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

// createSession mutation の処理を行うクラス
final readonly class CreateSession
{
    /** @param  array{planned_minutes: int, actual_minutes: int, rating: string, started_at: string, ended_at: string}  $args */
    public function __invoke(null $_, array $args): FocusSession
    {
        // 予定より短く終えたセットは「まだいけた」を選べない（途中で終了した扱いのため）
        if ($args['actual_minutes'] < $args['planned_minutes'] && $args['rating'] === 'MORE') {
            throw new Error('途中で終了したセットでは「まだいけた」を選べません');
        }

        // ログイン中のユーザーの記録として保存する
        // user_idはクライアントから受け取れないようにする（任意の名前で記録を作れてしまうから）
        return FocusSession::create([
            'user_id' => Auth::id(),
            ...$args,
        ]);
    }
}
