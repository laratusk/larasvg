import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'LaraSVG',
  description: 'A modern Laravel package for SVG conversion with multiple provider support.',
  base: '/',

  sitemap: {
    hostname: 'https://larasvg.laratusk.org',
  },

  head: [
    ['link', { rel: 'icon', type: 'image/png', href: '/laratusk-512.png' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:title', content: 'LaraSVG' }],
    ['meta', { property: 'og:description', content: 'A modern Laravel package for SVG conversion with multiple provider support.' }],
    ['meta', { property: 'og:image', content: '/banner.png' }],
    ['script', { async: '', src: 'https://www.googletagmanager.com/gtag/js?id=G-6Y6PEP1NW0' }],
    ['script', {}, "window.dataLayer = window.dataLayer || [];\nfunction gtag(){dataLayer.push(arguments);}\ngtag('js', new Date());\ngtag('config', 'G-6Y6PEP1NW0');"],
  ],

  themeConfig: {
    logo: '/logo.svg',
    siteTitle: false,

    nav: [
      { text: 'Guide', link: '/introduction' },
      { text: 'API Reference', link: '/api/facade' },
      {
        text: 'GitHub',
        link: 'https://github.com/laratusk/larasvg',
      },
    ],

    sidebar: [
      {
        text: 'Guide',
        items: [
          { text: 'Introduction', link: '/introduction' },
          { text: 'Installation', link: '/installation' },
          { text: 'Configuration', link: '/configuration' },
          { text: 'Quick Start', link: '/quick-start' },
        ],
      },
      {
        text: 'Usage',
        items: [
          { text: 'Basic Conversion', link: '/usage/basic-conversion' },
          { text: 'Dimensions & DPI', link: '/usage/dimensions-and-dpi' },
          { text: 'Background', link: '/usage/background' },
          { text: 'Output Methods', link: '/usage/output-methods' },
          { text: 'Disk Support', link: '/usage/disk-support' },
          { text: 'Stdout Streaming', link: '/usage/stdout-streaming' },
          { text: 'Dynamic Options', link: '/usage/dynamic-options' },
        ],
      },
      {
        text: 'Providers',
        items: [
          { text: 'Resvg', link: '/providers/resvg' },
          { text: 'Inkscape', link: '/providers/inkscape' },
        ],
      },
      {
        text: 'Advanced',
        items: [
          { text: 'Error Handling', link: '/advanced/error-handling' },
          { text: 'Testing', link: '/advanced/testing' },
          { text: 'Artisan Commands', link: '/advanced/artisan-commands' },
        ],
      },
      {
        text: 'API Reference',
        items: [
          { text: 'Facade', link: '/api/facade' },
          { text: 'Manager', link: '/api/manager' },
          { text: 'Provider Interface', link: '/api/provider-interface' },
          { text: 'Converters', link: '/api/converters' },
          { text: 'Exceptions', link: '/api/exceptions' },
        ],
      },
      {
        text: 'Contributing',
        link: '/contributing',
      },
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/laratusk/larasvg' },
    ],

    search: {
      provider: 'local',
    },

    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright © Laratusk',
    },
  },
})
