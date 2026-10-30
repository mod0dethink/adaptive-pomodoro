<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

final readonly class Sessions
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args): Collection
    {
        // ログイン中のユーザーの記録だけを、開始日時の新しい順に取り出す
        return Auth::user()->focusSessions()->orderByDesc('started_at')->get();
    }
}
