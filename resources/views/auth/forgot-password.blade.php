<x-guest-layout>
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <div style="width: 48px; height: 48px; background: #eff6ff; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
            <svg width="24" height="24" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0017.25 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
            </svg>
        </div>
        <h2 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem;">Forgot Password?</h2>
        <p style="font-size: 0.84rem; color: #64748b; margin: 0;">Enter your account email to receive a password reset link.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="alert alert-success" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <label for="email" class="form-label">{{ __('Email Address') }}</label>
            <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@example.com" />
            @if($errors->get('email'))
                <div class="form-error" style="color: #ef4444; font-size: 0.8125rem; margin-top: 4px;">{{ implode(', ', $errors->get('email')) }}</div>
            @endif
        </div>

        <div style="margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px; font-weight: 700;">
                {{ __('Send Password Reset Link') }}
            </button>
        </div>

        <div style="text-align: center; margin-top: 1rem;">
            <a href="{{ route('login') }}" style="font-size: 0.85rem; color: #3b82f6; text-decoration: none; font-weight: 500;">
                &larr; Back to Login
            </a>
        </div>
    </form>
</x-guest-layout>
