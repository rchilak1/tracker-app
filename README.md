# Tracker App

A PHP/MySQL task tracking web application designed to help users manage recurring tasks, daily tasks, notes, track their streaks, and visualize progress through graphs.   
<br>


## Prerequisites  

Before starting, make sure you meet the following prereq's:

PHP 7.4 or later with PDO and MySQL extensions enabled.

MySQL or MariaDB database server installed.

Git installed to clone and manage the repository.

<br>

## Setup and Installation

Follow these steps carefully to set up the application locally:

### Step 1: Clone the Repository

Clone the GitHub repository to your local machine and navigate to the project directory:

git clone https://github.com/rchilak1/tracker-app.git
cd tracker-app

<br>

### Step 2: Set Up the Database

Create your database and tables:

mysql -u <db_user> -p < create.sql

This script creates all necessary tables (USERS, HABITS, RECURRING_HABITS, DAILY_HABITS, PROGRESS, STREAK_HISTORY, and NOTES).

To seed your database with example data (optional but recommended for quick testing):

mysql -u <db_user> -p < load.sql

This populates your tables with initial sample data located in the CSVs directory.

<br>

### Step 3: Configure Database Connection

Open the file db_connect.php and set your database credentials as follows:

<img width="1252" alt="image" src="https://github.com/user-attachments/assets/aede4fa0-2f71-4f88-8259-a5da4458e100" />

<br>

### Step 4: Run the app via web server

Run PHP's built-in web server for quick local setup with the following command:

php -S 127.0.0.1:8000

<br>

### Step 5: Open the Web Interface

Access the application in your browser by going here: http://127.0.0.1:8000/main.php

This is the main page. The streaks and graphs pages can be accessed via the links below (or from the main page) but are not yet set up.

http://127.0.0.1:8000/streaks.php

http://127.0.0.1:8000/graphs.php

<br>

## File Structure Explanation

<img width="577" alt="image" src="https://github.com/user-attachments/assets/99d59602-9035-49dc-bfa7-25fc416e8b53" />

<br>

## Contributing

To contribute, follow these steps:

Fork the repository.

Create a new branch: git checkout -b feature/your-feature-name

Commit your changes: git commit -am "Description of your feature"

Push the branch: git push origin feature/your-feature-name



Built and maintained by the Routine App Team. Happy tracking!

