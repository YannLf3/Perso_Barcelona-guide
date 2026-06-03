# API Server Architecture

Server uses a simple 3-layer PHP architecture:

- `script.php` (router): read `todo` and dispatch
- `controller.php` (controller): validate input and call model
- `model.php` (model): SQL queries with prepared statements

All responses are returned as JSON.
