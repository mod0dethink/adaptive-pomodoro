<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

final readonly class DeleteSession
{
    /** @param  array{id: string}  $args */
    public function __invoke(null $_, array $args): bool
    {
        // 自分の記録の中から探す（他人の記録は見つからない扱いになり、消せない）
        $session = Auth::user()->focusSessions()->find($args['id']);

        if (! $session) {
            throw new Error('記録が見つかりません');
        }

        $session->delete();

        return true;
    }
}
