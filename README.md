# Portfolio CPT Plugin

Custom WordPress plugin for managing portfolio content with custom post types and fields.

## What it does

Creates two custom post types:
- **Case Studies** - For UX/UI design projects
- **Portfolio Items** - For web development projects

Includes custom fields for project metadata (client, role, tools, duration, etc.)

## Installation

1. Clone this repo into `/wp-content/plugins/`:
```bash
cd wp-content/plugins/
git clone https://github.com/Mella187/portfolio-cpt-plugin.git
```

2. Activate the plugin in WordPress admin

## Custom Fields

### Case Studies
- Client/Project name
- Your role
- Tools used
- Duration
- External link (optional)

### Portfolio Items
- Tech stack
- Role
- Live site URL
- GitHub repo URL

## REST API

All post types are exposed via WordPress REST API for headless implementation.
