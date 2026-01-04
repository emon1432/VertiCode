<ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
    <li class="avatar pull-up">
        @if ($user)
            <img class="rounded-circle" src="{{ imageShow($user->image) }}" alt="{{ $user->name }}"
                data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $user->name }}"
                style="width: 40px; height: 40px; object-fit: cover;" />
        @else
            <span
                class="avatar-initial rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('No user') }}"
                style="width: 40px; height: 40px;">
                {{ __('N/A') }}
            </span>
        @endif
    </li>
</ul>
