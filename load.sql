-- load.sql (LOCAL enabled CSV loads)
LOAD DATA LOCAL INFILE 'CSVs/users.csv' INTO TABLE Users
  FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'
  (UserID, Username, Email, Password, CreatedAt, LastLogin, Status);

LOAD DATA LOCAL INFILE 'CSVs/habits.csv' INTO TABLE Habits
  FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'
  (HabitID, UserID, HabitName, StartDate, Status, Notes);

LOAD DATA LOCAL INFILE 'CSVs/recurring_habits.csv' INTO TABLE Recurring_Habits
  FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'
  (HabitID, RecurrencePattern, DueDate);

LOAD DATA LOCAL INFILE 'CSVs/daily_habits.csv' INTO TABLE Daily_Habits
  FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'
  (HabitID, UserID);

LOAD DATA LOCAL INFILE 'CSVs/progress.csv' INTO TABLE Progress
  FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'
  (ProgressID, HabitID, CompletionDate, StreakCount, Status);

LOAD DATA LOCAL INFILE 'CSVs/streak_history.csv' INTO TABLE Streak_History
  FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'
  (StreakID, HabitID, StartDate, GraphData, StreakCount);

LOAD DATA LOCAL INFILE 'CSVs/notes.csv' INTO TABLE Notes
  FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'
  (NoteID, UserID, Content, EntryDate);
