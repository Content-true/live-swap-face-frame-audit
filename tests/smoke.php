<?php

declare(strict_types=1);

require __DIR__ . '/../src/FrameAudit.php';

use ContentTrue\LiveSwapFace\FrameAudit;

$result = FrameAudit::compare([0, 0, 0, 100, 100, 100], [0, 0, 0, 200, 200, 200], 2, 1, 0.15);
assert(abs($result['score'] - (300 / 1530)) < 0.00001);
assert($result['flagged'] === true);
assert($result['pixelsCompared'] === 2);
$crop = FrameAudit::compare([0, 0, 0, 100, 100, 100], [0, 0, 0, 200, 200, 200], 2, 1, 0.15, ['x' => 0, 'y' => 0, 'width' => 1, 'height' => 1]);
assert($crop['score'] === 0.0);
try {
    FrameAudit::compare([0], [0], 1, 1);
    throw new RuntimeException('Invalid channels were accepted.');
} catch (InvalidArgumentException) {
}
echo "FrameAudit smoke test passed\n";
