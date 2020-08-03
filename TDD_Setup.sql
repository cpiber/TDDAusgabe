/* Existierende Dateien laden */
/* URL beliebig änderbar */
LOAD DATA LOCAL INFILE 'http://localhost/Familien.txt' INTO TABLE Familien COLUMNS TERMINATED BY '\t';
