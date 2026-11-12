<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Services\UserStateService;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

final readonly class DeleteSession
{
    public function __construct(private UserStateService $userStates) {}

    /** @param  array{id: string}  $args */
    public function __invoke(null $_, array $args): bool
    {
        // 自分の記録の中から探す（他人の記録は見つからない扱いになり、消せない）
        $session = Auth::user()->focusSessions()->find($args['id']);

        if (! $session) {
            throw new Error('記録が見つかりません');
        }

        $session->delete();

        // 記録が減ったので、基準セット長を作り直す
        $this->userStates->recalculate(Auth::user());

        return true;
    }
}
