<ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
    @if ($users->isEmpty())
        <li class="avatar pull-up">
            <span class="avatar-initial rounded-circle pull-up" data-bs-toggle="tooltip" data-bs-placement="bottom"
                title="{{ __('No users found') }}">{{ __('N/A') }}</span>
        </li>
    @else
        @foreach ($users as $user)
            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="{{ $user->name }}"
                class="avatar pull-up">
                <img class="rounded-circle" src="{{ imageShow($user->image) }}" alt="{{ $user->name }}" />
            </li>
        @endforeach
        @if ($moreCount > 0)
            <li class="avatar">
                <span class="avatar-initial rounded-circle pull-up" data-bs-toggle="tooltip" data-bs-placement="bottom"
                    title="{{ $moreCount }} {{ __('more') }}">+{{ $moreCount }}</span>
            </li>
        @endif
    @endif
</ul>
