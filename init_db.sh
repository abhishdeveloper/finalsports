#!/bin/bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS college_sports DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root college_sports < database/schema.sql
echo "Database initialized successfully."
