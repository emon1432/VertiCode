<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
    data-bs-target="#permissionsViewModal{{ $role->id }}">
    {{ __('View') }}
</button>
<div class="modal fade" id="permissionsViewModal{{ $role->id }}" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Permissions for') }} {{ $role->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                @if ($role->id == 1)
                    <p class="text-muted text-center text-primary">
                        {{ __('This is a default role with all permissions granted.') }}
                    </p>
                @elseif (!empty($role->permission))
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Module') }}</th>
                                    <th>
                                        {{ __('Permissions') }}
                                        <i class="icon-base ti tabler-info-circle icon-xs" data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="{{ __('Success indicates granted permissions, while danger indicates denied permissions') }}"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($role->permission as $module => $permissions)
                                    <tr>
                                        <td>{{ ucfirst($module) }}</td>
                                        <td>
                                            @if (is_array($permissions))
                                                @foreach ($permissions as $permission => $value)
                                                    @if ($value)
                                                        <span
                                                            class="badge bg-success">{{ ucfirst($permission) }}</span>
                                                    @else
                                                        <span class="badge bg-danger">{{ ucfirst($permission) }}</span>
                                                    @endif
                                                @endforeach
                                            @else
                                                <span class="badge bg-danger">{{ __('No permissions defined') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center text-danger">
                        {{ __('No permissions defined for this role.') }}
                    </p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-danger"
                    data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
