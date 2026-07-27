<x-guest-layout>
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <div style="width: 48px; height: 48px; background: #eff6ff; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
            <svg width="24" height="24" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121 7.5z" />
            </svg>
        </div>
        <h2 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem;">Reset Your Password</h2>
        <p style="font-size: 0.84rem; color: #64748b; margin: 0;">Please enter your email and set a new secure password.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="alert alert-success" :status="session('status')" />

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="form-group">
            <label for="email" class="form-label">{{ __('Email Address') }}</label>
            <input id="email" class="form-input" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" />
            @if($errors->get('email'))
                <div class="form-error" style="color: #ef4444; font-size: 0.8125rem; margin-top: 4px;">{{ implode(', ', $errors->get('email')) }}</div>
            @endif
        </div>

        <!-- New Password -->
        <div class="form-group" x-data="{ showPassword: false }">
            <label for="password" class="form-label">{{ __('New Password') }}</label>
            <div style="position: relative;">
                <input id="password" class="form-input" :type="showPassword ? 'text' : 'password'" type="password" name="password" required autocomplete="new-password" style="padding-right: 2.5rem;" />
                <button type="button" @click="showPassword = !showPassword" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; cursor: pointer; color: #64748b; display: flex; align-items: center;" aria-label="Toggle password visibility">
                    <svg x-show="!showPassword" style="width: 1.2rem; height: 1.2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showPassword" x-cloak style="width: 1.2rem; height: 1.2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.046 10.046 0 013.122-.313c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21m-4.218-4.218L3 3" />
                    </svg>
                </button>
            </div>
            @if($errors->get('password'))
                <div class="form-error" style="color: #ef4444; font-size: 0.8125rem; margin-top: 4px;">{{ implode(', ', $errors->get('password')) }}</div>
            @endif
        </div>

        <!-- Confirm Password -->
        <div class="form-group" x-data="{ showConfirmPassword: false }">
            <label for="password_confirmation" class="form-label">{{ __('Confirm New Password') }}</label>
            <div style="position: relative;">
                <input id="password_confirmation" class="form-input" :type="showConfirmPassword ? 'text' : 'password'" type="password" name="password_confirmation" required autocomplete="new-password" style="padding-right: 2.5rem;" />
                <button type="button" @click="showConfirmPassword = !showConfirmPassword" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; cursor: pointer; color: #64748b; display: flex; align-items: center;" aria-label="Toggle password visibility">
                    <svg x-show="!showConfirmPassword" style="width: 1.2rem; height: 1.2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showConfirmPassword" x-cloak style="width: 1.2rem; height: 1.2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.046 10.046 0 013.122-.313c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21m-4.218-4.218L3 3" />
                    </svg>
                </button>
            </div>
            @if($errors->get('password_confirmation'))
                <div class="form-error" style="color: #ef4444; font-size: 0.8125rem; margin-top: 4px;">{{ implode(', ', $errors->get('password_confirmation')) }}</div>
            @endif
        </div>

        <div style="margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px; font-weight: 700;">
                {{ __('Reset Password') }}
            </button>
        </div>

        <div style="text-align: center; margin-top: 1rem;">
            <a href="{{ route('login') }}" style="font-size: 0.85rem; color: #3b82f6; text-decoration: none; font-weight: 500;">
                &larr; Back to Login
            </a>
        </div>
    </form>
</x-guest-layout>
