<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\FocusSession;
use App\Services\UserStateService;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

final readonly class UpdateSession
{
    public function __construct(private UserStateService $userStates) {}

    /** @param  array{id: string, actual_minutes?: int|null, rating?: string|null, break_skipped?: bool|null}  $args */
    public function __invoke(null $_, array $args): FocusSession
    {
        // 自分の記録の中から探す（他人の記録は見つからない扱いになり、更新できない）
        $session = Auth::user()->focusSessions()->find($args['id']);

        if (! $session) {
            throw new Error('記録が見つかりません');
        }

        // 送られてきた項目だけを変更対象にする（送られなかった項目は今の値のまま）
        $changes = array_filter(
            array_intersect_key($args, array_flip(['actual_minutes', 'rating', 'break_skipped'])),
            fn ($value) => $value !== null,
        );

        // 変更後の値で、途中終了のセットに「まだいけた」が付かないか確認してから保存する
        $session->fill($changes);

        if ($session->hasInvalidRating()) {
            throw new Error('途中で終了したセットでは「まだいけた」を選べません');
        }

        $session->save();

        // 記録が変わったので、基準セット長を作り直す
        $this->userStates->recalculate(Auth::user());

        return $session;
    }
}
