# HTTP-LOGS-DRAIN

This tool written with PHP enables an HTTP log drain for Clever Cloud applications and addons. 

It creates an endpoint to get logs of your apps.
Logs can be stored in a MySQL/PostgreSQL database or in a text file (`.log` or `.csv`).

## Installation as a Clever Cloud app

First, clone this repository.

### Tool configuration

/!\ Require PHP 8 and Clever Cloud CLI ([clever-tools](https://www.clever-cloud.com/doc/getting-started/cli/))

Configure the tool in `includes/config.php`

- Using environment variables is the better way: do not share secrets ;)

**Available options:**

| Options          | Description |
| ---------------- | ----------- |
| `USERNAME`       | This is the username for the basic HTTP auth |
| `PASSWORD`       | This is the password for the basic HTTP auth |
| `MODE`           | Selected mode to save logs (`csv`, `log`, `sql`) |
| `DIRPATH`        | Directory where `csv` and `log` files are stored  |
| `DB_`             | Database options  |
| `DB_MODE`     | Select either `mysql` or `pgsql` |
| `DB_HOST`     | Database hostname |
| `DB_PORT`     | Database port |
| `DB_NAME`   | Database name |
| `DB_USERNAME` | Database username |
| `DB_PASSWORD` | Database password |
| `DB_LOGS`     | Default table name where logs are stored |

### Deploy on your Clever Cloud app
- Create a PHP app using Git (not SFTP)
- Create an PostgreSQL or MysQL addon if needed (SQL mode)
    - The table structure is provided in the repository for MySQL and PostgreSQL
    - Update `includes/config.php` with the right environment variables
- Create a [FS Bucket](https://www.clever-cloud.com/doc/deploy/addon/fs-bucket/) addon if needed (CSV or LOG mode)
    - Mount the bucket where log files will be stored on the instance.
- Configure environment variables:
    - `CC_PHP_VERSION=8`
    - Your own environment variables (`USERNAME`, `PASSWORD`...)
- Configure, if you want it, a custom domain name (recommended)
- Run `git commands`
    ```bash
    git add includes/config.php
    git commit -m "deploy HTTP log drain"
    git remote add <...>
    git push clever master
    ```
- Add a log drain on apps you wants to get logs ([check documentation about drains](https://www.clever-cloud.com/doc/administrate/log-management/#exporting-logs-to-an-external-tools))
    ```bash
    clever login
    clever link <app_id> --alias <alias>
    clever drain create [--alias <alias>] HTTP <DRAIN-URL> --username <username> --password <password> 
    ```
- You can add extra options in `<DRAIN-URL>`. It's useful when you want to store logs of multiple apps
    - `https://<DRAIN-URL>/?table=<table_name>` to configure another table name than the provided one in `includes/config.php` (SQL mode)
    - `https://<DRAIN-URL>/?prefix=<your_prefix>` to configure a prefix to name text files (LOG or CSV mode)
    - `https://<DRAIN-URL>/?dirpath=<your_dirpath>` to configure the dirpath where text files are stored (LOG or CSV mode)
    - `https://<DRAIN-URL>/?filename=<your_filename>` to configure the filename of text files (LOG or CSV mode)

## Other features

### Convert logs stored in DB

Logs stored in DB can be converted. Reach `https://<DRAIN-URL>/convertlogs/` to convert the default table to a `.log` file.

The log file which will be created, will be stored by default in a directory in `https://<DRAIN-URL>/convertlogs/converted-logs`.

Some important options are available:
- `https://<DRAIN-URL>/convertlogs/?table=<table_name>` to configure another table name than the default one in `includes/config.php`
- `https://<DRAIN-URL>/convertlogs/?mode=<log or csv>` to configure either log or csv mode
- `https://<DRAIN-URL>/convertlogs/?before=<date>&after=<date>` to configure the date interval
    - Dates are ISO-8601 compliant : `2023-06-24T14:28:54.360Z`
- `https://<DRAIN-URL>/convertlogs/?time=<time_delta>` to configure the time delta
    - `d` = days / `h` = hours / `m` = minutes 
    - For example : logs older than 7 days = `7d` / logs more recent then 7 days = `-7d`

Extra options are also available:
- `https://<DRAIN-URL>/convertlogs/?prefix=<your_prefix>` to configure a prefix to name text files
- `https://<DRAIN-URL>/convertlogs/?dirpath=<your_dirpath>` to configure the dirpath where text files are stored
    - For example : `https://<DRAIN-URL>/convertlogs/foobar`
- `https://<DRAIN-URL>/convertlogs/?filename=<your_filename>` to configure the filename of text file

### Delete logs stored in DB

Logs stored in DB can be deleted. Reach `https://<DRAIN-URL>/deletelogs/` to delete logs in the default table

The number of deleted rows is returned.

Some important options are available:
- `https://<DRAIN-URL>/convertlogs/?table=<table_name>` to configure another table name than the default one in `includes/config.php`
- `https://<DRAIN-URL>/convertlogs/?before=<date>&after=<date>` to configure the date interval
    - Dates are ISO-8601 compliant : `2023-06-24T14:28:54.360Z`
- `https://<DRAIN-URL>/convertlogs/?time=<time_delta>` to configure the time delta
    - `d` = days / `h` = hours / `m` = minutes 
    - For example : logs older than 7 days = `7d` / logs more recent then 7 days = `-7d`

### More configuration...

You could add a [cron job](https://www.clever-cloud.com/doc/administrate/cron/) to store text files on [Cellar](https://www.clever-cloud.com/doc/deploy/addon/cellar/)
