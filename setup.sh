#!/bin/bash

echo "### Tracker App - Setup Script ###"
echo ""

# Step 1: Run SQL scripts to create the database and tables
echo "Creating database and tables..."
mysql -u root < create.sql

# Check if the database creation was successful
if [ $? -ne 0 ]; then
    echo "Error: Failed to create the database or tables. Please check the create.sql file."
    exit 1
fi

echo "Loading dummy data..."
mysql --local-infile=1 -u root < load.sql

# Check if loading the data was successful
if [ $? -ne 0 ]; then
    echo "Error: Failed to load data from load.sql. Please check if local_infile is enabled."
    echo "To enable it, add this to your MySQL config and restart the server:"
    echo ""
    echo "  [mysqld]"
    echo "  local_infile = 1"
    echo ""
    exit 1
fi

# Step 2: Run the app via PHP's built-in web server
echo "App is running at http://127.0.0.1:8000/main.php"
echo "Starting PHP built-in web server..."
php -S 127.0.0.1:8000
echo ""

echo "### Setup Complete ###"
