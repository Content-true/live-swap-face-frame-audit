<?php

declare(strict_types=1);

namespace ContentTrue\LiveSwapFace;

use InvalidArgumentException;

final class FrameAudit
{
    /**
     * Compare two flat RGB byte arrays in row-major order. A score of 1 means
     * every compared channel changed by the maximum possible amount.
     *
     * @param list<int> $before Exactly width * height * 3 values in [0, 255].
     * @param list<int> $after  Exactly width * height * 3 values in [0, 255].
     * @param array{x:int,y:int,width:int,height:int}|null $region Optional crop.
     * @return array{score:float,flagged:bool,pixelsCompared:int}
     */
    public static function compare(
        array $before,
        array $after,
        int $width,
        int $height,
        float $threshold = 0.25,
        ?array $region = null
    ): array {
        if ($width < 1 || $height < 1 || $height > intdiv(PHP_INT_MAX, 3) ||
            $width > intdiv(PHP_INT_MAX, $height * 3)) {
            throw new InvalidArgumentException('Frame dimensions must be positive and fit in memory.');
        }
        if (!is_finite($threshold) || $threshold < 0 || $threshold > 1) {
            throw new InvalidArgumentException('Threshold must be between 0 and 1.');
        }
        $expected = $width * $height * 3;
        if (count($before) !== $expected || count($after) !== $expected ||
            !array_is_list($before) || !array_is_list($after)) {
            throw new InvalidArgumentException('Both frames must be flat RGB lists of the stated dimensions.');
        }
        foreach ([$before, $after] as $frame) {
            foreach ($frame as $channel) {
                if (!is_int($channel) || $channel < 0 || $channel > 255) {
                    throw new InvalidArgumentException('Every RGB channel must be an integer from 0 to 255.');
                }
            }
        }
        $region ??= ['x' => 0, 'y' => 0, 'width' => $width, 'height' => $height];
        foreach (['x', 'y', 'width', 'height'] as $key) {
            if (!isset($region[$key]) || !is_int($region[$key])) {
                throw new InvalidArgumentException('Region requires integer x, y, width and height.');
            }
        }
        if ($region['x'] < 0 || $region['y'] < 0 || $region['width'] < 1 || $region['height'] < 1 ||
            $region['x'] > $width - $region['width'] || $region['y'] > $height - $region['height']) {
            throw new InvalidArgumentException('Region must fit inside the frame.');
        }

        $sum = 0;
        for ($y = $region['y']; $y < $region['y'] + $region['height']; $y++) {
            for ($x = $region['x']; $x < $region['x'] + $region['width']; $x++) {
                $offset = ($y * $width + $x) * 3;
                for ($channel = 0; $channel < 3; $channel++) {
                    $sum += abs($before[$offset + $channel] - $after[$offset + $channel]);
                }
            }
        }
        $pixels = $region['width'] * $region['height'];
        $score = $sum / ($pixels * 3 * 255);
        return ['score' => $score, 'flagged' => $score >= $threshold, 'pixelsCompared' => $pixels];
    }
}
