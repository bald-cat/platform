@component($typeForm, get_defined_vars())
    <div data-controller="lazy"
        data-lazy-placeholder="{{$attributes['placeholder'] ?? ''}}"
        data-lazy-allow-empty="{{ $allowEmpty }}"
        data-lazy-message-notfound="{{ __('No results found') }}"
        data-lazy-allow-add="{{ var_export($allowAdd, true) }}"
        data-lazy-message-add="{{ __('Add') }}"
    >
        <select {{ $attributes }}>
            @foreach($options as $key => $option)
                <option value="{{$key}}"
                        @isset($value)
                            @if (is_array($value) && in_array($key, $value)) selected
                            @elseif (isset($value[$key]) && $value[$key] == $option) selected
                            @elseif ($key == $value) selected
                            @endif
                        @endisset
                >{{$option}}</option>
            @endforeach
        </select>
    </div>
@endcomponent
