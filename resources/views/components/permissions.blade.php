<div class="row">
    @foreach ($routeList as $key => $value)
        <div class="col-md-3 mt-3">
            <div class="card border border-primary">
                <div class="card-header pb-0">
                    <div class="form-check">
                        <input class="form-check-input border border-primary" type="checkbox" id="{{ $key }}"
                            onclick="$('.item-{{ $key }}').prop('checked', this.checked);">
                        <label class="card-title fw-bold h6" for="{{ $key }}">
                            {{ str_replace('-', ' ', str_replace('_', ' ', ucfirst($key))) }}
                        </label>
                    </div>
                </div>
                <hr class="m-0 text-primary mx-5" />
                <div class="card-body">
                    @foreach ($routeList[$key] as $item => $value)
                        <div class="form-check">
                            <input class="form-check-input item-{{ $key }} border border-primary"
                                type="checkbox" id="{{ $key }}{{ $item }}"
                                {{ $value == 1 ? 'checked' : '' }} name="permission[{{ $key }}][]"
                                value="{{ $item }}">
                            <label class="form-check-label" for="{{ $key }}{{ $item }}">
                                {{ get_readable_action_name($item) }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
