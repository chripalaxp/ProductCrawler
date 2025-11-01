# Mini Product Crawler

A simple web scraping application built as an interview technical assignment. The application fetches product information (title, price, availability) from given URLs and stores the data in a local SQLite database.

## Test URLs

The project includes four test URLs in `urls.txt`:

1. `https://www.dioptra.gr/vivlia/paidika/xristougenna-stin-odo-selidodeikti-6` - ⚠️ Incomplete data (missing availability and price is loaded with js after load so its missing from the raw html)
2. `https://www.psichogios.gr/el/quicksilver.html` - ✅ Complete data
3. `https://www.psichogios.gr/el/zito-epitrapezio-paixnidi-poso-ton-exeis-gia-14.html` - ✅ Complete data
4. `https://www.psichogios.gr/el/animal-planet-falainokarxarias-deluxe-i.html` - ✅ Complete data

> **Note**: URLs with incomplete data demonstrate the application's ability to handle missing fields gracefully, logging warnings while still extracting available information.

## Architecture

The application follows a simple but clean architecture:

```
src/
├── Fetcher.php       # HTTP client with cURL (retry logic, headers, timeouts)
├── Parser.php        # HTML parsing with multiple CSS selector strategies
├── Repository.php    # SQLite database with upsert operations
└── Logger.php        # File-based logging (errors, warnings)

bin/
└── crawl.php         # Main entry point and orchestration

library/
└── simple_html_dom/  # HTML parser library (no external dependencies)

logs/
├── products.sqlite   # Product data storage
└── log.txt          # Application logs
```

### Key Components

**Fetcher**: Handles HTTP requests using cURL with:
- Automatic retry mechanism (one retry after 1 second)
- Custom User-Agent to simulate browser requests
- Proper timeout configuration (20 seconds)
- HTTP redirect following

**Parser**: Extracts product data using CSS selectors with fallback strategies:
- Multiple selector patterns per field (title, price, availability)
- Support for both HTML content and meta tag attributes
- Graceful handling of missing fields

**Repository**: Manages data persistence:
- SQLite for lightweight, serverless storage
- UPSERT operations to handle duplicate URLs
- Automatic schema migration on initialization

**Logger**: Provides visibility into the crawling process:
- Warning logs for missing fields
- Error logs for network or database failures



## Installation & Usage

No external dependencies or Composer required. Simply run:

```bash
php bin/crawl.php
```

The HTML parser library is already included in the `library/simple_html_dom/` directory.

## Configuration

Edit `urls.txt` to add or modify URLs to crawl:

## Output

After running the crawler:

- **`logs/products.sqlite`**: SQLite database containing all extracted product data
- **`logs/log.txt`**: Log file with warnings and errors

