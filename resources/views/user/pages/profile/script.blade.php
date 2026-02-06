<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        activateTabFromHash();

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
            alert('File size must be less than 2MB. Your file is ' + (file.size / (1024 * 1024)).toFixed(2) + 'MB');
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
</script>
