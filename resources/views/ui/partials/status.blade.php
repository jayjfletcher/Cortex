@if (session('status'))
    <x-atrium::alert variant="success" class="mb-4" dismissible>{{ session('status') }}</x-atrium::alert>
@endif

@foreach (['prompt', 'agent'] as $key)
    @if ($errors->has($key))
        <x-atrium::alert variant="danger" class="mb-4">{{ $errors->first($key) }}</x-atrium::alert>
    @endif
@endforeach
