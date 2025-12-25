
# Jego

**Jego** is a lightweight YAML-powered static site builder designed for fast, safe, and easy website generation. It supports full HTML/CSS rendering and works with custom themes.

---

## Features

- Build static websites from YAML pages
- Full support for HTML elements and CSS styling
- Safe single-core sequential builds
- Works with custom themes
- Lightning-fast generation for small and medium sites
- Cross-platform (Windows, Linux, macOS)
- No dependencies besides PHP and Spyc

---

## Getting Started

1. Clone the repository:

```bash
git clone https://github.com/VSS-CO/jego.git
cd jego
````

2. Place your pages in the `pages/` directory (YAML files).
3. Configure your site in `site.yml` and theme in `themes/<theme-name>/theme.yml`.
4. Run the build:

```bash
php build.php
```

5. Generated HTML files will appear in the `dist/` folder.

---

## Example YAML Page

```yaml
title: Home
blocks:
  - type: hero
    title: Welcome to Jego
    subtitle: Build static sites easily
  - type: text
    content: This is a simple paragraph block.
  - type: button
    label: Learn More
    href: /about.html
```

---

## License

MIT License

```
```
