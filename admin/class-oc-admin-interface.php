<?php
class OC_Admin_Interface {
    
    public function __construct() {
        // Hook into WordPress admin menu and init actions
        add_action('admin_menu', [$this, 'create_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    // Registers admin menu entry for Odds Settings
    public function create_menu() {
        add_menu_page(
            'Odds Comparator Settings',        // Page title (browser tab & top heading)
            'Odds Comparator',                 // Menu label in sidebar
            'manage_options',                  // Capability required
            'oc-settings',                     // Unique slug
            [$this, 'settings_page'],          // Callback
            'dashicons-chart-area',            // Icon (Dashicon or custom URL)
            26                                 // Menu position (optional, 26 = below Settings)
        );
    }

    // Registers settings so they can be saved via options.php
    public function register_settings() {
        register_setting('aoc-settings-group', 'aoc_api_key');
        register_setting('aoc-settings-group', 'aoc_region');
        register_setting('aoc-settings-group', 'aoc_selected_bookmakers');
        register_setting('aoc-settings-group', 'aoc_selected_markets');
        register_setting('aoc-settings-group', 'aoc_bookmaker_links');
        register_setting('aoc-settings-group', 'aoc_odds_format');

    }

    // Displays the admin settings screen
    public function settings_page() {
        $api_key = get_option('aoc_api_key', '');
        $selected_bookmakers = (array) get_option('aoc_selected_bookmakers', []);
        $selected_markets = (array) get_option('aoc_selected_markets', []);
        $bookmaker_links = get_option('aoc_bookmaker_links', []);
        $odds_format = get_option('aoc_odds_format', 'decimal');

        $bookmakers = ['Bet365', 'William Hill', 'Ladbrokes', 'Unibet', 'LiveScore Bet', 'Virgin Bet', 'Betfair', 'BoyleSports', 'Betway', '888sport'];
        $markets = ['h2h', 'spreads', 'totals'];
        ?>
        <div class="wrap oc-settings">
            <h1>⚡ Odds Comparator Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('aoc-settings-group'); ?>
                <div class="oc-card">
                    <h2>General</h2>
                    <label><strong>Select Region</strong></label><br>
                    <select name="aoc_region">
                        <option value="uk" <?php selected(get_option('aoc_region'), 'uk'); ?>>United Kingdom (UK)</option>
                        <option value="eu" <?php selected(get_option('aoc_region'), 'eu'); ?>>Europe (EU)</option>
                        <option value="au" <?php selected(get_option('aoc_region'), 'au'); ?>>Australia (AU)</option>
                    </select>
                    <br><br>
                    <label><strong>API Key</strong></label><br>
                    <input type="text" name="aoc_api_key" value="<?php echo esc_attr($api_key); ?>" style="width:400px;">
                </div>

                <div class="oc-card">
                    <h2>Bookmakers</h2>
                    <div class="oc-check-card">
                        <?php foreach ($bookmakers as $bm): ?>
                            <label class="oc-toggle">
                                <input type="checkbox" name="aoc_selected_bookmakers[]" value="<?php echo esc_attr($bm); ?>"
                                    <?php checked(in_array($bm, $selected_bookmakers)); ?>>
                                <span><?php echo esc_html($bm); ?></span>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="oc-card">
                    <h2>Markets</h2>
                    <?php foreach ($markets as $market): ?>
                        <label class="oc-toggle">
                            <input type="checkbox" name="aoc_selected_markets[]" value="<?php echo esc_attr($market); ?>"
                                <?php checked(in_array($market, $selected_markets)); ?>>
                            <span><?php echo esc_html($market); ?></span>
                        </label><br>
                    <?php endforeach; ?>
                </div>

                <div class="oc-card">
                    <h2>Bookmaker Links</h2>
                    <?php foreach ($bookmakers as $bm): ?>
                        <label><strong><?php echo $bm; ?> Link:</strong></label><br>
                        <input type="text" name="aoc_bookmaker_links[<?php echo esc_attr($bm); ?>]" 
                            value="<?php echo esc_attr($bookmaker_links[$bm] ?? ''); ?>" style="width:400px;"><br><br>
                    <?php endforeach; ?>
                </div>

                <div class="oc-card">
                    <h2>Odds Format</h2>
                    <select name="aoc_odds_format">
                        <option value="decimal" <?php selected($odds_format, 'decimal'); ?>>Decimal</option>
                        <option value="fractional" <?php selected($odds_format, 'fractional'); ?>>Fractional</option>
                        <option value="american" <?php selected($odds_format, 'american'); ?>>American</option>
                    </select>
                </div>

                <?php submit_button('Save Settings'); ?>
            </form>
        </div>

        <style>
            .oc-settings { max-width: 800px; }
            .oc-card {
                background: #fff;
                padding: 20px;
                margin: 15px 0;
                border: 1px solid #ccd0d4;
                border-radius: 8px;
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
            }
            .oc-card h2 { margin-top: 0; font-size: 1.2em; }
            .oc-toggle { display: flex; align-items: center; margin: 5px 0; }
            .oc-toggle input { margin-right: 8px; }
            /* 3 columns for the checkbox list */
            .oc-check-card{
              display: grid;
              grid-template-columns: repeat(3, minmax(0, 1fr)); /* 3 per row */
              column-gap: 24px;
              row-gap: 10px;
            }

            /* If <br> remains in the loop, hide it so it doesn't create empty cells */
            .oc-check-card br{ display:none; }

            /* Tidy up each label */
            .oc-toggle{
              display: inline-flex;         /* was flex; inline prevents odd stretching */
              align-items: center;
              gap: 6px;
              margin: 0;                    /* remove extra vertical spacing */
            }
            .oc-toggle input{ margin-right: 8px; flex: 0 0 auto; }

            /* Optional: responsive */
            @media (max-width:900px){ .oc-check-card{ grid-template-columns: repeat(2, 1fr); } }
            @media (max-width:560px){ .oc-check-card{ grid-template-columns: 1fr; } }

        </style>
        <?php
    }

}
