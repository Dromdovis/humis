@props([
    'name',
    'options' => [],
    'value' => null,
    'placeholder' => 'Pasirinkite...',
    'searchPlaceholder' => 'Ieškoti...',
    'required' => false,
    'disabled' => false,
    'labelField' => 'name',
    'valueField' => 'id',
    'showAvatar' => false,
    'avatarColorField' => 'color',
])

@php
    $uniqueId = 'ss-' . uniqid();
    $selectedOption = collect($options)->firstWhere($valueField, $value);
@endphp

<div class="searchable-select" id="{{ $uniqueId }}" data-name="{{ $name }}">
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" {{ $required ? 'required' : '' }}>
    
    <button type="button" 
            class="searchable-select__trigger form-input" 
            {{ $disabled ? 'disabled' : '' }}
            aria-haspopup="listbox">
        <span class="searchable-select__value">
            @if($selectedOption)
                @if($showAvatar)
                    <span class="searchable-select__avatar" style="background: {{ $selectedOption[$avatarColorField] ?? '#6366f1' }}">
                        {{ substr($selectedOption[$labelField], 0, 1) }}
                    </span>
                @endif
                {{ $selectedOption[$labelField] }}
            @else
                <span class="searchable-select__placeholder">{{ $placeholder }}</span>
            @endif
        </span>
        <svg class="searchable-select__arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>
    
    <div class="searchable-select__dropdown">
        <div class="searchable-select__search-wrapper">
            <svg class="searchable-select__search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <input type="text" 
                   class="searchable-select__search" 
                   placeholder="{{ $searchPlaceholder }}"
                   autocomplete="off">
        </div>
        
        <ul class="searchable-select__options" role="listbox">
            @foreach($options as $option)
                <li class="searchable-select__option {{ $value == $option[$valueField] ? 'searchable-select__option--selected' : '' }}" 
                    data-value="{{ $option[$valueField] }}"
                    data-label="{{ $option[$labelField] }}"
                    data-color="{{ $option[$avatarColorField] ?? '#6366f1' }}"
                    role="option">
                    @if($showAvatar)
                        <span class="searchable-select__avatar" style="background: {{ $option[$avatarColorField] ?? '#6366f1' }}">
                            {{ substr($option[$labelField], 0, 1) }}
                        </span>
                    @endif
                    <span class="searchable-select__option-label">{{ $option[$labelField] }}</span>
                </li>
            @endforeach
        </ul>
        
        <div class="searchable-select__empty" style="display: none;">
            Nieko nerasta
        </div>
    </div>
</div>
