<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Jobs\FetchUserLevel;

class AuthController extends Controller
{
    /**
     * Redirect to the OAuth server for authentication.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function redirect(Request $request)
    {
        $request->session()->put('state', $state = Str::random(40));

        $query = http_build_query([
            'client_id' => config('services.oauth.client_id'),
            'redirect_uri' => config('services.oauth.redirect'),
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
            // 'prompt' => '', // "none", "consent", or "login"
        ]);

        return response()->json([
            'redirect_url' => config('services.oauth.url') . '/oauth/authorize?' . $query
        ]);
    }

    /**
     * Handle the OAuth callback.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        $state = $request->session()->pull('state');

        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            \InvalidArgumentException::class
        );

        $response = Http::asForm()->post(config('services.oauth.url') . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.oauth.client_id'),
            'client_secret' => config('services.oauth.client_secret'),
            'redirect_uri' => config('services.oauth.redirect'),
            'code' => $request->code,
        ]);

        $accessToken = $response->json('access_token');

        $userResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ])
        ->acceptJson()
        ->get(config('services.oauth.url') . '/api/user');

        $userArray = $userResponse->json();

        $user = User::updateOrCreate(
            [
                'email' => $userArray['email'],
            ],
            [
                'name' => $userArray['name'],
                'mobile' => $userArray['mobile'],
                'code' => $userArray['code'],
                'access_token' => $accessToken,
                'refresh_token' => $response->json('refresh_token'),
                'expires_in' => $response->json('expires_in'),
                'token_type' => $response->json('token_type'),
            ]
        );

        $user->update(['email_verified_at' => $userArray['email_verified_at']]);

        if($user->wasChanged('email_verified_at')) {
            $user->increment('score', 10); // Increment score for new user
        }

        Auth::login($user);

        // Dispatch job to fetch user level
        FetchUserLevel::dispatch($user);

        $token = $user->createToken('auth-token')->plainTextToken;

        return redirect(config('services.oauth.app_url') . '?token=' . $token);
    }

    /**
     * Get the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }


    /**
     * Log out the user and revoke all tokens.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete(); // Revoke all tokens
        Auth::logout(); // Log out the user
        $request->session()->invalidate(); // Invalidate the session
        $request->session()->regenerateToken(); // Regenerate CSRF token

        return response()->json(['message' => 'Logged out successfully']);
    }
}
