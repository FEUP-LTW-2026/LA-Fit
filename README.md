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
- [ ] Browse the schedule of available fitness classes, filtering by type, trainer, day, or time.
- [ ] Enroll in and cancel enrollment from upcoming classes, subject to capacity limits.
- [ ] View trainer profiles, including their specializations and the classes they teach.
- [ ] Check the current availability of equipment in the main training area.
- [ ] Leave ratings and reviews for classes they have attended.

**Trainers:**
- [ ] Manage their public profile, including bio, specializations, and certifications.
- [ ] View the roster of members enrolled in their classes.
- [ ] Track and manage their assigned class schedule.

**Admins:**
- [ ] Manage members and trainers (create, update, and deactivate accounts).
- [ ] Manage the class catalog (create, edit, and remove classes) and assign trainers to them.
- [ ] Manage equipment in the main training area (add, update availability status, and remove items).
- [ ] Elevate a user to admin status.
- [ ] Oversee and ensure the smooth operation of the entire system.

**Extra:**
- [ ] Something extra (e.g., personal training bookings, membership plans, waitlist, ...).

## Running

Create the SQLite database from the schema and seed data:

    sqlite3 database/database.db < database/database.sql

To view the current project locally, run:

    php -S localhost:9000

Then open this link in the browser:

    http://localhost:9000

Main PHP pages:

- `index.php` - homepage with plans and featured classes from the database.
- `aulas.php` - group class schedule and enrollments.
- `login.php` - client login.
- `inscricao.php` - member registration.
- `perfil.php` - logged-in user area.

## Credentials

- admin/p4s5w0rd
- member/1234
- trainer/1234

## Project Structure

```text
ltw-project-ltw05g05/
├── actions/                  # Form handlers and session actions
├── css/                      # Stylesheets for the website
├── database/                 # SQLite schema and seed data
├── html/                     # Old static HTML mockups kept for reference
├── images/                   # Images used in the pages
├── templates/                # Reusable PHP templates
├── index.php                 # Main page
├── aulas.php                 # Class schedule page
├── login.php                 # Client login page
├── inscricao.php             # Registration page
├── perfil.php                # Client profile page
├── README.md                 # Project overview and running instructions
└── .gitignore                # Git ignore rules
```
