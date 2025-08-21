# Odds Comparator

A lightweight WordPress plugin that provides an admin interface to configure API settings, bookmakers, markets, and regional preferences for an odds comparison system.

## Features
- Admin menu for **Odds Settings**
- Region selector (UK, EU, AU)
- Securely store an **API Key**
- Choose preferred **bookmakers** and **markets**
- Assign custom **affiliate links** per bookmaker

## Requirements
- WordPress 5.8+  
- PHP 7.4+ (PHP 8.x recommended)

## Installation
1. Download or clone this repository.
2. Upload the plugin folder to `wp-content/plugins/`.
3. Activate **Odds Comparator** from **Plugins** in WordPress.
4. Open **Settings → Odds Comparator** in the admin menu.

## Get an API Key (The Odds API)
1. Go to https://the-odds-api.com/  
2. Create an account and verify your email.
3. Log in and open **API Keys** (or **Dashboard**) to generate a key.
4. Copy your key and paste it into **Settings → Odds Comparator → API Key**.
5. Review your plan’s rate limits on their dashboard and docs, and configure your usage accordingly.

> Tip: Keep your key private. Do not commit it to version control. Use environment variables or the admin settings screen.

## Configuration
Under **Settings → Odds Comparator**, set:
- **API Key** – Your key from The Odds API.
- **Region** – Operational region (e.g., US, UK, EU, AU).
- **Bookmakers** – Select which bookmakers to display odds for.
- **Markets** – Market types such as `h2h`, `spreads`, or `totals`.
- **Bookmaker Links** – Custom affiliate URLs per bookmaker.

## Notes for Developers
- Access to settings is restricted to users with the `manage_options` capability.
- Escape output using `esc_attr()`, `esc_html()`, and `selected()` in forms.
- Consider caching API responses to respect external rate limits.

## Roadmap
- Optional: shortcode/block to render odds tables
- Optional: transient-based caching layer
- Optional: per-region defaults

## License
GPL-2.0-or-later

## Maintainer
[Ankur Parashar](https://github.com/ParasharAnkur)
