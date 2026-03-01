<h4 class="mb-4">
    <i class="bi bi-funnel-fill"></i> Filter &amp; Sort
</h4>
<div class="card leaderboard-filter-card shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('leaderboard') }}" class="row g-3">
            <div class="col-lg-6 col-md-6">
                <label for="search" class="form-label fw-semibold">Search</label>
                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}"
                    placeholder="Name or username">
            </div>

            <div class="col-lg-6 col-md-6">
                <label for="institute_id" class="form-label fw-semibold">Institute</label>
                <select name="institute_id" id="institute_id" class="form-select js-filter-select2-ajax"
                    data-type="institute" data-placeholder="All institutes">
                    <option value="">All institutes</option>
                    @if ($selectedInstitute)
                        <option value="{{ $selectedInstitute->id }}" selected>{{ $selectedInstitute->name }}</option>
                    @endif
                </select>
            </div>

            <div class="col-lg-4 col-md-6">
                <label for="country_id" class="form-label fw-semibold">Country</label>
                <select name="country_id" id="country_id" class="form-select js-filter-select2-ajax"
                    data-type="country" data-placeholder="All countries">
                    <option value="">All countries</option>
                    @if ($selectedCountry)
                        <option value="{{ $selectedCountry->id }}" selected>
                            {{ trim(($selectedCountry->flag ? $selectedCountry->flag . ' ' : '') . $selectedCountry->name) }}
                        </option>
                    @endif
                </select>
            </div>

            <div class="col-lg-4 col-md-6">
                <label for="platform_id" class="form-label fw-semibold">Platform</label>
                <select name="platform_id" id="platform_id" class="form-select js-filter-select2"
                    data-placeholder="All platforms">
                    <option value="">All platforms</option>
                    @foreach ($platforms as $platform)
                        <option value="{{ $platform->id }}" @selected((string) request('platform_id') === (string) $platform->id)>
                            {{ $platform->display_name ?: ucfirst($platform->name) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-4 col-md-6">
                <label for="sort" class="form-label fw-semibold">Sort by</label>
                <select name="sort" id="sort" class="form-select js-filter-select2" data-placeholder="Default sorting">
                    <option value="rating_desc" @selected($sort === 'rating_desc')>Rating high to low</option>
                    <option value="rating_asc" @selected($sort === 'rating_asc')>Rating low to high</option>
                    <option value="solved_desc" @selected($sort === 'solved_desc')>Solved count high to low</option>
                    <option value="solved_asc" @selected($sort === 'solved_asc')>Solved count low to high</option>
                </select>
            </div>

            <div class="col-12 d-flex gap-2 pt-2">
                <button type="submit" class="btn btn-primary-gradient">
                    <i class="bi bi-funnel-fill"></i> Apply filters
                </button>
                <a href="{{ route('leaderboard') }}" class="btn btn-danger-gradient">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>
<hr>
