# PileQuote Material Calculator (WordPress plugin)

A free WordPress plugin for landscape supply yards, garden centers, and contractors. A visitor enters the size of the area and
the depth; the calculator shows how much mulch, topsoil, compost, gravel, sand, or concrete to order, rounded the way you sell
it, with your price and a button to your quote page.

**Live demo and download:** https://jesse-py.github.io/landscape-material-calculator/
**Try it in a throwaway WordPress site (WordPress Playground):**
https://playground.wordpress.net/?blueprint-url=https://jesse-py.github.io/landscape-material-calculator/blueprint.json

## Features
- Six materials; rename or hide them, or show one material on its own page.
- Sold by volume or weight: cubic yards or tons (cubic meters or tonnes), with your own density per material.
- Your price per unit, rounding to 1/4, 1/2, or whole units, a minimum order, and an extra percentage for waste.
- Rectangle, circle, triangle, or a known area. Optional concrete premix bag counts (40, 60, 80 lb).
- A block, and the shortcodes `[pilequote]` and `[pilequote material="gravel"]`.
- The math runs in the visitor's browser. The plugin sends nothing anywhere, sets no cookies, and loads its assets only on
  pages that show a calculator.

## Install
1. Download [pilequote-material-calculator-0.1.0.zip](pilequote-material-calculator-0.1.0.zip).
2. WordPress dashboard > Plugins > Add New Plugin > Upload Plugin > Install Now > Activate.
3. Settings > Material Calculator: choose materials, prices, rounding, minimum, and your quote page.
4. Add the Material Calculator block to a page, or paste `[pilequote]`.

Requires WordPress 6.3+ and PHP 7.4+. Tested on WordPress 7.1 with PHP 8.3, and with the WordPress.org Plugin Check tool
(0 errors, 0 warnings).

## Repository
- `pilequote-material-calculator/`: the plugin source (the same files as the zip).
- `blueprint.json`: the WordPress Playground demo (example prices, a demo page, and a contact page for the quote button).
- `index.html`: the demo page. Its calculators are the plugin's own shortcode output, run by the plugin's own script.

Questions, bugs, and feature requests: [open an issue](https://github.com/Jesse-py/landscape-material-calculator/issues).

License: GPLv2 or later (see LICENSE).
