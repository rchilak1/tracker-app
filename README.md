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

Run the below command which will do the following:

- create the database and all necessary tables (USERS, HABITS, RECURRING_HABITS, DAILY_HABITS, PROGRESS, STREAK_HISTORY, and NOTES):

- loads the tables with dummy data from the CSVs directory:

- configures database connection

- runs the app via web server


<br>

### Step 3: Open the Web Interface

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

