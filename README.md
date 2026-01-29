# WordPress Tools Plugin

A comprehensive toolkit for WordPress performance and functionality improvements.

## Features

### Image Tool - WebP Converter

- Automatic conversion of uploaded images to WebP format
- Configurable image resizing (max width/height)
- Adjustable compression quality
- EXIF orientation handling
- Intelligent file size comparison (keeps original if WebP isn't smaller)
- Support for JPEG, PNG, GIF, and HEIC formats
- Requires: ImageMagick PHP extension

### Iran Host Speedup

- Blocks external host requests from known CDNs and services
- Improves performance for Iranian hosting environments
- Reduces unnecessary external API calls

## Installation

1. Extract the plugin folder to `wp-content/plugins/`
2. Activate the plugin from WordPress admin panel
3. Configure settings in **Settings > Media**

## Requirements

- WordPress 5.0+
- PHP 7.2+
- ImageMagick PHP extension (for WebP conversion)

## Configuration

Navigate to **Settings > Media** to configure:

- Enable/disable WebP conversion
- Set maximum image dimensions
- Adjust compression quality (1-100)

## File Structure

```
wp_tools/
├── wp-tools.php              # Main plugin file
├── includes/
│   ├── image-tool.php        # WebP converter module
│   └── iran-host-speedup.php # Performance optimization module
├── assets/
│   └── css/
│       └── image-tool.css    # Admin styles
├── languages/                # Translation files
└── README.md                 # This file
```

## Author

mojtabahbb - https://www.mojtabahbb.ir/

## License

GPL-2.0-or-later
