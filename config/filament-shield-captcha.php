<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Storage / Verification
    |--------------------------------------------------------------------------
    |
    | This package stores ONLY the minimum server-side verification state:
    | - the expected answer (server-side only)
    | - attempt count
    | - expiry timestamp
    | - a per-challenge random seed used for deterministic noise rendering
    |
    | No CAPTCHA image bytes are ever stored (or written to disk).
    */
    'store' => [
        /*
         * Cache store name to use. Null uses Laravel's default cache store.
         * Choose a store appropriate for your environment (Redis recommended).
         */
        'cache_store' => env('FILAMENT_SHIELD_CAPTCHA_CACHE_STORE'),

        /*
         * Default time-to-live (seconds) for a challenge.
         */
        'ttl' => (int) env('FILAMENT_SHIELD_CAPTCHA_TTL', 300),

        /*
         * Maximum number of wrong attempts allowed per challenge.
         * Once exceeded, the challenge is invalidated and the user must refresh.
         */
        'max_attempts' => (int) env('FILAMENT_SHIELD_CAPTCHA_MAX_ATTEMPTS', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | GD Guard
    |--------------------------------------------------------------------------
    |
    | Composer already requires ext-gd. This runtime guard exists to provide a
    | developer-friendly, actionable exception when GD/FreeType support is
    | missing or incomplete on the server.
    */
    'gd' => [
        /*
         * When true, the package validates GD capabilities during boot.
         * Disable only if you need your app to boot in environments that do not
         * render CAPTCHA (e.g. queue workers without GD installed).
         */
        'guard_on_boot' => (bool) env('FILAMENT_SHIELD_CAPTCHA_GD_GUARD_ON_BOOT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Defaults (Field Options)
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'width' => (int) env('FILAMENT_SHIELD_CAPTCHA_WIDTH', 220),
        'height' => (int) env('FILAMENT_SHIELD_CAPTCHA_HEIGHT', 60),
        'length' => (int) env('FILAMENT_SHIELD_CAPTCHA_LENGTH', 5),

        /*
         * Supported modes: numeric, alphabetic, alphanumeric, custom
         */
        'mode' => env('FILAMENT_SHIELD_CAPTCHA_MODE', 'alphanumeric'),

        /*
         * Used only when mode=custom.
         */
        'charset' => env('FILAMENT_SHIELD_CAPTCHA_CHARSET', 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'),

        /*
         * When false, answers are compared case-insensitively.
         * Set to true to require exact case matching.
         */
        'case_sensitive' => (bool) env('FILAMENT_SHIELD_CAPTCHA_CASE_SENSITIVE', false),

        'font_size' => (int) env('FILAMENT_SHIELD_CAPTCHA_FONT_SIZE', 26),

        /*
         * PNG is default. JPEG is supported if you prefer smaller payloads,
         * but note that JPEG is lossy and may reduce readability.
         *
         * Supported: png, jpeg
         */
        'format' => env('FILAMENT_SHIELD_CAPTCHA_FORMAT', 'png'),

        /*
         * Used for JPEG output only (0-100). Ignored for PNG.
         */
        'jpeg_quality' => (int) env('FILAMENT_SHIELD_CAPTCHA_JPEG_QUALITY', 85),

        /*
         * Noise is deterministic per challenge (seeded), so the image stays
         * stable across Livewire re-renders until the user refreshes.
         */
        'noise' => [
            'level' => (int) env('FILAMENT_SHIELD_CAPTCHA_NOISE_LEVEL', 2),
            'lines' => (int) env('FILAMENT_SHIELD_CAPTCHA_NOISE_LINES', 3),
            'dots' => (int) env('FILAMENT_SHIELD_CAPTCHA_NOISE_DOTS', 40),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Theming (Light / Dark)
    |--------------------------------------------------------------------------
    |
    | Colors are RGB arrays. These are chosen to remain readable and balanced
    | in Filament panels.
    */
    'colors' => [
        'light' => [
            'background' => [255, 255, 255],
            'text' => [30, 30, 30],
        ],
        'dark' => [
            'background' => [24, 24, 27],
            'text' => [245, 245, 245],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonts
    |--------------------------------------------------------------------------
    |
    | This package ships two bundled fonts (OFL):
    | - "vazirmatn" for Persian/Arabic-friendly shaping in many environments
    | - "noto_sans" as a Latin-friendly default with broad Unicode coverage
    |
    | Note: FreeType glyph coverage can vary by platform. If you need specific
    | glyphs, set a custom local font path.
    */
    'fonts' => [
        'default' => env('FILAMENT_SHIELD_CAPTCHA_FONT', 'noto_sans'),

        /*
         * Default font for RTL locales.
         */
        'rtl' => env('FILAMENT_SHIELD_CAPTCHA_FONT_RTL', 'vazirmatn'),

        /*
         * Default font for LTR locales.
         */
        'ltr' => env('FILAMENT_SHIELD_CAPTCHA_FONT_LTR', 'noto_sans'),

        /*
         * Optional absolute path to a custom font file (TTF).
         * When set, it overrides the selected font key above.
         */
        'custom_path' => env('FILAMENT_SHIELD_CAPTCHA_FONT_PATH'),

        /*
         * Bundled font map (key => relative path inside the package).
         */
        'bundled' => [
            'vazirmatn' => 'resources/fonts/Vazirmatn-Regular.ttf',
            'noto_sans' => 'resources/fonts/NotoSans.ttf',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | UI (Refresh action)
    |--------------------------------------------------------------------------
    */
    'ui' => [
        'refresh' => [
            'label' => env('FILAMENT_SHIELD_CAPTCHA_REFRESH_LABEL', 'Refresh captcha'),
            'icon' => env('FILAMENT_SHIELD_CAPTCHA_REFRESH_ICON', 'heroicon-m-arrow-path'),
        ],
    ],
];
