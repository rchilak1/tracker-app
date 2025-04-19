-- create.sql (MySQL syntax)

DROP DATABASE IF EXISTS routine_app;
CREATE DATABASE routine_app;
USE routine_app;

CREATE TABLE Users (
  UserID   INT            AUTO_INCREMENT PRIMARY KEY,
  Username VARCHAR(255)   NOT NULL,
  Email    VARCHAR(255)   NOT NULL,
  Password VARCHAR(255)   NOT NULL,
  CreatedAt DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  LastLogin DATETIME,
  Status   ENUM('Active','Inactive') NOT NULL DEFAULT 'Inactive'
);

CREATE TABLE Habits (
  HabitID   INT            AUTO_INCREMENT PRIMARY KEY,
  UserID    INT            NOT NULL,
  HabitName VARCHAR(255)   NOT NULL,
  StartDate DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Status    ENUM('Complete','Incomplete') NOT NULL DEFAULT 'Incomplete',
  Notes     TEXT,
  FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

CREATE TABLE Recurring_Habits (
  HabitID           INT      PRIMARY KEY,
  RecurrencePattern VARCHAR(50) NOT NULL,
  DueDate           DATE    NOT NULL,
  FOREIGN KEY (HabitID) REFERENCES Habits(HabitID) ON DELETE CASCADE
);

CREATE TABLE Daily_Habits (
  HabitID INT PRIMARY KEY,
  UserID  INT NOT NULL,
  FOREIGN KEY (HabitID) REFERENCES Habits(HabitID) ON DELETE CASCADE,
  FOREIGN KEY (UserID)  REFERENCES Users(UserID) ON DELETE CASCADE
);

CREATE TABLE Progress (
  ProgressID     INT      AUTO_INCREMENT PRIMARY KEY,
  HabitID        INT      NOT NULL,
  CompletionDate DATETIME NOT NULL,
  StreakCount    INT      NOT NULL DEFAULT 0,
  Status         ENUM('Completed','Missed') NOT NULL DEFAULT 'Missed',
  FOREIGN KEY (HabitID) REFERENCES Habits(HabitID) ON DELETE CASCADE
);

CREATE TABLE Streak_History (
  StreakID   INT      AUTO_INCREMENT PRIMARY KEY,
  HabitID    INT      NOT NULL,
  StartDate  DATE     NOT NULL,
  GraphData  TEXT,
  StreakCount INT     NOT NULL DEFAULT 0,
  FOREIGN KEY (HabitID) REFERENCES Habits(HabitID) ON DELETE CASCADE
);

CREATE TABLE Notes (
  NoteID    INT      AUTO_INCREMENT PRIMARY KEY,
  UserID    INT      NOT NULL,
  Content   TEXT     NOT NULL,
  EntryDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

