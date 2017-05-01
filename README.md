# Slackjaw
Processes exported Slack transcripts for search

## Encompasses three basic functions:
- Ingest slack transcripts
- Perform search across all chat streams
- Present results in a webpage

Not associated with Slack Technologies

## Installation
- Assumes apache is already installed
- Assumes at least PHP v5.5 is already installed and enabled. Enabled PHP should exhibit the following lines in the httpd configuration files (usually found in `/etc/httpd/*.conf`)
- - `AddHandler php5-script .php`
- - `AddType text/html .php`
- - `DirectoryIndex index.php`

1. Clone this repo: `git clone https://github.com/hardwarehuman/slackjaw.git` to where you want the webapp to run
2. edit the `DocumentRoot` value to point to the `slackjaw/web` directory
  * It's generally `"/var/www/html/"` by default
  * If you git cloned into `/var/www/html`, then the new entry is `DocumentRoot "/var/www/html/slackjaw/web"`
