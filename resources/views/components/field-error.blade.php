@props(['name'])

@error($name)
    <p class="ccc-field-error" id="{{ $name }}-error">{{ $message }}</p>
@enderror
