<?php
define("USERNAME", getenv("USERNAME"));
define("PASSWORD", getenv("PASSWORD"));

define("MODE", "sql");

define("DIRPATH", "test");

define("DB_MODE", "mysql");
define("DB_HOST", getenv("MYSQL_ADDON_HOST"));
define("DB_PORT", getenv("MYSQL_ADDON_PORT"));
define("DB_NAME", getenv("MYSQL_ADDON_DB"));
define("DB_USERNAME", getenv("MYSQL_ADDON_USER"));
define("DB_PASSWORD", getenv("MYSQL_ADDON_PASSWORD"));
define("DB_TABLE", "logs");
