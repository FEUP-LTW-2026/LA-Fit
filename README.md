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
- [X] Member Progress Tracking: Members can log workouts, set fitness goals, and track progress over time with charts or statistics.
- [ ] Nutrition Plans: Trainers can create and assign nutrition plans to their members, with meal and calorie tracking.

## Running

Create the SQLite database from the schema and seed data:

    sqlite3 database/database.db < database/database.sql

To view the current project locally, run:

    php -S localhost:9000

Then open this link in the browser:

    http://localhost:9000

Main PHP pages:

- `index.php` - homepage with plans and featured classes from the database.
- `login.php` - client login.
- `inscricao.php` - member registration.
- `perfil.php` - logged-in member area (profile, classes, equipment, reports).
- `aulas.php` - group class schedule and enrollments.
- `equipamentos.php` - equipment availability by zone.
- `report.php` - submit and track issue reports.
- `avaliacao.php` - rate and review attended classes.
- `trainer.php` - trainer area (profile, class schedule, rosters).
- `admin.php` - admin area (accounts, classes, reports).
- `profile_view.php` - public trainer profile.
- `class_roster.php` - enrolled members for a class (trainer/admin).

## Credentials

- admin/p4s5w0rd
- member/1234
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
