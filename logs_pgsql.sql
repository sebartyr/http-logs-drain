START TRANSACTION;

DROP TABLE IF EXISTS logs;
CREATE TABLE IF NOT EXISTS logs (
  "id" varchar(16) NOT NULL,
  "date" varchar(255) NOT NULL,
  "instanceid" varchar(255) NOT NULL,
  "logsinfo" text NOT NULL,
  PRIMARY KEY (id)
);
CREATE INDEX date ON logs (date);
COMMIT;