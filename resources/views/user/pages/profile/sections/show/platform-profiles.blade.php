@if ($user->platformProfiles->count() > 0)
    <div class="mb-5">
        <h4 class="mb-4">
            <i class="bi bi-trophy"></i> Connected Platforms
        </h4>
        <div class="card overflow-hidden">
            <div class="table-responsive">
                <table class="table mb-0 platform-table">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <th class="fw-600 text-white ps-4"><i class="bi bi-trophy me-2"></i>Platform</th>
                            <th class="fw-600 text-white"><i class="bi bi-person me-2"></i>Handle</th>
                            <th class="fw-600 text-white"><i class="bi bi-star-fill me-2"></i>Rating</th>
                            <th class="fw-600 text-white"><i class="bi bi-check2-circle me-2"></i>Problems Solved
                            </th>
                            <th class="fw-600 text-white text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user->platformProfiles as $profile)
                            <tr class="platform-row"
                                style="border-bottom: 1px solid #f0f0f0; transition: all 0.3s ease;">
                                <td class="ps-4 py-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="platform-icon-bg"
                                            style="width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); font-size: 1.5rem;">
                                            {{ $profile->platform->icon ?? '🏆' }}
                                        </div>
                                        <div>
                                            <p class="mb-0 fw-600">{{ $profile->platform->display_name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <span class="badge bg-light text-dark"
                                        style="font-size: 0.85rem; padding: 8px 12px; border-radius: 8px;">
                                        {{ $profile->handle }}
                                    </span>
                                </td>
                                <td class="py-4">
                                    @if ($profile->rating)
                                        <div>
                                            <span class="badge bg-light text-dark"
                                                style="font-size: 0.85rem; padding: 8px 12px; border-radius: 8px;">
                                                {{ $profile->rating }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted d-flex align-items-center gap-1">
                                            <i class="bi bi-dash-circle"></i> N/A
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4">
                                    @if ($profile->total_solved)
                                        <div>
                                            <span class="badge bg-light text-dark"
                                                style="font-size: 0.85rem; padding: 8px 12px; border-radius: 8px;">
                                                {{ $profile->total_solved }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted d-flex align-items-center gap-1">
                                            <i class="bi bi-dash-circle"></i> No data
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 text-center">
                                    <a href="{{ $profile->profile_url }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px; transition: all 0.3s ease;"
                                        title="View Profile">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    <div class="card text-center py-5 mb-5">
        <div class="card-body">
            <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e0;"></i>
            <p class="text-muted mt-3">No connected platforms yet</p>
        </div>
    </div>
@endif
