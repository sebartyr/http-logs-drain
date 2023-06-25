# HTTP-LOGS-DRAIN

This tool written with PHP enables an HTTP logs drain for Clever Cloud applications and addons. 
Logs can be sotred in a MySQL/PostgreSQL database or in a text file (`.log` or `.csv`).

## Installation as a Clever Cloud app

First, clone this repository.

Configure the tool in `includes/config.php`

- Using environment variables is the better way : do not share secrets ;)

**Available options :**

| Options          | Description |
| ---------------- | ----------- |
| `username`       | This is the username for the basic HTTP auth |
| `password`       | This is the password for the basic HTTP auth |
| `mode`           | Selected mode to save logs (`csv`, `log`, `sql`) |
| `dirpath`        | Directory where `csv` and `text` logs are stored  |
| `db`             | Database options  |
| `db => mode`     | Select either `mysql` or `pgsql` |
| `db => host`     | Database hostname |
| `db => port`     | Database port |
| `db => dbname`   | Database name |
| `db => username` | Database username |
| `db => password` | Database password |
| `db => logs`     | Table name where logs are stored |

Deploy on your Clever Cloud app
- Create a PHP app using Git (not SFTP)
- Create an PostgreSQL or MysQL addon if needed (SQL mode)
    - The table structure is provided in the repository for MySQL (PostgreSQL is comming)
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
- You can add extra options in `<DRAIN-URL>`. It's useful when you want to store logs of multiple apps.
    - `https://<DRAIN-URL>/?table=<table_name>` to configure another name than the provided one in `includes/config.php` (SQL mode)
    - `https://<DRAIN-URL>/?prefix=<your_prefix>` to configure a prefix to name text files (LOG or CSV mode).