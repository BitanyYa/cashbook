<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        // Automatically create a Business for the user and assign as Primary Admin (Owner)
        $business = \App\Models\Business::create([
            'name' => $request->business_name ?: ($request->name . "'s Business"),
            'currency' => 'USD',
        ]);

        $business->users()->attach($user->id, ['role' => 'primary_admin']);

        // Automatically create a default Cashbook for the business
        $book = \App\Models\Book::create([
            'name' => 'Main Cashbook',
            'description' => 'Default Cashbook',
            'business_id' => $business->id,
            'currency' => 'USD',
        ]);

        $book->users()->attach($user->id, ['role' => 'primary_admin']);

        session(['active_business_id' => $business->id]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
