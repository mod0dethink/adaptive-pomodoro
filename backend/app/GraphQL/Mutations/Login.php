<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Hash;

// login mutation の処理を行うクラス
final readonly class Login
{
    /** @param  array{email: string, password: string}  $args */
    public function __invoke(null $_, array $args): array
    {
        // メールアドレスでユーザーを探す
        $user = User::where('email', $args['email'])->first();

        // ユーザーがいない、またはパスワードが違う場合は同じエラーにする（どちらが違うかを教えない）
        if (! $user || ! Hash::check($args['password'], $user->password)) {
            throw new Error('メールアドレスまたはパスワードが違います');
        }

        // 合っていれば、新しいトークンを発行して返す
        return ['token' => $user->createToken('auth')->plainTextToken];
    }
}
