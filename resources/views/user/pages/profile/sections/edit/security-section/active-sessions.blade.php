<div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#activeSessionsCollapse" aria-expanded="false" aria-controls="activeSessionsCollapse">
            <i class="bi bi-diagram-3 me-2"></i> Active Sessions
        </button>
    </h2>
    <div id="activeSessionsCollapse" class="accordion-collapse collapse" data-bs-parent="#securityAccordion">
        <div class="accordion-body">
            <p class="text-muted mb-3">Manage your active sessions and logout from other devices</p>

            @if (isset($sessions) && $sessions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>Browser</th>
                                <th>IP Address</th>
                                <th>Last Active</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sessions as $session)
                                <tr>
                                    <td>
                                        <i class="bi {{ $session->device['icon'] }}"></i>
                                        {{ $session->device['name'] }}
                                        @if ($session->is_current_device)
                                            <span class="badge bg-success ms-2">Current</span>
                                        @endif
                                    </td>
                                    <td>{{ $session->browser }}</td>
                                    <td>{{ $session->ip_address }}</td>
                                    <td>{{ $session->last_active->diffForHumans() }}</td>
                                    <td>
                                        @if (!$session->is_current_device)
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="logoutSession('{{ $session->id }}')">
                                                <i class="bi bi-box-arrow-right"></i> Logout
                                            </button>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($sessions->where('is_current_device', false)->count() > 0)
                    <div class="mt-3">
                        <button type="button" class="btn btn-danger" onclick="logoutAllSessions()">
                            <i class="bi bi-box-arrow-right"></i> Logout All Other Sessions
                        </button>
                    </div>
                @endif
            @else
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle me-2"></i> No active sessions found. This may indicate that database
                    session storage is not configured.
                </div>
            @endif
        </div>
    </div>
</div>
