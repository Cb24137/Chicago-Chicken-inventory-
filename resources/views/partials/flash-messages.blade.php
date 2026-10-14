{{-- Success, error and validation messages for every page --}}
@if (session('success'))
    <div class="ccc-alert is-success" role="status">
        <x-icon name="check-circle" />
        <div class="ccc-alert-content">{{ session('success') }}</div>
        <button type="button" class="ccc-alert-close" data-dismiss-alert aria-label="Dismiss message">
            <x-icon name="close" />
        </button>
    </div>
@endif

@if (session('error'))
    <div class="ccc-alert is-error" role="alert">
        <x-icon name="alert" />
        <div class="ccc-alert-content">{{ session('error') }}</div>
        <button type="button" class="ccc-alert-close" data-dismiss-alert aria-label="Dismiss message">
            <x-icon name="close" />
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="ccc-alert is-error" role="alert">
        <x-icon name="alert" />
        <div class="ccc-alert-content">
            @if ($errors->count() === 1)
                {{ $errors->first() }}
            @else
                <strong>Please fix the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif
