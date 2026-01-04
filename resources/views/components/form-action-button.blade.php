@if ($type === 'page')
    @if ($action === 'create')
        <button type="submit" class="btn btn-primary me-2">
            <i class="icon-base ti tabler-device-floppy icon-xs me-2"></i>
            {{ __('Save') }}
        </button>
    @elseif($action === 'edit')
        <button type="submit" class="btn btn-primary me-2">
            <i class="icon-base ti tabler-device-floppy icon-xs me-2"></i>
            {{ __('Update') }}
        </button>
    @endif
    <button type="button" class="btn btn-secondary me-2"
        onclick="window.location.href='{{ route($resource . '.index') }}'">
        <i class="icon-base ti tabler-x icon-xs me-2"></i>
        {{ __('Cancel') }}
    </button>
    <button type="button" class="btn btn-danger" onclick="window.location.reload();">
        <i class="icon-base ti tabler-refresh icon-xs me-2"></i>
        {{ __('Reset') }}
    </button>
@elseif($type === 'modal')
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="icon-base ti tabler-x icon-xs me-2"></i>
        {{ __('Close') }}
    </button>
    @if ($action === 'create')
        <button type="submit" class="btn btn-primary">
            <i class="icon-base ti tabler-device-floppy icon-xs me-2"></i>
            {{ __('Save') }}
        </button>
    @elseif($action === 'edit')
        <button type="submit" class="btn btn-primary">
            <i class="icon-base ti tabler-device-floppy icon-xs me-2"></i>
            {{ __('Update') }}
        </button>
    @endif
@endif
