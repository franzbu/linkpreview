# Link Preview Filter for Moodle

This filter automatically detects URLs in Moodle content and replaces them with an embedded link preview.

## Installation
1. Copy the `linkpreview` folder into your Moodle `/filter/` directory.
2. Log in to your Moodle site as an administrator.
3. Navigate to **Site administration > Notifications** to trigger the database upgrade.
4. Go to **Site administration > Plugins > Filters > Manage filters**.
5. Locate the **Link Preview** filter and set it to **On**.
6. Ensure it is set to apply to **Content** (or "Content and headings" if desired).

## Configuration
* **Filter Order:** In the "Manage filters" page, use the up/down arrows to adjust the order if you encounter conflicts with other filters.
* **Text Cache:** You may need to adjust your "Text cache lifetime" under **Site administration > Appearance > Filter settings** if you notice performance impacts on large sites.