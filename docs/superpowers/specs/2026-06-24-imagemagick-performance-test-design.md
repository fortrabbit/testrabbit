# ImageMagick performance test — design

**Ticket:** MR-110
**Date:** 2026-06-24

## Problem

Clients report that ImageMagick image transforms are much slower on the new
platform than on the old one (observed: 883 → 2693 ms/rendition, ~3× slower).
We need a repeatable, kept-around test that produces a clean, comparable
ms/rendition figure so the same workload can be run on old and new platform apps
and the numbers compared directly.

The existing `App\Tests\ImagickTest` runs automatically on the homepage suite,
varies output format randomly, and gates source formats per platform — none of
which gives a stable cross-platform number. This test is separate and does not
touch it.

## Route

- `GET /imagick-perf` — standalone page. Runs the transform workload only when
  visited, so the heavy job never fires on a normal homepage load.
- New `perf()` method on `App\Http\Controllers\Controller`.
- Registered in `routes/web.php`.

## Workload

- **4 fixed source images**, chosen because every supported platform
  (Ubuntu 18/20/22/24, k8s) can decode them — no HEIC/AVIF, which old platforms
  cannot decode:
  - `public/imagick/jpg-medium.jpeg`
  - `public/imagick/jpg-large.jpg`
  - `public/imagick/png-medium.png`
  - `public/imagick/png-small.png`
- Each source is transformed to `count` renditions.
- **Output is always JPEG**, quality 82, aspect ratio preserved (downscale only).
- Renditions are distinct downscale widths, deterministic, so old and new
  platforms perform identical work.

### `count` parameter

- Controlled by `?count=` URL param. Default **4**. Intended values: 4, 8, 16,
  32, 64.
- `count` = renditions **per source file**. Total renditions = `4 × count`
  (e.g. `?count=64` → 256 renditions).
- `count` is clamped to a sane range (1–64) and cast to int to avoid abuse.

### Width generation

- For a given `count`, generate `count` evenly-spaced target widths descending
  from a max (1600) to a min (200), inclusive of both ends.
- Each width is applied with aspect ratio preserved; the source is only ever
  downscaled, never upscaled (if a computed width exceeds the source width, the
  source width is used).
- Deterministic: same `count` always yields the same width list, so runs are
  comparable across platforms and across repeated runs.

## Measurement

- Each rendition is timed end to end: decode + resize + encode + write to disk,
  via `microtime(true)`.
- Reported figures:
  - Per source: count of renditions, total ms, ms/rendition.
  - **Headline:** overall total renditions, total ms, and **ms/rendition
    average** — directly comparable to the client's 883 → 2693 ms/rendition.
- Context shown on the page: platform (`config('fortrabbit.platform')`), Imagick
  version, and the Imagick resource limits (memory/map/area/disk/file), reusing
  the limit-reading approach from the existing `ImagickTest`.

## Output

- Self-contained minimal Blade view (`resources/views/imagick-perf.blade.php`),
  not wired into the homepage Alpine suite.
- Layout: context block, headline ms/rendition figure, then a per-source results
  table. Plain server-rendered HTML — no JSON/fetch round-trip.
- Temp output written to `imagick/tmp/` (existing `config('imagick.tempLocation')`).
- Temp files are deleted at the **start** of each run so a run reflects only its
  own work and disk does not accumulate. Reuse the cleanup approach already in
  `Controller::deleteTempImages()` (extract or call a shared helper rather than
  duplicating the glob/unlink logic).

## Error handling

- A failure decoding/encoding a single rendition is caught, recorded against
  that source (counted as failed, not timed into the average), and the run
  continues — one bad image must not abort the whole measurement.
- If a source file is missing, it is reported as missing and skipped.

## Out of scope

- No changes to the existing `ImagickTest` or the homepage auto-suite.
- No multiple output formats (JPEG only).
- No worker/queue involvement.
- No GD comparison (this ticket is ImageMagick-specific).

## Deployment

Once merged, deploy to the old-platform testrabbit apps (EU/US, pro/uni) and the
new-platform app, then compare `/imagick-perf?count=16` ms/rendition across them.
Deployment itself is out of scope for the code change.
