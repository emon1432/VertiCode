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
