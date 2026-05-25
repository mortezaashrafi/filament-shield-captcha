<?php

declare(strict_types=1);

namespace MortezaAshrafi\FilamentShieldCaptcha;

use MortezaAshrafi\FilamentShieldCaptcha\Enums\ImageFormat;
use MortezaAshrafi\FilamentShieldCaptcha\Enums\Theme;
use MortezaAshrafi\FilamentShieldCaptcha\Options\CaptchaOptions;
use MortezaAshrafi\FilamentShieldCaptcha\Support\DeterministicRandom;

final class CaptchaGenerator
{
    public function __construct(
        private readonly CaptchaOptions $options,
    ) {}

    public function toDataUri(string $answer, string $seed, Theme $theme): string
    {
        $rng = new DeterministicRandom($seed.'|'.$theme->value);

        $width = $this->options->width;
        $height = $this->options->height;

        $image = imagecreatetruecolor($width, $height);

        $background = $theme === Theme::Dark ? $this->options->darkBackground : $this->options->lightBackground;
        $textColor = $theme === Theme::Dark ? $this->options->darkText : $this->options->lightText;

        $bg = imagecolorallocate($image, $background->r, $background->g, $background->b);
        $gdTextColor = imagecolorallocate($image, $textColor->r, $textColor->g, $textColor->b);
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        $this->drawNoise($image, $rng, $width, $height, $gdTextColor);
        $this->drawText($image, $rng, $answer, $width, $height, $gdTextColor);

        $data = $this->encodeImage($image);

        imagedestroy($image);

        $mime = $this->options->format === ImageFormat::Jpeg ? 'image/jpeg' : 'image/png';

        return "data:{$mime};base64,".base64_encode($data);
    }

    private function drawNoise(\GdImage $image, DeterministicRandom $rng, int $width, int $height, int $textColor): void
    {
        $level = $this->options->noise->level;

        if ($level <= 0) {
            return;
        }

        $lineCount = (int) round($this->options->noise->lines * (1 + ($level - 1) * 0.35));
        $dotCount = (int) round($this->options->noise->dots * (1 + ($level - 1) * 0.45));

        $lineColor = $this->allocateNoiseColor($image, $rng, $textColor, 0.45);
        $dotColor = $this->allocateNoiseColor($image, $rng, $textColor, 0.35);

        imagesetthickness($image, max(1, min(3, (int) round($level / 2))));

        for ($i = 0; $i < $lineCount; $i++) {
            imageline(
                $image,
                $rng->int(0, $width),
                $rng->int(0, $height),
                $rng->int(0, $width),
                $rng->int(0, $height),
                $lineColor
            );
        }

        for ($i = 0; $i < $dotCount; $i++) {
            imagesetpixel(
                $image,
                $rng->int(0, $width - 1),
                $rng->int(0, $height - 1),
                $dotColor
            );
        }
    }

    private function drawText(\GdImage $image, DeterministicRandom $rng, string $answer, int $width, int $height, int $textColor): void
    {
        $fontFile = $this->options->fontPath;
        $fontSize = $this->options->fontSize;

        $characters = mb_str_split($answer);
        $count = max(1, count($characters));

        $paddingX = (int) round($width * 0.08);
        $availableWidth = max(1, $width - (2 * $paddingX));
        $step = $availableWidth / $count;

        $baseline = (int) round($height * 0.68);
        $maxRotate = 14;
        $jitterY = max(2, (int) round($height * 0.08));

        for ($i = 0; $i < $count; $i++) {
            $char = $characters[$i];
            $angle = $rng->int(-$maxRotate, $maxRotate);
            $x = (int) round($paddingX + ($step * $i) + $rng->int(0, (int) max(1, $step * 0.22)));
            $y = $baseline + $rng->int(-$jitterY, $jitterY);

            imagettftext($image, $fontSize, $angle, $x, $y, $textColor, $fontFile, $char);
        }
    }

    private function allocateNoiseColor(\GdImage $image, DeterministicRandom $rng, int $baseColor, float $alpha): int
    {
        $rgba = imagecolorsforindex($image, $baseColor);

        $r = (int) round($rgba['red'] + ($rng->int(-30, 30)));
        $g = (int) round($rgba['green'] + ($rng->int(-30, 30)));
        $b = (int) round($rgba['blue'] + ($rng->int(-30, 30)));

        $r = max(0, min(255, $r));
        $g = max(0, min(255, $g));
        $b = max(0, min(255, $b));

        $gdAlpha = (int) round((1 - $alpha) * 127);

        return imagecolorallocatealpha($image, $r, $g, $b, max(0, min(127, $gdAlpha)));
    }

    private function encodeImage(\GdImage $image): string
    {
        ob_start();

        try {
            if ($this->options->format === ImageFormat::Jpeg) {
                imagejpeg($image, null, max(0, min(100, $this->options->jpegQuality)));
            } else {
                imagepng($image);
            }

            $data = ob_get_contents();
            if (! is_string($data)) {
                throw new \RuntimeException('Failed to capture CAPTCHA image output.');
            }

            return $data;
        } finally {
            ob_end_clean();
        }
    }
}
