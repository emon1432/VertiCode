<div class="tab-pane fade" id="profile-security">
    <div class="card">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <h5 class="mb-0">
                <i class="bi bi-shield-lock"></i> Account Security
            </h5>
        </div>
        <div class="card-body">
            <div class="accordion" id="securityAccordion">
                @include('user.pages.profile.sections.edit.security-section.change-password')
                @include('user.pages.profile.sections.edit.security-section.two-factor-authentication')
                @include('user.pages.profile.sections.edit.security-section.active-sessions')
            </div>
        </div>
    </div>
</div>

<!-- Logout Session Modal -->
<div class="modal fade" id="logoutSessionModal" tabindex="-1" aria-labelledby="logoutSessionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form
                action="{{ route('user.profile.update', ['username' => auth()->user()->username, 'section' => 'profile-security']) }}"
                method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="sub-section" value="activeSessionsCollapse">
                <input type="hidden" name="session_id" id="logout_session_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="logoutSessionModalLabel">
                        <i class="bi bi-shield-lock"></i> Confirm Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" id="logoutMessage">Please enter your password to logout from this session.</p>
                    <div class="mb-3">
                        <label for="logout_password" class="form-label fw-600">Password <span
                                class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="logout_password" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
