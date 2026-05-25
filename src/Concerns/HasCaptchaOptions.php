<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha\Concerns;

use MortezaAshrafi\FilamentShieldCaptcha\Enums\CharacterMode;
use MortezaAshrafi\FilamentShieldCaptcha\Enums\ImageFormat;

trait HasCaptchaOptions
{
    protected ?int $captchaWidth = null;

    protected ?int $captchaHeight = null;

    protected ?int $captchaLength = null;

    protected ?int $captchaFontSize = null;

    protected ?CharacterMode $captchaMode = null;

    protected ?string $captchaCharset = null;

    protected ?bool $captchaCaseSensitive = null;

    protected ?int $captchaNoiseLevel = null;

    protected ?int $captchaNoiseLines = null;

    protected ?int $captchaNoiseDots = null;

    protected ?int $captchaTtlSeconds = null;

    protected ?int $captchaMaxAttempts = null;

    protected ?string $captchaFontKey = null;

    protected ?string $captchaFontPath = null;

    protected ?ImageFormat $captchaFormat = null;

    protected ?int $captchaJpegQuality = null;

    protected ?bool $captchaIsRtl = null;

    protected ?bool $captchaIsLtr = null;

    /** @var array{background: array{0:int,1:int,2:int}, text: array{0:int,1:int,2:int}}|null */
    protected ?array $captchaLightColors = null;

    /** @var array{background: array{0:int,1:int,2:int}, text: array{0:int,1:int,2:int}}|null */
    protected ?array $captchaDarkColors = null;

    public function width(int $width): static
    {
        $this->captchaWidth = $width;

        return $this;
    }

    public function height(int $height): static
    {
        $this->captchaHeight = $height;

        return $this;
    }

    public function length(int $length): static
    {
        $this->captchaLength = $length;

        return $this;
    }

    public function fontSize(int $fontSize): static
    {
        $this->captchaFontSize = $fontSize;

        return $this;
    }

    public function font(string $key): static
    {
        $this->captchaFontKey = $key;

        return $this;
    }

    public function fontPath(string $path): static
    {
        $this->captchaFontPath = $path;

        return $this;
    }

    /**
     * @param  array{0:int,1:int,2:int}  $background
     * @param  array{0:int,1:int,2:int}  $text
     */
    public function lightColors(array $background, array $text): static
    {
        $this->captchaLightColors = ['background' => $background, 'text' => $text];

        return $this;
    }

    /**
     * @param  array{0:int,1:int,2:int}  $background
     * @param  array{0:int,1:int,2:int}  $text
     */
    public function darkColors(array $background, array $text): static
    {
        $this->captchaDarkColors = ['background' => $background, 'text' => $text];

        return $this;
    }

    public function numeric(): static
    {
        $this->captchaMode = CharacterMode::Numeric;

        return $this;
    }

    public function alphabetic(): static
    {
        $this->captchaMode = CharacterMode::Alphabetic;

        return $this;
    }

    public function alphanumeric(): static
    {
        $this->captchaMode = CharacterMode::Alphanumeric;

        return $this;
    }

    public function symbols(string $symbols = '!@#$%^&*+-='): static
    {
        $this->captchaMode = CharacterMode::Custom;
        $this->captchaCharset = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'.$symbols;

        return $this;
    }

    public function charset(string $charset): static
    {
        $this->captchaMode = CharacterMode::Custom;
        $this->captchaCharset = $charset;

        return $this;
    }

    public function caseSensitive(bool $caseSensitive = true): static
    {
        $this->captchaCaseSensitive = $caseSensitive;

        return $this;
    }

    public function noise(int $level, ?int $lines = null, ?int $dots = null): static
    {
        $this->captchaNoiseLevel = $level;

        if ($lines !== null) {
            $this->captchaNoiseLines = $lines;
        }

        if ($dots !== null) {
            $this->captchaNoiseDots = $dots;
        }

        return $this;
    }

    public function ttl(int $seconds): static
    {
        $this->captchaTtlSeconds = $seconds;

        return $this;
    }

    public function maxAttempts(int $maxAttempts): static
    {
        $this->captchaMaxAttempts = $maxAttempts;

        return $this;
    }

    public function format(ImageFormat $format, ?int $jpegQuality = null): static
    {
        $this->captchaFormat = $format;

        if ($jpegQuality !== null) {
            $this->captchaJpegQuality = $jpegQuality;
        }

        return $this;
    }

    public function rtl(bool $rtl = true): static
    {
        $this->captchaIsRtl = $rtl;
        $this->captchaIsLtr = ! $rtl;

        return $this;
    }

    public function ltr(bool $ltr = true): static
    {
        $this->captchaIsLtr = $ltr;
        $this->captchaIsRtl = ! $ltr;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getCaptchaOptionOverrides(): array
    {
        $overrides = [];

        if ($this->captchaWidth !== null) {
            $overrides['width'] = $this->captchaWidth;
        }
        if ($this->captchaHeight !== null) {
            $overrides['height'] = $this->captchaHeight;
        }
        if ($this->captchaLength !== null) {
            $overrides['length'] = $this->captchaLength;
        }
        if ($this->captchaFontSize !== null) {
            $overrides['font_size'] = $this->captchaFontSize;
        }
        if ($this->captchaMode !== null) {
            $overrides['mode'] = $this->captchaMode->value;
        }
        if ($this->captchaCharset !== null) {
            $overrides['charset'] = $this->captchaCharset;
        }
        if ($this->captchaCaseSensitive !== null) {
            $overrides['case_sensitive'] = $this->captchaCaseSensitive;
        }

        $noise = [];
        if ($this->captchaNoiseLevel !== null) {
            $noise['level'] = $this->captchaNoiseLevel;
        }
        if ($this->captchaNoiseLines !== null) {
            $noise['lines'] = $this->captchaNoiseLines;
        }
        if ($this->captchaNoiseDots !== null) {
            $noise['dots'] = $this->captchaNoiseDots;
        }
        if ($noise !== []) {
            $overrides['noise'] = $noise;
        }

        if ($this->captchaTtlSeconds !== null) {
            $overrides['ttl'] = $this->captchaTtlSeconds;
        }
        if ($this->captchaMaxAttempts !== null) {
            $overrides['max_attempts'] = $this->captchaMaxAttempts;
        }

        if ($this->captchaLightColors !== null) {
            $overrides['light_background'] = $this->captchaLightColors['background'];
            $overrides['light_text'] = $this->captchaLightColors['text'];
        }

        if ($this->captchaDarkColors !== null) {
            $overrides['dark_background'] = $this->captchaDarkColors['background'];
            $overrides['dark_text'] = $this->captchaDarkColors['text'];
        }

        if ($this->captchaFontKey !== null) {
            $overrides['font'] = $this->captchaFontKey;
        }

        if ($this->captchaFontPath !== null) {
            $overrides['font_path'] = $this->captchaFontPath;
        }

        if ($this->captchaIsRtl !== null) {
            $overrides['is_rtl'] = $this->captchaIsRtl;
        }

        if ($this->captchaIsLtr !== null) {
            $overrides['is_ltr'] = $this->captchaIsLtr;
        }

        if ($this->captchaFormat !== null) {
            $overrides['format'] = $this->captchaFormat->value;
        }
        if ($this->captchaJpegQuality !== null) {
            $overrides['jpeg_quality'] = $this->captchaJpegQuality;
        }

        return $overrides;
    }
}
