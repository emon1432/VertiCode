@extends('admin.layouts.app')
@section('title', __('Trash'))
@section('content')
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Trash') }}</h5>
        </div>
        <div class="card-datatable">
            <table class="common-datatable table d-table" data-url="{{ route('trash.index') }}"
                data-columns='[
                { "data": "info" },
                { "data": "model" },
                { "data": "deleted_by" },
                { "data": "deleted_at" },
                { "data": "actions" }
                ]'>
                <thead class="border-top">
                    <tr>
                        <th>{{ __('Info') }}</th>
                        <th>{{ __('Item Type') }}</th>
                        <th>{{ __('Deleted By') }}</th>
                        <th>{{ __('Deleted At') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
