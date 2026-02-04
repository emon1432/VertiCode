@extends('admin.layouts.app')
@section('title', __('Edit Platform'))
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ __('Edit Platform') }}</h5>
                    <a class="btn add-new btn-primary" href="{{ route('platforms.index') }}">
                        <span class="d-flex align-items-center gap-2 text-white">
                            <i class="icon-base ti tabler-arrow-back-up icon-xs"></i>
                            {{ __('Back to Platform List') }}
                        </span>
                    </a>
                </div>
                <div class="card-body">
                    <form class="row g-6 common-form" action="{{ route('platforms.update', $platform->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="col-12">
                            <h6>{{ __('Platform Information') }}</h6>
                            <hr class="mt-0" />
                        </div>
                        <div class="col-md-12 form-control-validation">
                            <label class="form-label" for="display_name">{{ __('Display Name') }}<span
                                    class="text-danger">*</span></label>
                            <input type="text" name="display_name" id="display_name" class="form-control"
                                placeholder="{{ __('Enter display name') }}"
                                value="{{ old('display_name', $platform->display_name) }}" required />
                        </div>
                        <div class="col-md-6 form-control-validation">
                            <label class="form-label" for="name">{{ __('Short Name') }}<span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="{{ __('Enter name') }}" value="{{ old('name', $platform->name) }}" required />
                        </div>
                        <div class="col-md-6 form-control-validation">
                            <label class="form-label" for="base_url">{{ __('Base URL') }}<span
                                    class="text-danger">*</span></label>
                            <input type="url" name="base_url" id="base_url" class="form-control"
                                placeholder="{{ __('Enter base URL') }}"
                                value="{{ old('base_url', $platform->base_url) }}" required />
                        </div>
                        <div class="col-md-5 form-control-validation align-self-center">
                            <label class="form-label" for="image">{{ __('Image') }}</label>
                            <input type="file" name="image" id="image" class="form-control"
                                placeholder="{{ __('Upload image') }}" accept="image/*"
                                onchange="document.getElementById('image_preview').src = window.URL.createObjectURL(this.files[0])" />
                        </div>
                        <div class="col-md-1 form-control-validation">
                            <label class="form-label" for="image_preview">{{ __('Image Preview') }}</label>
                            <div class="image-preview">
                                <img id="image_preview" src="{{ imageShow($platform->image) }}" class="img-fluid rounded"
                                    alt="{{ __('Image Preview') }}" />
                            </div>
                        </div>
                        <div class="col-md-6 form-control-validation">
                            <label class="form-label" for="status">{{ __('Status') }}<span
                                    class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="status" required>
                                <option value="Active" {{ old('status', $platform->status) === 'Active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="Inactive" {{ old('status', $platform->status) === 'Inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-12 form-control-validation">
                            <x-form-action-button :resource="'platforms'" :action="'edit'" :type="'page'" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
