@php
    $fieldWrapperView = $getFieldWrapperView();
    $extraAttributeBag = $getExtraAttributeBag();
    $id = $getId();
    $isDisabled = $isDisabled();
    $statePath = $getStatePath();
@endphp
@php
    $isRtl = in_array(app()->getLocale(), ['fa', 'ar', 'he', 'ur']);
@endphp

<x-dynamic-component
    :component="$fieldWrapperView"
    :field="$field"
    :inline-label-vertical-alignment="\Filament\Support\Enums\VerticalAlignment::Start"
>
    <div
        {{
            \Filament\Support\prepare_inherited_attributes($extraAttributeBag)
                ->class(['fi-fo-field fi-fo-shield-captcha'])
        }}
    >
        <div
            x-data="{ isDark: document.documentElement.classList.contains('dark') }"
            x-init="
                const el = document.documentElement;
                const obs = new MutationObserver(() => { isDark = el.classList.contains('dark') });
                obs.observe(el, { attributes: true, attributeFilter: ['class'] });
            "
            style="display: flex !important; position: relative"
        >
            <div class="overflow-hidden">
                <img
                    alt="{{ __('filament-shield-captcha::messages.alt') }}"
                    aria-label="{{ __('filament-shield-captcha::messages.alt') }}"
                    class="fi-input-wrp"
                    :src="isDark ? @js($darkDataUri) : @js($lightDataUri)"
                />
                <div style="padding: 5px;
                        {{ $isRtl ? 'margin-right: 5px;' : 'margin-left: 5px;' }}
                        display: flex; justify-content: center;
                        align-items: center; width: 36px; height: 36px;
                        position: absolute;
                        {{ $isRtl ? 'left: 0;' : 'right: 0;' }}
                        top: 0"
                     class="fi-input-wrp">
                    {{ $getAction('refresh') }}
                </div>
            </div>


        </div>

        <div class="mt-3">
            <x-filament::input.wrapper
                :disabled="$isDisabled"
                :valid="! $errors->has($statePath)"
                x-on:focus-input.stop="$el.querySelector('input')?.focus()"
                class="w-full"
            >
                <input
                    {{
                        $attributes
                            ->merge([
                                'id' => $id,
                                'inputmode' => 'text',
                                'autocomplete' => 'off',
                                'autocapitalize' => 'off',
                                'spellcheck' => 'false',
                                'disabled' => $isDisabled,
                                'aria-required' => $isRequired() ? 'true' : 'false',
                                $applyStateBindingModifiers('wire:model') => $statePath,
                            ], escape: false)
                            ->class(['fi-input'])
                    }}
                    type="text"
                />
            </x-filament::input.wrapper>
        </div>
    </div>
</x-dynamic-component>
