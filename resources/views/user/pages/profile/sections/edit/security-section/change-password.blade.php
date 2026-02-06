<div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#changePasswordCollapse" aria-expanded="false" aria-controls="changePasswordCollapse">
            <i class="bi bi-key me-2"></i> Change Password
        </button>
    </h2>
    <div id="changePasswordCollapse" class="accordion-collapse collapse" data-bs-parent="#securityAccordion">
        <div class="accordion-body">
            <p class="text-muted mb-3">Update your password regularly to keep your account secure</p>
            <form
                action="{{ route('user.profile.update', ['username' => auth()->user()->username, 'section' => 'profile-security', 'sub-section' => 'changePasswordCollapse']) }}"
                method="POST">
                @csrf
                @method('PUT')
                <!-- Current Password -->
                <div class="mb-3">
                    <label for="current_password" class="form-label fw-600">Current
                        Password<span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="current_password" name="current_password"
                            placeholder="Enter your current password" required minlength="8">
                        <span class="input-group-text">
                            <i class="bi bi-eye-slash" id="toggleCurrentPassword" style="cursor: pointer;"></i>
                        </span>
                    </div>
                </div>

                <!-- New Password -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-600">New Password<span
                            class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Enter your new password" required minlength="8">
                        <span class="input-group-text">
                            <i class="bi bi-eye-slash" id="toggleNewPassword" style="cursor: pointer;"></i>
                        </span>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label fw-600">Confirm
                        Password<span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                            placeholder="Confirm your new password" required minlength="8">
                        <span class="input-group-text">
                            <i class="bi bi-eye-slash" id="toggleConfirmPassword" style="cursor: pointer;"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Update Password
                </button>
            </form>
        </div>
    </div>
</div>
