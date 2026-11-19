# Live Swap Face Frame Audit

A small, dependency-free PHP library that compares two decoded RGB video frames and flags a large visual change. It helps narrow down frames for **manual** review; camera motion, lighting and compression can also cause a high score.

```bash
composer require content-true/live-swap-face-frame-audit
```

```php
use ContentTrue\LiveSwapFace\FrameAudit;

// Row-major RGB bytes from two 2×1 frames.
$before = [0, 0, 0, 100, 100, 100];
$after  = [0, 0, 0, 200, 200, 200];
$report = FrameAudit::compare($before, $after, 2, 1, 0.15);
// $report: score (0–1), flagged (bool), pixelsCompared (int).
```

The optional sixth argument is a crop: `['x' => 0, 'y' => 0, 'width' => 1, 'height' => 1]`. Decode and align frames before calling this library; it does not read image files or perform face swapping. Keep an unedited baseline and inspect flagged moments during turning, blinking and occlusion. A numerical difference does not establish authenticity or quality. This check can complement a practical review of [live swap face](https://liveswapface.com/) previews without sending frames to any service.

Requires PHP 8.1+. Run `php -d zend.assertions=1 -d assert.exception=1 tests/smoke.php` from this repository to check core behavior. MIT license.
