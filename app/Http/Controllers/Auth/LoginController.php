<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserLoginSession;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    /**
     * Get the needed authorization credentials from the request.
     *
     * @return array
     */
    protected function credentials(\Illuminate\Http\Request $request)
    {
        return ['email' => $request->{$this->username()}, 'password' => $request->password, 'statut' => 1];
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return \Inertia\Inertia::render('Auth/Login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = (bool) $request->input('remember', false);

        if (Auth::attempt($this->credentials($request), $remember)) {
            // Regenerate the web session ID to prevent fixation
            $request->session()->regenerate();
            $request->session()->forget('two_factor_verified');

            // Persist the current web session ID in user_login_sessions
            try {
                $user = Auth::guard('web')->user();
                if ($user) {
                    $sessionId = $request->session()->getId();

                    UserLoginSession::query()->updateOrCreate(
                        [
                            'user_id' => (int) $user->id,
                            'session_id' => $sessionId,
                        ],
                        [
                            'access_token_id' => $sessionId, // marker for web sessions
                            'ip_address' => $request->ip(),
                            'user_agent' => (string) ($request->userAgent() ?? ''),
                            'logged_in_at' => now(),
                            'last_activity_at' => now(),
                            'revoked_at' => null,
                        ]
                    );
                }
            } catch (\Throwable $e) {
                // Never break login if tracking fails
            }

            return redirect($this->redirectTo);
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('web')->user();
        if ($user) {
            $user->setRememberToken(null);
            $user->save();
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
    
    
    

    /**
     * Get the login username to be used by the controller.
     */
    public function username()
    {
        return 'email';
    }
}
