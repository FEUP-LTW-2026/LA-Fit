# ltw05g05

## Group Members

- Guilherme Martins da Silva - 202404270
- Tomás de Araújo Ribeiro Silva - 202404344
- Pedro Miguel Malhão Meireles - 202306104

## Features

**All users:**
- [X] Register a new account.
- [X] Log in and out.
- [X] Edit their profile, including name, username, password, and profile photo.

**Members:**
- [X] Browse the schedule of available fitness classes, filtering by type, trainer, day, or time.
- [X] Enroll in and cancel enrollment from upcoming classes, subject to capacity limits.
- [X] View trainer profiles, including their specializations and the classes they teach.
- [X] Check the current availability of equipment in the main training area.
- [X] Leave ratings and reviews for classes they have attended.

**Trainers:**
- [X] Manage their public profile, including bio, specializations, and certifications.
- [X] View the roster of members enrolled in their classes.
- [X] Track and manage their assigned class schedule.

**Admins:**
- [X] Manage members and trainers (create, update, and deactivate accounts).
- [X] Manage the class catalog (create, edit, and remove classes) and assign trainers to them.
- [X] Manage equipment in the main training area (add, update availability status, and remove items).
- [X] Elevate a user to admin status.
- [X] Oversee and ensure the smooth operation of the entire system.

**Extra:**
- [X] Define tiered membership plans with different access levels, and allow members to subscribe to or upgrade their plan.
- [X] Admins can view gym-wide metrics such as most popular classes, equipment usage, and member retention.
- [X] Members can report issues (e.g., equipment malfunction, class cancellations) and admins can manage and respond to these reports.
- [X] Members can log workouts, set fitness goals, and track progress over time with charts or statistics.
- [X] Trainers can create and assign nutrition plans to their members, with meal and calorie tracking.

## Running

Create the SQLite database from the schema and seed data:

    sqlite3 database/database.db < database/database.sql

To view the current project locally, run:

    php -S localhost:9000

Then open this link in the browser:

    http://localhost:9000

Main PHP pages:

- `index.php` - homepage with plans and featured classes from the database.
- `login.php` - login for all users.
- `register.php` - member registration.
- `profile.php` - area for members, trainers, and admins (adapts by role).
- `classes.php` - group class schedule, filters, and enrollments.
- `equipment.php` - equipment availability by zone.
- `report.php` - submit and track issue reports.
- `review.php` - rate and review enrolled classes.
- `profile_view.php` - public trainer profile with bio, specializations, and classes.
- `class_roster.php` - enrolled members for a class (trainer only).
- `class_reviews.php` - all member reviews for a class (public).

## Credentials

- admin/p4s5w0rd
- memberbas/1234 (plano Básico)
- member/1234 (plano Ilimitado)
- memberprem/1234 (plano Premium)
- trainer/1234

## Project Structure

```text
ltw-project-ltw05g05/
├── actions/                  # Form handlers and session actions
├── css/                      # Stylesheets for the website
├── database/                 # SQLite schema, data and PHP DB helpers
├── images/                   # Images used in the pages
├── javascript/               # Client-side scripts
├── pages/                    # Public PHP pages
├── templates/                # Reusable PHP templates
├── README.md                 # Project overview and running instructions
└── .gitignore                # Git ignore rules
```

## Images

<img width="1582" height="966" alt="Captura de ecrã 2026-10-05, às 23 38 22" src="https://github.com/user-attachments/assets/af75a97b-7908-4ba8-8275-cee657d9c0fb" />

<img width="1582" height="966" alt="Captura de ecrã 2026-10-05, às 23 37 27" src="https://github.com/user-attachments/assets/fc7edbaf-626a-4cd3-a045-fb0796d7f810" />

<img width="1582" height="966" alt="Captura de ecrã 2026-10-05, às 23 35 32" src="https://github.com/user-attachments/assets/2590b405-8700-4a0c-abe7-be8d2c90df00" />

<img width="1582" height="966" alt="Captura de ecrã 2026-10-05, às 23 35 26" src="https://github.com/user-attachments/assets/d3c80a2f-0d83-43b9-9931-22df8c7e8f82" />

<img width="1582" height="966" alt="Captura de ecrã 2026-10-05, às 23 35 02" src="https://github.com/user-attachments/assets/51bedd06-6a40-4dcc-9928-55f2301b866e" />

<img width="1582" height="966" alt="Captura de ecrã 2026-10-05, às 23 34 51" src="https://github.com/user-attachments/assets/391d47c2-650a-4ab5-bcb4-985adca5ac1a" />

<img width="1582" height="966" alt="Captura de ecrã 2026-10-05, às 23 34 41" src="https://github.com/user-attachments/assets/b30b4f08-9a5f-488f-939b-fa199f976b93" />


<img width="1582" height="966" alt="Captura de ecrã 2026-10-05, às 23 34 35" src="https://github.com/user-attachments/assets/c647fc05-97e6-497d-83a7-f6ee3763998b" />


<img width="1582" height="966" alt="Captura de ecrã 2026-10-05, às 23 34 24" src="https://github.com/user-attachments/assets/563e3234-1276-445c-b972-3ef000bb8e58" />

<img width="1582" height="966" alt="Captura de ecrã 2026-10-05, às 23 34 11" src="https://github.com/user-attachments/assets/ba968652-8176-48fe-ba4f-64b274dcf042" />







