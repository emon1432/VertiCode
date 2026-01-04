<div>
    @foreach ($tags as $tag)
        <span class="badge bg-label-{{ $color }}">{{ trim($tag) }}</span>
    @endforeach
</div>
