<x-app-layout>
    <div style="padding: 3rem 0;">
        <div style="max-width: 80rem; margin: 0 auto; padding: 0 1.5rem;">

            @php
                $cardStyle = "padding: 2rem; background-color: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06); border-radius: 0.5rem; margin-bottom: 1.5rem;";
                $titleStyle = "font-size: 1.125rem; font-weight: 500; color: #111827;";
                $descStyle = "margin-top: 0.25rem; font-size: 0.875rem; color: #4b5563;";
                $formGroupStyle = "margin-bottom: 1.5rem;";
                $inputStyle = "margin-top: 0.25rem; display: block; width: 100%;";
                $selectStyle = "display: block; width: 100%; border-radius: 0.375rem; border: 1px solid #d1d5db; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 0.5rem;";
            @endphp

            {{-- Business Settings --}}
            <div style="{{ $cardStyle }}">
                <div style="max-width: 36rem;">
                    <h2 style="{{ $titleStyle }}">{{ __('Business Settings') }}</h2>
                    <p style="{{ $descStyle }}">{{ __("Update your business's name and currency.") }}</p>
                    <form method="post" action="{{ route('settings.business.update') }}" style="margin-top: 1.5rem;">
                        @csrf
                        <div style="{{ $formGroupStyle }}">
                            <x-input-label for="name" :value="__('Business Name')" />
                            <x-text-input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name', $business->name) }}"
                                style="
                                    margin-top: 0.25rem;
                                    display: block;
                                    width: 100%;
                                    padding: 0.5rem 0.75rem;
                                    border: 1px solid #d1d5db;
                                    border-radius: 0.375rem;
                                    background-color: #f9fafb;
                                    color: #111827;
                                    font-size: 0.875rem;
                                    transition: border-color 0.2s, box-shadow 0.2s;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 1px #3b82f6';"
                                onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';"
                            />
                            <x-input-error style="margin-top: 0.5rem;" :messages="$errors->get('name')" />
                        </div>
                        <div style="{{ $formGroupStyle }}">
                            <x-input-label for="currency" :value="__('Currency')" />
                            <x-text-input id="currency" name="currency" type="text"
                            style="
                                    margin-top: 0.25rem;
                                    display: block;
                                    width: 100%;
                                    padding: 0.5rem 0.75rem;
                                    border: 1px solid #d1d5db;
                                    border-radius: 0.375rem;
                                    background-color: #f9fafb;
                                    color: #111827;
                                    font-size: 0.875rem;
                                    transition: border-color 0.2s, box-shadow 0.2s;"
                                :value="old('currency', $business->currency)" required />
                            <x-input-error style="margin-top: 0.5rem;" :messages="$errors->get('currency')" />
                        </div>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <x-primary-button style="
                                    background-color: #3b82f6;
                                    color: #fff;
                                    padding: 0.5rem 1rem;
                                    border-radius: 0.375rem;
                                    font-weight: 500;
                                    transition: background-color 0.2s;"
                                onmouseover="this.style.backgroundColor='#2563eb';"
                                onmouseout="this.style.backgroundColor='#3b82f6';"
                            >{{ __('Save') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Team Members --}}
            <div style="{{ $cardStyle }}">
                <div style="max-width: 36rem;">
                    <h2 style="{{ $titleStyle }}">{{ __('Team Members') }}</h2>
                    <p style="{{ $descStyle }}">{{ __('Manage your team members and their roles.') }}</p>
                    <div style="margin-top: 1.5rem;">
                        @foreach ($members as $member)
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                                <div>
                                    <div style="font-weight: 500; color: #111827;">{{ $member->name }}</div>
                                    <div style="font-size: 0.875rem; color: #6b7280;">{{ $member->email }}</div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <form method="post" action="{{ route('settings.member.role', $member) }}">
                                        @csrf
                                        <select name="role" onchange="this.form.submit()" style="{{ $selectStyle }}">
                                            <option value="primary_admin" @if($member->pivot->role === 'primary_admin') selected @endif>{{ __('Primary Admin') }}</option>
                                            <option value="admin" @if($member->pivot->role === 'admin') selected @endif>{{ __('Admin') }}</option>
                                            <option value="employee" @if($member->pivot->role === 'employee') selected @endif>{{ __('Employee') }}</option>
                                        </select>
                                    </form>
                                    <form method="post" action="{{ route('settings.member.remove', $member) }}">
                                        @csrf
                                        @method('DELETE')
                                        <x-danger-button>{{ __('Remove') }}</x-danger-button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Invite New Member --}}
            <div style="{{ $cardStyle }}">
                <div style="max-width: 36rem;">
                    <h2 style="{{ $titleStyle }}">{{ __('Invite New Member') }}</h2>
                    <form method="post" action="{{ route('settings.invite') }}" style="margin-top: 1.5rem;">
                        @csrf
                        <div style="{{ $formGroupStyle }}">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email"
                                style="
                                    margin-top: 0.25rem;
                                    display: block;
                                    width: 100%;
                                    padding: 0.5rem 0.75rem;
                                    border: 1px solid #d1d5db;
                                    border-radius: 0.375rem;
                                    background-color: #f9fafb;
                                    color: #111827;
                                    font-size: 0.875rem;
                                    transition: border-color 0.2s, box-shadow 0.2s;"
                                :value="old('email')"
                                placeholder="{{ __('Enter email address') }}"
                             required />
                            <x-input-error style="margin-top: 0.5rem;" :messages="$errors->get('email')" />
                        </div>
                        <div style="{{ $formGroupStyle }}">
                            <x-input-label for="role" :value="__('Role')" />
                            <select name="role" id="role" style="{{ $selectStyle }}">
                                <option value="primary_admin">{{ __('Primary Admin') }}</option>
                                <option value="admin">{{ __('Admin') }}</option>
                                <option value="employee" selected>{{ __('Employee') }}</option>
                            </select>
                        </div>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <x-primary-button style="
                                    background-color: #3b82f6;
                                    color: #fff;
                                    padding: 0.5rem 1rem;
                                    border-radius: 0.375rem;
                                    font-weight: 500;
                                    transition: background-color 0.2s;"
                                onmouseover="this.style.backgroundColor='#2563eb';"
                                onmouseout="this.style.backgroundColor='#3b82f6';"
                            >{{ __('Invite') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Leave Business --}}
            <div style="{{ $cardStyle }}">
                <div style="max-width: 36rem;">
                    <h2 style="{{ $titleStyle }}">{{ __('Leave Business') }}</h2>
                    <p style="{{ $descStyle }}">{{ __('If you leave this business, you will lose access to all of its resources.') }}</p>
                    <form method="post" action="{{ route('settings.leave') }}" style="margin-top: 1.5rem;">
                        @csrf
                        @method('DELETE')
                        <x-danger-button>{{ __('Leave Business') }}</x-danger-button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
