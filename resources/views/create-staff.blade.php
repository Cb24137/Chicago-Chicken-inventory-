@extends('layouts.app')

@section('title', 'Add Staff')

@section('content')
    <x-page-header
        eyebrow="Administration · Admin only"
        title="Add New Staff"
        description="Create a new employee account. New accounts have staff access."
        :back="route('admin.staff.index')"
        back-label="Back to staff" />

    <form method="POST" action="{{ route('admin.staff.store') }}" class="ccc-card ccc-form-card">
        @csrf

        <div class="ccc-form-section">
            <h2 class="ccc-form-section-title">Account details</h2>
            <p class="ccc-form-section-text">The staff member signs in with this email address and password.</p>

            <div class="ccc-form-grid">
                <div class="ccc-field">
                    <label for="name" class="ccc-label">Full name <span class="ccc-required">*</span></label>
                    <input id="name" type="text" name="name" required autocomplete="name"
                           value="{{ old('name') }}"
                           placeholder="Enter staff name"
                           class="ccc-input @error('name') is-invalid @enderror">
                    <x-field-error name="name" />
                </div>

                <div class="ccc-field">
                    <label for="email" class="ccc-label">Email address <span class="ccc-required">*</span></label>
                    <input id="email" type="email" name="email" required autocomplete="email"
                           value="{{ old('email') }}"
                           placeholder="Enter staff email"
                           class="ccc-input @error('email') is-invalid @enderror">
                    <x-field-error name="email" />
                </div>

                <div class="ccc-field">
                    <label for="password" class="ccc-label">Password <span class="ccc-required">*</span></label>
                    <input id="password" type="password" name="password" required minlength="5"
                           autocomplete="new-password"
                           placeholder="Minimum 5 characters"
                           class="ccc-input @error('password') is-invalid @enderror">
                    <x-field-error name="password" />
                </div>

                <div class="ccc-field">
                    <label for="password_confirmation" class="ccc-label">Confirm password <span class="ccc-required">*</span></label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required minlength="5"
                           autocomplete="new-password"
                           placeholder="Confirm password"
                           class="ccc-input">
                </div>
            </div>
        </div>

        <div class="ccc-form-actions">
            <button type="submit" class="ccc-btn ccc-btn-primary">
                <x-icon name="check" /> Create Staff Account
            </button>
            <a href="{{ route('admin.staff.index') }}" class="ccc-btn ccc-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
