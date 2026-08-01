<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="form-group">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input id="name" class="form-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
            @if($errors->get('name'))
                <div class="form-error">{{ implode(', ', $errors->get('name')) }}</div>
            @endif
        </div>

        <!-- Email Address -->
        <div class="form-group">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
            @if($errors->get('email'))
                <div class="form-error">{{ implode(', ', $errors->get('email')) }}</div>
            @endif
        </div>

        <!-- Password -->
        <div class="form-group" x-data="{ showPassword: false }">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <div style="position: relative;">
                <input id="password" class="form-input" :type="showPassword ? 'text' : 'password'" type="password" name="password" required autocomplete="new-password" style="padding-right: 2.5rem;" />
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

        <!-- Confirm Password -->
        <div class="form-group" x-data="{ showConfirmPassword: false }">
            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
            <div style="position: relative;">
                <input id="password_confirmation" class="form-input" :type="showConfirmPassword ? 'text' : 'password'" type="password" name="password_confirmation" required autocomplete="new-password" style="padding-right: 2.5rem;" />
                <button type="button" @click="showConfirmPassword = !showConfirmPassword" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; cursor: pointer; color: var(--gray-500); display: flex; align-items: center;" aria-label="Toggle confirm password visibility">
                    <svg x-show="!showConfirmPassword" style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showConfirmPassword" x-cloak style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.046 10.046 0 013.122-.313c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21m-4.218-4.218L3 3" />
                    </svg>
                </button>
            </div>
            @if($errors->get('password_confirmation'))
                <div class="form-error">{{ implode(', ', $errors->get('password_confirmation')) }}</div>
            @endif
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('login') }}" style="font-size: 0.875rem; color: var(--primary-color); text-decoration: none;">
                {{ __('Already registered?') }}
            </a>

            <button type="submit" class="btn btn-primary">
                {{ __('Register') }}
            </button>
        </div>
    </form>
</x-guest-layout>
