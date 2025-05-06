<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class AuthController
{
    public function redirectToGithub(): SymfonyRedirectResponse
    {
        /** @var GithubProvider $githubDriver */
        $githubDriver = Socialite::driver('github');

        return $githubDriver->stateless()->redirect();
    }

    public function handleGithubCallback(): RedirectResponse
    {
        try {
            /** @var GithubProvider $githubDriver */
            $githubDriver = Socialite::driver('github');
            /** @var SocialiteUser $githubUser */
            $githubUser = $githubDriver->stateless()->user();

            $user = User::query()->firstOrCreate(
                ['github_id' => $githubUser->getId()],
                [
                    'name' => $githubUser->getName(),
                    'email' => $githubUser->getEmail(),
                    'password' => bcrypt(Str::random()),
                ]
            );

            $token = $user->createToken('github-auth')->plainTextToken;

            return redirect(config('app.url')."/auth/callback?token=$token");
        } catch (Exception $e) {
            Log::error('Failed to process github callback: '.$e->getMessage());

            return redirect('/login')->withErrors('Github auth error.');
        }
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
