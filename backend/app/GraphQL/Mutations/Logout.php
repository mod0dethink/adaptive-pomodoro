<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;

// logout mutation の処理を行うクラス
final readonly class Logout
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args): bool
    {
        // 今リクエストで使われたトークンを DB から削除する（他の端末のトークンは残る）
        Auth::user()->currentAccessToken()->delete();

        return true;
    }
}
