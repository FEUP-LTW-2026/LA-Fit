# ltw05g05

## Features

**All users:**
- [ ] Register a new account.
- [ ] Log in and out.
- [ ] Edit their profile, including name, username, password, and profile photo.

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

The database is not implemented yet, so the database command below is kept only as a reference for a future version of the project.

    sqlite3 database/database.db < database/database.sql

To view the current project locally, run:

    php -S localhost:9000

Then open this link in the browser:

    http://localhost:9000

## Credentials

- admin/p4s5w0rd
- member/1234
- trainer/1234

## Project Structure

```text
ltw-project-ltw05g05/
├── css/                      # Stylesheets for the website
├── images/                   # Images used in the pages
├── index.html                # Main page
├── login.html                # Client login page
├── inscricao.html            # Registration page
├── README.md                 # Project overview and running instructions
└── .gitignore                # Git ignore rules
```


