<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Sync button countdown timer
    let syncCountdownInterval = null;
    let syncStatusCheckInterval = null;

    function formatTimeRemaining(seconds) {
        if (seconds <= 0) return 'Sync Now';

        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;

        if (hours > 0) {
            return `Available in ${hours}h ${minutes}m`;
        } else if (minutes > 0) {
            return `Available in ${minutes}m ${secs}s`;
        } else {
            return `Available in ${secs}s`;
        }
    }

    function updateSyncButton() {
        const $button = $('#syncButton');
        if ($button.length === 0) return;

        const syncStatusUrl = $button.data('sync-status-url');

        $.ajax({
            url: syncStatusUrl,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                const $text = $('#syncButtonText');

                if (response.canSync && response.hasActiveProfiles) {
                    $button.prop('disabled', false);
                    $text.text('Sync Now');
                    $button.attr('title', 'Sync all connected platforms');

                    // Clear countdown interval if it exists
                    if (syncCountdownInterval) {
                        clearInterval(syncCountdownInterval);
                        syncCountdownInterval = null;
                    }
                } else if (!response.hasActiveProfiles) {
                    $button.prop('disabled', true);
                    $text.text('No Active Platforms');
                    $button.attr('title', 'Please connect at least one platform');
                } else if (response.remainingSeconds > 0) {
                    $button.prop('disabled', true);
                    startSyncCountdown(response.remainingSeconds);
                }
            }
        });
    }

    function startSyncCountdown(remainingSeconds) {
        const $button = $('#syncButton');
        const $text = $('#syncButtonText');

        // Clear any existing countdown
        if (syncCountdownInterval) {
            clearInterval(syncCountdownInterval);
        }

        let secondsLeft = remainingSeconds;

        function updateCountdown() {
            if (secondsLeft <= 0) {
                clearInterval(syncCountdownInterval);
                syncCountdownInterval = null;
                updateSyncButton(); // Re-check status
                return;
            }

            secondsLeft--;
            $button.prop('disabled', true);
            $text.text(formatTimeRemaining(secondsLeft));
            $button.attr('title', 'Sync cooldown in progress. Please wait.');
        }

        // Update immediately
        updateCountdown();

        // Then update every second
        syncCountdownInterval = setInterval(updateCountdown, 1000);
    }

    $(document).ready(function() {
        activateTabFromHash();
        // Initialize sync button
        const $syncButton = $('#syncButton');
        if ($syncButton.length > 0) {
            const remainingSeconds = parseInt($syncButton.data('remaining-seconds')) || 0;
            if (remainingSeconds > 0) {
                startSyncCountdown(remainingSeconds);
            } else {
                updateSyncButton();
            }

            // Check sync status periodically every 30 seconds
            syncStatusCheckInterval = setInterval(updateSyncButton, 30000);

            // Handle form submission
            $('#syncForm').on('submit', function(e) {
                e.preventDefault(); // Prevent page reload

                const $button = $syncButton;
                $button.prop('disabled', true);
                $button.find('.bi').addClass('spin');

                // Show progress animation
                const $text = $('#syncButtonText');
                $text.html('<i class="bi bi-hourglass-split me-2"></i>Syncing... 0%');

                // Simulate progress (since backend is async)
                let progress = 0;
                const progressInterval = setInterval(function() {
                    progress = Math.min(progress + Math.random() * 30, 90);
                    $text.html(
                        `<i class="bi bi-hourglass-split me-2"></i>Syncing... ${Math.floor(progress)}%`
                    );
                }, 500);

                // Submit form via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        clearInterval(progressInterval);
                        $text.html(
                            '<i class="bi bi-hourglass-split me-2"></i>Syncing... 100%'
                        );

                        // Wait a bit then update button status
                        setTimeout(function() {
                            $button.find('.bi').removeClass('spin');
                            // Start the cooldown timer
                            const cooldownMinutes = parseInt($button.data('cooldown-minutes')) || 120;
                            const cooldownSeconds = cooldownMinutes * 60;
                            startSyncCountdown(cooldownSeconds);
                        }, 1500);
                    },
                    error: function(xhr) {
                        clearInterval(progressInterval);
                        $button.find('.bi').removeClass('spin');
                        $text.text('Sync Failed');
                        $button.prop('disabled', false);

                        // Show error message
                        const errorMsg = xhr.responseJSON?.message || 'An error occurred during sync';
                        alert(errorMsg);

                        // Reset button after 3 seconds
                        setTimeout(function() {
                            updateSyncButton();
                        }, 3000);
                    }
                });
            });
        }


        $('.nav-link[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
            const hash = e.target.getAttribute('href');
            history.replaceState(null, '', hash);
        });

        $(window).on('hashchange', function() {
            activateTabFromHash();
        });

        $('.select2').select2();

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $('.select2-ajax').select2({
            placeholder: 'Search and select...',
            allowClear: true,
            ajax: {
                url: '{{ route('user.profile.edit', auth()->user()->username) }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        type: $(this).data('type'),
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        });

        $('#institute_country').select2({
            dropdownParent: $('#addInstituteModal'),
            placeholder: 'Search and select...',
            allowClear: true,
            ajax: {
                url: '{{ route('user.profile.edit', auth()->user()->username) }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        type: $(this).data('type'),
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        });

        $('#addInstituteForm').on('submit', function(e) {
            e.preventDefault();

            const formData = {
                name: $('#institute_name').val(),
                country_id: $('#institute_country').val(),
                website: $('#institute_website').val(),
                _token: $('input[name="_token"]').val()
            };

            $.ajax({
                url: '',
                method: 'POST',
                data: formData,
                success: function(response) {
                    // Add new option to institute select
                    const newOption = new Option(response.text, response.id, true, true);
                    $('#institute_id').append(newOption).trigger('change');

                    // Reset form and close modal
                    $('#addInstituteForm')[0].reset();
                    $('#addInstituteModal').modal('hide');

                    // Clear error messages
                    $('.invalid-feedback').html('').hide();
                    $('.form-control, .form-select').removeClass('is-invalid');
                },
                error: function(xhr) {
                    // Handle validation errors
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;

                        // Clear previous errors
                        $('.invalid-feedback').html('').hide();
                        $('.form-control, .form-select').removeClass('is-invalid');

                        // Display new errors
                        $.each(errors, function(field, messages) {
                            const errorEl = $('#' + field + '-error');
                            if (errorEl.length) {
                                errorEl.html(messages[0]).show();
                                $('[name="' + field + '"]').addClass('is-invalid');
                            }
                        });
                    }
                }
            });
        });

        $('#toggleCurrentPassword, #toggleNewPassword, #toggleConfirmPassword').on('click', function() {
            const input = $(this).closest('.input-group').find('input');
            const type = input.attr('type') === 'password' ? 'text' : 'password';
            input.attr('type', type);
            $(this).toggleClass('bi-eye-slash bi-eye');
        });

        @if (session('sub-section'))
            const collapseId = "{{ session('sub-section') }}";
            const collapseEl = document.getElementById(collapseId);

            if (collapseEl && typeof bootstrap !== 'undefined') {
                const collapse = new bootstrap.Collapse(collapseEl, {
                    toggle: true
                });
            }
        @endif
    });

    function previewProfileImage(event) {
        const file = event.target.files[0];
        if (!file) return;
        const allowedTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (PNG, JPG, JPEG, GIF)');
            event.target.value = '';
            return;
        }
        const maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File size must be less than 2MB. Your file is ' + (file.size / (1024 * 1024)).toFixed(
                2) + 'MB');
            event.target.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const avatarContainer = document.querySelector('.rounded-circle.overflow-hidden');
            if (avatarContainer) {
                avatarContainer.innerHTML = '';
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-100 h-100';
                img.style.objectFit = 'cover';
                avatarContainer.appendChild(img);
                avatarContainer.style.animation = 'fadeIn 0.3s ease-in';
            }
        };
        reader.readAsDataURL(file);
    }

    function updateCharacterCount(event) {
        const characterCountSpan = document.getElementById('fav-quote-character-count');
        if (characterCountSpan) {
            characterCountSpan.textContent = event.target.value.length;
        }
    }

    function activateTabFromHash() {
        const hash = window.location.hash;
        if (!hash) return;

        const $trigger = $(`.nav-link[data-bs-toggle="pill"][href="${hash}"]`);

        if ($trigger.length && typeof bootstrap !== 'undefined') {
            const tab = new bootstrap.Tab($trigger[0]);
            tab.show();
        }
    }

    function logoutSession(sessionId) {
        const sessionIdInput = document.getElementById('logout_session_id');
        const logoutMessage = document.getElementById('logoutMessage');
        const logoutPasswordInput = document.getElementById('logout_password');

        if (sessionIdInput && logoutMessage && logoutPasswordInput) {
            sessionIdInput.value = sessionId;
            logoutMessage.textContent = 'Please enter your password to logout from this session.';
            logoutPasswordInput.value = '';
            new bootstrap.Modal(document.getElementById('logoutSessionModal')).show();
        } else {
            console.error('Modal elements not found');
        }
    }

    function logoutAllSessions() {
        const sessionIdInput = document.getElementById('logout_session_id');
        const logoutMessage = document.getElementById('logoutMessage');
        const logoutPasswordInput = document.getElementById('logout_password');

        if (sessionIdInput && logoutMessage && logoutPasswordInput) {
            sessionIdInput.value = '';
            logoutMessage.textContent = 'Please enter your password to logout from all other sessions.';
            logoutPasswordInput.value = '';
            new bootstrap.Modal(document.getElementById('logoutSessionModal')).show();
        } else {
            console.error('Modal elements not found');
        }
    }

    // Clean up intervals on page unload
    $(window).on('beforeunload', function() {
        if (syncCountdownInterval) {
            clearInterval(syncCountdownInterval);
        }
        if (syncStatusCheckInterval) {
            clearInterval(syncStatusCheckInterval);
        }
    });
</script>
