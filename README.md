# mortezaashrafi/filament-shield-captcha

A production-grade inline Image CAPTCHA field for Filament forms.

- Native **PHP GD** only (no Intervention Image)
- **Zero image file persistence** (no temporary files, nothing written to disk)
- Base64 **data URI** output rendered inline inside Filament forms
- Deterministic noise per challenge (stable across Livewire re-renders)
- Session/cache-based server-side verification with TTL, max attempts, replay protection
- Bundled fonts (OFL): Vazirmatn + Noto Sans, with custom font path support
- **Auto RTL support**: Automatically switches fonts based on locale direction.

## Compatibility

| Package  | Versions           |
|----------|--------------------|
| PHP      | 8.2, 8.3, 8.4, 8.5 |
| Laravel  | 11, 12, 13         |
| Filament | 3, 4, 5            |

## Installation

```bash
composer require mortezaashrafi/filament-shield-captcha
```

### GD requirement

This package requires PHP GD with FreeType support (for `imagettftext()`).

Common installs:
- Debian/Ubuntu: `sudo apt-get install php-gd`
- Alpine: `apk add php82-gd` (match your PHP version)

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=filament-shield-captcha-config
```

Config file: `config/filament-shield-captcha.php`

## Usage in Forms

```php
use MortezaAshrafi\FilamentShieldCaptcha\Forms\Components\Captcha;

Captcha::make('captcha')
    ->required()
```

## Customization (Fluent API)

| Method                        | Description                                     | Range / Limits                 |
|-------------------------------|-------------------------------------------------|--------------------------------|
| `->length(int)`               | Number of characters in CAPTCHA                 | 1 to 12 (recommended 4-6)      |
| `->width(int)`                | Image width in pixels                           | min 100 (recommended 220)      |
| `->height(int)`               | Image height in pixels                          | min 30 (recommended 60)        |
| `->fontSize(int)`             | Font size in pixels                             | 12 to 40 (recommended 26)      |
| `->font(string)`              | Use bundled font key (`vazirmatn`, `noto_sans`) | -                              |
| `->rtl()`                     | Force RTL mode (uses `rtl` font from config)    | -                              |
| `->ltr()`                     | Force LTR mode (uses `ltr` font from config)    | -                              |
| `->numeric()`                 | Numbers only (0-9)                              | -                              |
| `->alphabetic()`              | Letters only (A-Z)                              | -                              |
| `->alphanumeric()`            | Letters and numbers (Default)                   | -                              |
| `->symbols(string)`           | Alphanumeric + custom symbols                   | default symbols: `!@#$%^&*+-=` |
| `->charset(string)`           | Completely custom character set                 | -                              |
| `->caseSensitive(bool)`       | Require exact case matching                     | default: `false`               |
| `->noise(level, lines, dots)` | Adjust image noise complexity                   | level 0-5 (default 2)          |
| `->ttl(int)`                  | Challenge expiry in seconds                     | default 300                    |
| `->maxAttempts(int)`          | Max wrong attempts before lock                  | default 5                      |

Example with full options:

```php
Captcha::make('captcha')
    ->width(220)
    ->height(60)
    ->length(5)
    ->fontSize(26)
    ->font('vazirmatn')
    ->lightColors(background: [255, 255, 255], text: [30, 30, 30])
    ->darkColors(background: [24, 24, 27], text: [245, 245, 245])
    ->alphanumeric()
    ->caseSensitive(false)
    ->noise(level: 2, lines: 3, dots: 40)
    ->ttl(300)
    ->maxAttempts(5)
```

## Integration with Filament Auth Pages

To add CAPTCHA to Filament's built-in Login, Register, or Reset Password pages (v5), follow these steps:

### 1. Create Custom Auth Components

Extend the original Filament Auth pages and use the `InteractsWithCaptcha` trait.

**Login Page:** `app/Filament/Pages/Auth/Login.php`
```php
namespace App\Filament\Pages\Auth;

use Filament\Schemas\Schema;
use Filament\Auth\Pages\Login as BaseLogin;
use MortezaAshrafi\FilamentShieldCaptcha\Concerns\InteractsWithCaptcha;

class Login extends BaseLogin
{
    use InteractsWithCaptcha;

    public function form(Schema $form): Schema
    {
        return parent::form($form)
            ->components([
                ...$form->getComponents(),
                $this->getCaptchaFormComponent(),
            ]);
    }
}
```

**Register Page:** `app/Filament/Pages/Auth/Register.php`
```php
namespace App\Filament\Pages\Auth;

use Filament\Schemas\Schema;
use Filament\Auth\Pages\Register as BaseRegister;
use MortezaAshrafi\FilamentShieldCaptcha\Concerns\InteractsWithCaptcha;

class Register extends BaseRegister
{
    use InteractsWithCaptcha;

    public function form(Schema $form): Schema
    {
        return parent::form($form)
            ->components([
                ...$form->getComponents(),
                $this->getCaptchaFormComponent(),
            ]);
    }
}
```

**Password Reset Page:** `app/Filament/Pages/Auth/RequestPasswordReset.php`
```php
namespace App\Filament\Pages\Auth;

use Filament\Schemas\Schema;
use Filament\Auth\Pages\RequestPasswordReset as BaseRequestPasswordReset;
use MortezaAshrafi\FilamentShieldCaptcha\Concerns\InteractsWithCaptcha;

class Register extends BaseRequestPasswordReset
{
    use InteractsWithCaptcha;

    public function form(Schema $form): Schema
    {
        return parent::form($form)
            ->components([
                ...$form->getComponents(),
                $this->getCaptchaFormComponent(),
            ]);
    }
}
```

### 2. Register Custom Pages in Panel Provider

Update your `AdminPanelProvider.php` (or other panel provider) to use your custom Auth pages:

```php
// app/Providers/Filament/AdminPanelProvider.php

public function panel(Panel $panel): Panel
{
    return $panel
        ->login(\App\Filament\Pages\Auth\Login::class)
        ->registration(\App\Filament\Pages\Auth\Register::class)
        ->passwordReset(\App\Filament\Pages\Auth\RequestPasswordReset::class)
        // ...
}
```

## RTL / LTR Support

The package automatically detects the current locale direction. You can configure default fonts for each direction in the config file:

```php
'fonts' => [
    'rtl' => 'vazirmatn',
    'ltr' => 'noto_sans',
],
```

## Security & Architecture

- **In-Memory Generation**: Images are created via GD and encoded to base64 data URIs. No files are ever written to the disk.
- **Server-Side Verification**: The expected answer is never exposed to the client.
- **Replay Protection**: Successful verification invalidates the challenge immediately.
- **Rate Limiting**: Wrong attempts increment a counter; once `maxAttempts` is reached, the challenge is locked and must be refreshed.

## Fonts

Bundled (OFL):
- **Vazirmatn**: Excellent for Persian/Arabic-friendly UI.
- **Noto Sans**: Reliable Latin-friendly default.

To use your own font:
```php
Captcha::make('captcha')->fontPath(storage_path('fonts/my-font.ttf'));
```

## Testing

```bash
composer test      # Runs PHPUnit suite
composer analyse   # Runs PHPStan static analysis
composer format    # Runs Laravel Pint formatter
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
