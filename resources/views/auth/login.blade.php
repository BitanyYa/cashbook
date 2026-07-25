<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="alert alert-success" :status="session('status')" />

    @php
        $isDemo = request()->query('demo') == 1;
    @endphp

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" class="form-input" type="email" name="email" value="{{ $isDemo ? 'demo@cashbook.test' : old('email') }}" required autofocus autocomplete="username" />
            @if($errors->get('email'))
                <div class="form-error">{{ implode(', ', $errors->get('email')) }}</div>
            @endif
        </div>

        <!-- Password -->
        <div class="form-group" x-data="{ showPassword: false }">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <div style="position: relative;">
                <input id="password" class="form-input" :type="showPassword ? 'text' : 'password'" type="password" name="password" value="{{ $isDemo ? 'Password' : '' }}" required autocomplete="current-password" style="padding-right: 2.5rem;" />
                <button type="button" @click="showPassword = !showPassword" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; cursor: pointer; color: var(--gray-500); display: flex; align-items: center;" aria-label="Toggle password visibility">
                    <svg x-show="!showPassword" style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showPassword" x-cloak style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.046 10.046 0 013.122-.313c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21m-4.218-4.218L3 3" />
                    </svg>
                </button>
            </div>
            @if($errors->get('password'))
                <div class="form-error">{{ implode(', ', $errors->get('password')) }}</div>
            @endif
        </div>

        <!-- Remember Me -->
        <div class="form-group">
            <label for="remember_me" style="display: flex; align-items: center;">
                <input id="remember_me" type="checkbox" name="remember" style="margin-right: 8px;">
                <span class="form-label" style="margin-bottom: 0;">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="font-size: 0.875rem; color: var(--primary-color); text-decoration: none;">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <button type="submit" class="btn btn-primary">
                {{ __('Log in') }}
            </button>
        </div>

        <div class="text-center mt-4">
            <span style="font-size: 0.875rem; color: var(--gray-600);">Don't have an account?</span>
            <a href="{{ route('register') }}" style="font-size: 0.875rem; color: var(--primary-color); text-decoration: none; margin-left: 0.5rem;">Sign up</a>
        </div>
    </form>
</x-guest-layout>
