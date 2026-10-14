@extends('layouts.app')

@section('title', 'Edit Staff')

@section('content')
    <x-page-header
        eyebrow="Administration · Admin only"
        title="Edit Staff"
        description="Update employee account information."
        :back="route('admin.staff.index')"
        back-label="Back to staff" />

    <form method="POST" action="{{ route('admin.staff.update', $staff) }}" class="ccc-card ccc-form-card">
        @csrf
        @method('PUT')

        <div class="ccc-form-section">
            <h2 class="ccc-form-section-title">Account details</h2>
            <p class="ccc-form-section-text">Leave the password fields blank to keep the current password.</p>

            <div class="ccc-form-grid">
                <div class="ccc-field">
                    <label for="name" class="ccc-label">Full name <span class="ccc-required">*</span></label>
                    <input id="name" type="text" name="name" required
                           value="{{ old('name', $staff->name) }}"
                           class="ccc-input @error('name') is-invalid @enderror">
                    <x-field-error name="name" />
                </div>

                <div class="ccc-field">
                    <label for="email" class="ccc-label">Email address <span class="ccc-required">*</span></label>
                    <input id="email" type="email" name="email" required
                           value="{{ old('email', $staff->email) }}"
                           class="ccc-input @error('email') is-invalid @enderror">
                    <x-field-error name="email" />
                </div>

                <div class="ccc-field">
                    <label for="password" class="ccc-label">New password <span class="ccc-optional">(optional)</span></label>
                    <input id="password" type="password" name="password" minlength="5"
                           autocomplete="new-password"
                           placeholder="Leave blank to keep current password"
                           class="ccc-input @error('password') is-invalid @enderror">
                    <x-field-error name="password" />
                </div>

                <div class="ccc-field">
                    <label for="password_confirmation" class="ccc-label">Confirm new password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" minlength="5"
                           autocomplete="new-password"
                           placeholder="Confirm new password"
                           class="ccc-input">
                </div>
            </div>
        </div>

        <div class="ccc-form-actions">
            <button type="submit" class="ccc-btn ccc-btn-primary">
                <x-icon name="check" /> Save Changes
            </button>
            <a href="{{ route('admin.staff.index') }}" class="ccc-btn ccc-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
