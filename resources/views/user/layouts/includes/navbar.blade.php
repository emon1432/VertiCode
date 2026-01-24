<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('user.dashboard') }}">VertiCode</a>

        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('user.dashboard') }}">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('user.platform-profiles.index') }}">Platform Profiles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                        href="{{ route('user.profile', ['username' => auth()->user()->username]) }}">Account</a>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-light ms-2">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
