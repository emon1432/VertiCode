<footer class="content-footer footer bg-footer-theme">
    <div class="container-xxl">
        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">

            <div class="text-body">
                {{ settings('system_settings', 'footer_text', '© ' . date('Y') . ' Your Company. All rights reserved.') }}
            </div>

            <div class="text-body">
                {{ settings('system_settings', 'copyright', 'Copyright © ' . date('Y') . ' Your Company') }}

                &nbsp; | &nbsp;

                {{ __('Design and Developed by') }}
                <i class="icon-base ti tabler-heart text-danger"></i>
                <a href="https://emonideas.com" target="_blank" class="text-body text-decoration-none">Emon Ideas</a>
            </div>

        </div>
    </div>
</footer>
