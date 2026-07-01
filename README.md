# Resume Builder

A full-stack web application built in PHP for creating, managing, and storing digital resume entries. Developed solo as a course project for COMP 1006 (Winter 2026) at Georgian College, across two development phases.

## Features

- **Full CRUD functionality** — create, read, update, and delete resume entries
- **User authentication** — secure registration and login system
- **User profiles** — authenticated users can update their account details or permanently delete their data
- **File attachments** — upload PDF and image files alongside resume entries, with server-side validation for MIME type and file size
- **Smart file handling** — updating an entry can replace an existing file or keep the current one if no new file is provided
- **Spam protection** — Google reCAPTCHA integrated on all entry forms
- **Responsive UI** — built with Bootstrap 5.3
- **Client-side and server-side validation** — ensures data integrity before anything reaches the database

## Tech Stack

- **Backend:** PHP
- **Database:** MySQL, accessed via PDO with prepared statements (migrated from mysqli for improved security and flexibility)
- **Frontend:** HTML, CSS, Bootstrap 5.3
- **Security:** Google reCAPTCHA, server-side input sanitization, prepared statements to prevent SQL injection

## Project History

**Phase One**
- Built core CRUD functionality for resume entries
- Implemented client-side and server-side validation
- Styled the interface with Bootstrap
- Deployed to a live production server

**Phase Two**
- Migrated the database layer from mysqli to PDO, using prepared statements throughout
- Added user authentication (registration and login)
- Added a user profile section for account management
- Extended CRUD functionality to support file attachments
- Implemented file upload validation (MIME type and size checks)
- Integrated Google reCAPTCHA across all forms

## Planned Improvements

- Automated email notifications for account registration and resume updates
- Drag-and-drop file upload functionality
- Search and filter functionality on the main dashboard for sorting entries by skill or job title
- Enhanced visual design with custom styling and more polished UI transitions

## Notes

This project was built and tested with a live server during Phase One; it is not currently deployed, so this repository is intended to be run locally. Setup requires a PHP environment with MySQL and a `.env` or config file for database credentials and reCAPTCHA keys (not included in this repo for security reasons).
