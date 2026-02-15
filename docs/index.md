---
layout: home

hero:
  name: LaraSVG
  text: SVG Conversion for Laravel
  tagline: Convert SVG files to PNG, PDF, EPS, and more using a fluent API with Resvg and Inkscape support.
  image:
    src: /banner.svg
    alt: LaraSVG
  actions:
    - theme: brand
      text: Get Started
      link: /introduction
    - theme: alt
      text: View on GitHub
      link: https://github.com/laratusk/larasvg

features:
  - icon: ⚡
    title: Multi-Provider Architecture
    details: Switch between Resvg and Inkscape with a single method call. Use the best tool for each job.
  - icon: 🎯
    title: Fluent API
    details: Chainable methods for dimensions, background, format, and provider-specific options.
  - icon: 💾
    title: Laravel Filesystem
    details: Read from and write to any Laravel disk — S3, local, or any custom driver.
  - icon: 🔌
    title: Stdout Streaming
    details: Pipe conversion output directly to stdout for HTTP response streaming.
  - icon: 🧪
    title: Fully Testable
    details: Built on Laravel's Process facade with full Process::fake() support.
  - icon: 🛠️
    title: Setup Command
    details: Interactive artisan command to detect and install conversion providers.
---
