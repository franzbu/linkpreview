# Moodle Link Preview Filter

A lightweight Moodle Text Filter that automatically detects URLs within course content and dynamically replaces them with rich, embedded link previews.

<img width="524" height="406" alt="Screenshot 2026-06-12 at 5 55 32 PM" src="https://github.com/user-attachments/assets/c28b1188-e518-4b4a-9e91-ddf22475e1db" />


## Features
* **Automatic Detection:** Scans text editor content for standard URLs.
* **Rich Embeds:** Converts raw links into visual cards with titles, descriptions, and images (where supported by the target site).
* **Non-Destructive:** Only processes content on display; the underlying raw URL remains intact in the database.

## Architecture: How it Works
This plugin integrates directly into Moodle's core text rendering pipeline. When a page is loaded, the following sequence occurs before the HTML is sent to the user's browser:

1. **The Filter Hook:** Moodle passes raw user content (forum posts, page resources, labels) through the `filter_linkpreview` class.
2. **Regex Parsing:** The filter applies standard regular expressions to identify unformatted web addresses or existing `<a>` tags within the text block.
3. **Metadata Extraction:** For detected URLs, the filter retrieves Open Graph (`og:title`, `og:image`, `og:description`) and standard meta tags from the target webpage. 
4. **DOM Replacement:** The original text URL is seamlessly swapped with a responsive HTML card template containing the fetched metadata.
5. **Caching:** To guarantee high performance, the finalized HTML output is stored in Moodle's native text cache. External sites are only queried once per link, preventing slow page loads.

## Installation

### Method 1: ZIP Upload (Web Interface)
1. Download the latest release from this repository as a `.zip` file.
2. Log in to your Moodle site as an Administrator.
3. Navigate to **Site administration** > **Plugins** > **Install plugins**.
4. Upload the `.zip` file and select **Text filter** as the plugin type (if prompted).
5. Complete the installation and upgrade the Moodle database.

### Method 2: Direct Server Deployment (Terminal)
Clone or extract this repository directly into your Moodle installation's `/filter/` directory.

```bash
cd /path/to/your/moodle/filter/
git clone [https://github.com/franzbu/linkpreview.git](https://github.com/franzbu/linkpreview.git)
```
*Ensure the folder is named exactly `linkpreview` and that your web server has correct ownership (`chown -R www-data:www-data linkpreview`).*

## Activation & Configuration
Once installed, the filter must be enabled before it will process links.

1. Navigate to **Site administration** > **Plugins** > **Filters** > **Manage filters**.
2. Locate the **Link Preview** filter in the list.
3. In the "Active?" column, change the dropdown from *Disabled* to **On**.
4. In the "Apply to" column, ensure it is set to **Content** (or "Content and headings" if desired).

**Troubleshooting Order Conflicts:** If the link previews are not generating, another filter (like the default "Convert URLs into links and images") might be processing the text first. Use the up/down arrows on the Manage Filters page to move the Link Preview filter higher in the execution order.
