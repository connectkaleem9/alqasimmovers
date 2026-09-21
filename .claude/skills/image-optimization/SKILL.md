---
name: image-optimization
description: Prepare and publish images correctly. Use whenever an image is added.
---

# Image Optimization

Project: Al Qasim Movers — movers & packers, Dubai (https://alqasimmovers.com). Follow `CLAUDE.md` and the rules in `.claude/rules/`, especially `content-integrity-rules.md`.

## Instructions
1. Folder by purpose: `src/images/{logo,hero,services,projects,team,areas,icons}`.
2. Descriptive, lowercase, hyphenated filenames (`villa-movers-dubai.webp`, never `IMG_1234.webp`).
3. Export at needed sizes (e.g. 480/800/1200/1600 w); AVIF + WebP, JPEG fallback only if needed.
4. Always set `width`/`height`; use `srcset`/`sizes`; lazy-load below the fold.
5. Descriptive alt text describing the image content; decorative → `alt=""`.
6. Real company photos preferred; confirm owner has rights; strip EXIF location data.
7. Stock images never presented as the company's own work.

## Checklist
- [ ] Descriptive filename
- [ ] Modern format
- [ ] Responsive sizes
- [ ] Dimensions set
- [ ] Alt text
- [ ] Rights confirmed
- [ ] EXIF stripped
- [ ] Hero < 120 KB

## Expected Output
Optimised images in `src/images/…` and an entry in `docs/design/image-register.md` (file, source, rights, alt).
