<?php
// Define a class named OC_Scraper to handle odds fetching
class OC_Scraper {

    // This is a static method that fetches odds data from an external API
    public static function fetch_odds() {
        // Get the API key saved in WordPress settings
        $api_key = get_option('aoc_api_key');

        // Get selected bookmakers and markets from settings, default to empty array
        $selected_bookmakers = get_option('aoc_selected_bookmakers', []);
        $selected_markets = get_option('aoc_selected_markets', []);

        // Ensure that the selected_bookmakers and selected_markets are arrays
        if (!is_array($selected_bookmakers)) $selected_bookmakers = [];
        if (!is_array($selected_markets)) $selected_markets = [];

        // Get custom links for each bookmaker from settings
        $bookmaker_links = get_option('aoc_bookmaker_links', []);

        // Get the region setting (default to 'us')
        $region = get_option('aoc_region', 'us');

        // If there's no API key, return an empty array (stop here)
        if (!$api_key) return [];

        // Prepare the market parameter for the API URL
        $market_param = count($selected_markets) > 0 ? implode(',', $selected_markets) : '';

        // Build the API URL using region, markets, and API key
        $url = "https://api.the-odds-api.com/v4/sports/upcoming/odds/?regions={$region}&markets={$market_param}&oddsFormat=american&apiKey={$api_key}";

        // Send a GET request to the API
        $response = wp_remote_get($url);

        // If the API request fails, return a message inside an array
        if (is_wp_error($response)) {
            return [['bookmaker' => 'Error', 'odds' => 'API fetch failed']];
        }

        // Get the body (raw JSON) from the response
        $body = wp_remote_retrieve_body($response);

        // Convert JSON string into a PHP array
        $data = json_decode($body, true);

        // Initialize an array to store the odds data
        $odds = [];

        // Check if the decoded data is an array
        if (is_array($data)) {
            // Loop through each event
            foreach ($data as $event) {

                // Loop through each bookmaker within the event
                foreach ($event['bookmakers'] ?? [] as $bookmaker) {
                    $name = $bookmaker['title'];

                    // Skip this bookmaker if it's not in the user's selected list
                    if (is_array($selected_bookmakers) && count($selected_bookmakers) > 0 && !in_array($name, $selected_bookmakers)) continue;

                    // Loop through each market type under this bookmaker
                    foreach ($bookmaker['markets'] ?? [] as $market_data) {

                        // Skip this market if it's not selected by the user
                        if (is_array($selected_markets) && count($selected_markets) > 0 && !in_array($market_data['key'], $selected_markets)) continue;

                        // Loop through each outcome (e.g. team) under the market
                        foreach ($market_data['outcomes'] ?? [] as $outcome) {

                            // Add odds data to the array with key details
                            $odds[] = [
                                'bookmaker' => $name,
                                'market'    => $market_data['key'],
                                'team'      => $outcome['name'],
                                'odds'      => $outcome['price'],
                                'link'      => $bookmaker_links[$name] ?? '#' // Use custom link if available
                            ];
                        }
                    }
                }
            }
        }

        // Return the final odds array
        return $odds;
    }
}
