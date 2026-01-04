@extends('admin.layouts.app')
@section('title', __('Settings'))
@section('content')
    <div class="col-12">
        <div class="bs-stepper vertical wizard-modern wizard-modern-vertical-icons-example mt-2">
            <div class="bs-stepper-header">
                @foreach ($settings as $setting)
                    <div class="step {{ $loop->first ? 'active' : '' }}" data-target="#{{ $setting->key }}">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">
                                <i class="icon-base ti tabler-{{ $setting->icon }} icon-md"></i>
                            </span>
                            <span class="bs-stepper-label">
                                <span
                                    class="bs-stepper-title">{{ __(ucwords(str_replace('_', ' ', $setting->key))) }}</span>
                            </span>
                        </button>
                    </div>
                @endforeach
            </div>
            <div class="bs-stepper-content">
                @foreach ($settings as $setting)
                    <div id="{{ $setting->key }}" class="content {{ $loop->first ? 'active' : '' }}">
                        @include('admin.pages.settings.sections.' . $setting->key, ['setting' => $setting])
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            var stepper = new Stepper(document.querySelector('.wizard-modern-vertical-icons-example'), {
                linear: false
            });
            $('.bs-stepper-header .step').on('click', function() {
                var crossedElements = $('.bs-stepper-header .step.crossed');
                crossedElements.removeClass('crossed');
            });
        });
    </script>
@endpush
