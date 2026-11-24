=== PileQuote Material Calculator ===
Contributors: smalltoolshop
Tags: mulch calculator, gravel calculator, concrete calculator, cubic yard, landscaping
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let customers work out how many yards or tons of mulch, topsoil, gravel, sand, or concrete they need, priced with your rates.

== Description ==

Built for landscape supply yards, gravel and topsoil suppliers, garden centers, and contractors. A visitor enters the size of the
area and the depth, and the calculator shows how much to order, rounded the way you sell it, with an estimated price and a button
to your quote or order page.

* **Six materials:** mulch, topsoil, compost, gravel / crushed stone, sand, and concrete. Rename them, hide the ones you don't sell,
  or show a single material on its own page.
* **Sold by volume or weight:** cubic yards or tons (or cubic meters and tonnes). Enter your own density for each material, since
  stone weight varies.
* **Your prices and rules:** price per unit, round orders up to 1/4, 1/2, or whole units, a minimum order, and an extra
  percentage for waste and settling.
* **Four area shapes:** rectangle, circle, triangle, or a known area.
* **Concrete bags:** optionally shows how many 40, 60, and 80 lb bags of premix the same job takes.
* **Quote button:** links the result to your contact, quote, or shop page.
* **Imperial or metric.**
* A block and a shortcode, `[pilequote]`, or `[pilequote material="gravel"]` for one material.

Private and light: the math runs in the visitor's browser. The plugin sends nothing anywhere, sets no cookies, adds no tracking,
and loads its small script and stylesheet only on pages that show a calculator.

= How the numbers are worked out =

* Volume = area x depth. A cubic yard is 27 cubic feet; a cubic meter is 100 x 100 x 100 cm.
* Weight = volume x the density you set. The gravel and sand default, 1.35 tons per cubic yard (1.6 tonnes per cubic meter), is a
  50 lb bag filling 0.5 cubic feet, the figure retailers list for QUIKRETE all-purpose gravel. Use your own figure.
* Concrete bags use the yields on the QUIKRETE Concrete Mix data sheet: 0.30, 0.45, and 0.60 cubic feet for 40, 60, and 80 lb bags.
* Unit conversions use NIST Handbook 44 (1 short ton = 0.90718474 tonnes, 1 cubic yard = 0.764554857984 cubic meters).

Results are estimates for planning an order. Compaction, moisture, and uneven ground change the real amount, so the extra
percentage setting is there for you to cover it.

The calculator code is tested against hand-worked examples and against the engine behind the free
[DIY Project Calculators](https://simpletoolshop.com/diy-project-calculators/) site.

== Installation ==

1. Install from Plugins > Add New, or upload the `pilequote-material-calculator` folder to `/wp-content/plugins/`, then activate it.
2. Go to Settings > Material Calculator. Pick your units, enter prices if you want them shown, and add your quote page link.
3. Add the Material Calculator block to a page, or paste `[pilequote]`.

== Frequently Asked Questions ==

= Does it work with my theme or page builder? =

It uses your theme's fonts and colors and works anywhere a shortcode or block works, including the classic editor.

= Can I show only one material? =

Yes. In the block, pick the material in the sidebar. With the shortcode, add `material="mulch"` (or topsoil, compost, gravel,
sand, concrete).

= I sell gravel by the ton. =

In Settings > Material Calculator, set Gravel to "Sold by: ton" and enter the density your quarry gives you (tons per cubic yard).

= Does it collect visitor data? =

No. Nothing the visitor types leaves their browser.

== Screenshots ==

1. A gravel page sold by the ton: the order is rounded up to the supplier's half-ton step, priced, with a quote button.
2. All materials in one calculator: a concrete slab, with the same job in 40, 60, and 80 lb bags of premix.
3. Settings > Material Calculator: units, prices, rounding, minimum orders, and the quote page.

== Changelog ==

= 0.1.0 =
* First release.
