<?php
defined('MOODLE_INTERNAL') || die();

class filter_linkpreview extends moodle_text_filter {
    
    public function filter($text, array $options = array()) {
        if (empty($text)) {
            return $text;
        }

        // UPDATED REGEX: We added (.*?) before </a> to capture the link's descriptive text
        $regex = '/<a\s[^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/is';
        
        // Loop through all links found in the resource content
        return preg_replace_callback($regex, array($this, 'generate_card'), $text);
    }

    private function generate_card($matches) {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $url = $matches[1];
        // Clean any stray HTML tags from the inner text just in case Moodle wrapped it in a <span>
        $inner_text = trim(strip_tags($matches[2]));

        // --- THE SMART TEXT RULE ---
        // Normalize both strings by removing 'http://', 'https://', and trailing slashes
        $clean_url = preg_replace('#^https?://#', '', rtrim($url, '/'));
        $clean_inner = preg_replace('#^https?://#', '', rtrim($inner_text, '/'));

        // If the teacher wrote custom descriptive text, abort and return the standard blue link
        if ($clean_url !== $clean_inner) {
            return $matches[0]; 
        }
        // ---------------------------

        // Ensure we aren't trying to fetch or format internal Moodle pages
        if (strpos($url, $CFG->wwwroot) === 0) {
            return $matches[0]; 
        }

        // Use Moodle's native curl helper
        $curl = new curl(array('proxy' => true));
        $curl->setopt(array('CURLOPT_TIMEOUT' => 3, 'CURLOPT_CONNECTTIMEOUT' => 2));
        
        $html_content = $curl->get($url);
        
        // If the server cannot fetch the site, return the standard blue link gracefully
        if (!$html_content || $curl->get_info()['http_code'] != 200) {
            return $matches[0];
        }

        // Fallbacks
        $title = $url;
        $description = '';
        $image = 'https://images.unsplash.com/photo-1546054454-aa26e2b734c7?q=80&w=800&auto=format&fit=crop'; 
        $site_name = parse_url($url, PHP_URL_HOST);

        // Parse DOM for Open Graph metadata
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html_content, 'HTML-ENTITIES', 'UTF-8'));
        $tags = $dom->getElementsByTagName('meta');

        foreach ($tags as $tag) {
            $property = $tag->getAttribute('property');
            $content = $tag->getAttribute('content');

            if ($property === 'og:title') $title = $content;
            if ($property === 'og:description') $description = $content;
            if ($property === 'og:image') $image = $content;
            if ($property === 'og:site_name') $site_name = $content;
        }

        // Output clean visual card HTML structure
        $html = '
        <div class="moodle-link-preview-card" style="margin: 1.5rem auto; max-width: 550px; background-color: #ffffff; border: 1px solid #e1e8ed; border-radius: 12px; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); transition: transform 0.2s ease, box-shadow 0.2s ease;">
            <a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener noreferrer" style="text-decoration: none; color: inherit; display: block;">
                <div style="width: 100%; height: 260px; overflow: hidden; background-color: #f5f8fa; position: relative;">
                    <img src="' . htmlspecialchars($image) . '" alt="Card Image" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                </div>
                <div style="padding: 20px; background-color: #ffffff;">
                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #bb1a1a; letter-spacing: 1px; text-transform: uppercase;">' . htmlspecialchars($site_name) . '</span>
                    </div>
                    <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 700; line-height: 1.4; color: #1c1e21;">
                        ' . htmlspecialchars($title) . '
                    </h3>
                    <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #4b5563; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">
                        ' . htmlspecialchars($description) . '
                    </p>
                </div>
            </a>
        </div>';

        return $html;
    }
}