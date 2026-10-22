<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;

// register mutation の処理を行うクラス
final readonly class Register
{
    // 引数 $args に、スキーマで受け取った email と password が入ってくる
    /** @param  array{email: string, password: string}  $args */
    public function __invoke(null $_, array $args): array
    {
        // users の表に1行追加する（password はモデルの設定で自動的に暗号化される）
        $user = User::create([
            'email' => $args['email'],
            'password' => $args['password'],
        ]);

        // トークンを発行し、その文字列を token という項目で返す（AuthPayload の形に合わせる）
        return ['token' => $user->createToken('auth')->plainTextToken];
    }
}
