<div class="d-flex justify-content-start align-items-center item-info">
    <div class="avatar-wrapper">
        <div class="avatar avatar me-2 me-sm-4 rounded-2 bg-label-secondary">
            @if (!empty($image) && file_exists(public_path($image)))
                <img src="{{ $image }}" alt="{{ $name }}" class="rounded">
            @elseif (!empty($initials))
                @php
                    $colors = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
                    $color = $colors[array_rand($colors)];
                @endphp
                <span class="avatar-initial rounded-2 bg-label-{{ $color }}">
                    {{ $initials }}
                </span>
            @endif
        </div>
    </div>
    <div class="d-flex flex-column">
        <h6 class="text-nowrap mb-0">{{ $name }}</h6>
        @if (!empty($code) && !empty($barcode))
            <small class="text-truncate d-none d-sm-block">{{ $code }} ({{ $barcode }})</small>
        @elseif(!empty($code))
            <small class="text-truncate d-none d-sm-block">{{ $code }}</small>
        @elseif(!empty($barcode))
            <small class="text-truncate d-none d-sm-block">{{ $barcode }}</small>
        @endif
    </div>
</div>
