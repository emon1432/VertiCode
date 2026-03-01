<h4 class="mb-4">
    <i class="bi bi-list-stars"></i> Full Leaderboard
</h4>
<div class="card leaderboard-table-card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table leaderboard-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Rank</th>
                        <th>User</th>
                        <th>Institute</th>
                        <th class="text-end">Total Rating</th>
                        <th class="text-end">Total Solved</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="ps-4">
                                <span
                                    class="badge bg-primary leaderboard-rank-badge">#{{ $user->leaderboard_rank }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <a href="{{ route('user.profile.show', $user->username) }}"
                                        class="d-flex align-items-center gap-3 text-decoration-none text-dark">
                                        <img src="{{ $user->image && imageExists($user->image) ? imageShow($user->image) : $user->profile_photo_url }}"
                                            alt="{{ $user->name }}" class="rounded-circle" width="42"
                                            height="42">
                                    </a>
                                    <div>
                                        <div class="fw-semibold">
                                            <a href="{{ route('user.profile.show', $user->username) }}"
                                                class=" text-decoration-none text-dark">
                                                {{ $user->name }}
                                            </a>
                                            @if ($user->country)
                                                <span
                                                    title="{{ $user->country->name . ' (' . $user->country->code . ')' }}">
                                                    {{ $user->country->flag }}</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('user.profile.show', $user->username) }}"
                                            class="small text-muted text-decoration-none">
                                            &#64;{{ $user->username }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($user->institute)
                                        <span class="">{{ $user->institute->name }}</span>
                                    @else
                                        <span class="small text-muted">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="leaderboard-score">{{ number_format((int) $user->total_rating) }}</span>
                            </td>
                            <td class="text-end">
                                <span class="leaderboard-score">{{ number_format((int) $user->total_solved) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No users matched your
                                filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($users->hasPages())
        <div class="card-footer leaderboard-pagination-wrap border-0 py-3 px-4">
            <div class="leaderboard-pagination-meta mb-3 d-flex flex-wrap gap-2 justify-content-between">
                <span>
                    Showing {{ number_format($users->firstItem() ?? 0) }}-{{ number_format($users->lastItem() ?? 0) }}
                    of {{ number_format($users->total()) }} users
                </span>
                <span>
                    Total pages: <strong>{{ number_format($users->lastPage()) }}</strong>
                </span>
            </div>
            <nav aria-label="Leaderboard pagination" class="d-flex justify-content-center">
                <ul class="pagination mb-0">
                    @php
                        $currentPage = $users->currentPage();
                        $lastPage = $users->lastPage();
                        $windowStart = max(2, $currentPage - 2);
                        $windowEnd = min($lastPage - 1, $currentPage + 2);

                        if ($currentPage <= 4) {
                            $windowStart = 2;
                            $windowEnd = min($lastPage - 1, 6);
                        }

                        if ($currentPage >= $lastPage - 3) {
                            $windowStart = max(2, $lastPage - 5);
                            $windowEnd = $lastPage - 1;
                        }
                    @endphp

                    <li class="page-item @if ($users->onFirstPage()) disabled @endif">
                        <a class="page-link" href="{{ $users->url(1) }}" aria-label="First">First</a>
                    </li>

                    <li class="page-item @if ($users->onFirstPage()) disabled @endif">
                        <a class="page-link" href="{{ $users->previousPageUrl() ?: '#' }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <li class="page-item @if ($currentPage === 1) active @endif">
                        <a class="page-link" href="{{ $users->url(1) }}">1</a>
                    </li>

                    @if ($windowStart > 2)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif

                    @for ($page = $windowStart; $page <= $windowEnd; $page++)
                        <li class="page-item @if ($page === $currentPage) active @endif">
                            <a class="page-link" href="{{ $users->url($page) }}">{{ $page }}</a>
                        </li>
                    @endfor

                    @if ($windowEnd < $lastPage - 1)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif

                    @if ($lastPage > 1)
                        <li class="page-item @if ($currentPage === $lastPage) active @endif">
                            <a class="page-link" href="{{ $users->url($lastPage) }}">{{ $lastPage }}</a>
                        </li>
                    @endif

                    <li class="page-item @if (!$users->hasMorePages()) disabled @endif">
                        <a class="page-link" href="{{ $users->nextPageUrl() ?: '#' }}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>

                    <li class="page-item @if (!$users->hasMorePages()) disabled @endif">
                        <a class="page-link" href="{{ $users->url($lastPage) }}" aria-label="Last">Last</a>
                    </li>
                </ul>
            </nav>
        </div>
    @endif
</div>
