# Helpful Article Vote - WordPress Plugin

Enhance your WordPress site by allowing users to vote on the helpfulness of your articles. This plugin adds "Yes" and "No" voting buttons to the end of each post, enabling feedback collection in a simple and user-friendly manner.

Contents:

- [Requirements](#requirements)
- [Configuration](#configuration)
- [Installation](#installation)
- [Usage](#usage)

## Requirements

- A working WordPress installation (Version 5.0 or higher recommended).
- Basic knowledge of navigating the WordPress admin panel.

## Configuration

After installation, the plugin works out-of-the-box. Default settings apply the voting buttons to all posts. If you wish to customize the appearance or functionality, additional configurations can be done by editing the plugin files directly.

## Installation

1. Download the plugin zip file.
2. Log in to your WordPress dashboard.
3. Navigate to `Plugins > Add New > Upload Plugin`.
4. Upload the zip file and click `Install Now`.
5. Once the installation is complete, activate the plugin by clicking `Activate Plugin`.

## Usage

### For Site Visitors

Visitors can interact with the voting buttons at the end of each article to submit their feedback. After voting, they're shown the current percentage of helpful votes.

### For Site Administrators

Admins can view voting results directly in the WordPress dashboard under the `Posts` section. Each post will display its accumulated votes.

### Customization

- **Styling:** You can customize the style of the voting buttons by editing the plugin's CSS file located at `/wp-content/plugins/helpful-article-vote/css/style.css`.

- **Functionality:** To adjust the functionality, you may edit the `class-helpful-article-vote.php` file within the plugin directory.

## Developing a Theme or Plugin Compatibility

To ensure compatibility with your custom themes or plugins, test the voting functionality extensively and adjust the provided hooks and filters as needed.

### WP CLI Support

While the plugin does not directly integrate with WP CLI commands, WordPress's native CLI functionality remains unaffected and can be used for site management.

### phpMyAdmin

Database management, including viewing vote counts directly in the database, can be performed using phpMyAdmin or similar tools. The voting data is stored in post meta.

---

By following this guide, you should be able to successfully implement the "Helpful Article Vote" plugin on your WordPress site. For further assistance, contact the plugin support.
