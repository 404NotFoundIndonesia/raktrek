# PRD — RakTrek

## Overview

RakTrek is a web-based library management platform that streamlines catalogue exploration, book borrowing, and reading activity management for both patrons and library staff.

---

## Goals

- Develop an efficient, user-friendly platform for library management and patron interactions.
- Improve the borrowing process and reduce manual paperwork.
- Enhance user engagement through personalised recommendations and features.

---

## User Roles

| Role | Description |
|------|-------------|
| **Guest** | Browse catalogue only, no borrowing or account features |
| **Member** | Registered patron — borrow, review, reserve, manage profile |
| **Staff** | Library employee — manage catalogue, users, borrowing records via admin dashboard |

Role is stored as a `role` field on the `user` table.

---

## Features

### 1. User Authentication & Registration

- Register with name, email, phone, address, and password.
- Log in via email/password or Google (OAuth via `google_id`).
- Profile management: update personal info, change password.
- Staff accounts granted elevated privileges via `role` field.

**Acceptance criteria:**
- Password confirmation required on register.
- Google One Tap login redirects authenticated users to home.
- Guests redirected away from auth pages if already logged in.

---

### 2. Catalogue Browsing

- Display paginated list of books with cover images (from `book_image`).
- Search by title, author name, publisher.
- Filter by genre, publication year, language, availability.
- Sort by title, year, rating.

**Acceptance criteria:**
- Availability flag on `book` reflects real-time borrow state.
- Filters are combinable and shareable via URL params.

---

### 3. Book Details

Each book detail page shows:

| Data | Source |
|------|--------|
| Title, synopsis, publisher, language, year | `book` |
| Page count | `book.page_number` |
| Author name, bio, photo | `author` |
| Genres | `book_genres` → `genre` |
| Cover images | `book_image` |
| Availability status | `book.availability` |
| Average rating & reviews | `review` |

**Acceptance criteria:**
- Multiple images supported; display as gallery.
- Availability badge: "Available" / "Borrowed" / "On Waitlist".

---

### 4. Borrowing & Returns

- Members borrow available books; system sets `due_date` on `borrowing` record.
- Return recorded by updating `return_date`.
- `book.availability` updated on borrow/return.
- Renewal extends `due_date` if no waitlist queue exists.

**Acceptance criteria:**
- A user cannot borrow a book already checked out to them.
- Staff can record returns on behalf of members.
- Overdue books clearly flagged (current date > `due_date` and `return_date` is null).

---

### 5. Reservations & Waitlists

- Members reserve books currently on loan; creates a `waitlist` record.
- `finish_date` on `waitlist` represents the reservation expiry — if not claimed by then, next member in queue is notified.
- When a book is returned, the first member on the waitlist is notified.

**Acceptance criteria:**
- Members can cancel their waitlist position.
- Staff can see full waitlist queue per book.
- A member cannot appear more than once per book in the waitlist.

---

### 6. Personalised Recommendations

- Members mark favourite authors (`favourite_authors` table).
- Recommendations surface:
  - New books by favourite authors.
  - Books in genres the member has borrowed or reviewed.
  - Highly-rated books not yet read by the member.

**Acceptance criteria:**
- Recommendation section visible on home/explore page for logged-in members.
- Falls back to popular/highly-rated books for guests or new members with no history.

---

### 7. User Reviews & Ratings

- Members submit a `rate` (numeric) and optional `comment` per book via `review` table.
- One review per member per book.
- Book detail page displays average rating and all reviews.

**Acceptance criteria:**
- Rating scale: 1–5.
- Members can edit or delete their own review.
- Average rating recalculated on each review change.

---

### 8. Notifications

Trigger points:

| Event | Recipient |
|-------|-----------|
| Due date approaching (e.g. 3 days before) | Borrowing member |
| Book overdue | Borrowing member |
| Waitlisted book becomes available | First member in queue |
| New book by a favourite author added | All members who favourited that author |

**Acceptance criteria:**
- Notifications delivered in-app (notification centre) and optionally via email.
- Members can configure notification preferences.

---

### 9. Admin Dashboard

Staff-only area covering:

- **Catalogue management** — add, edit, soft-delete books; manage authors and genres; upload cover images.
- **User management** — view, activate/deactivate member accounts; promote to staff.
- **Borrowing records** — view active loans, process returns, flag overdue.
- **Waitlist management** — view and manage queue per book.
- **Reports** — most borrowed books, overdue summary, active members.

**Acceptance criteria:**
- Dashboard access gated by `role = staff`.
- Soft-delete used for books (`deleted_at`); records preserved in borrowing history.

---

## Data Model Summary

```
user            — id, name, email, phone, address, role, password, google_id
book            — id, title, page_number, synopsis, publication_year, publisher, language, author_id, availability
author          — id, name, about, photo
genre           — id, name, description
book_genres     — id, book_id, genre_id          (pivot)
favourite_authors — id, user_id, author_id       (pivot)
borrowing       — id, user_id, book_id, due_date, return_date
waitlist        — id, user_id, book_id, finish_date
review          — id, user_id, book_id, rate, comment
book_image      — id, book_id, path, description
```

---

## User Stories

| # | Story |
|---|-------|
| 1 | As a user, I want to browse the library's catalogue and find books of interest. |
| 2 | As a user, I want to borrow and return books online, avoiding manual processes. |
| 3 | As a user, I want personalised book recommendations based on my preferences. |
| 4 | As a user, I want to see reviews and ratings to help me choose books to read. |
| 5 | As a user, I want to receive notifications about upcoming due dates and available books. |
| 6 | As a staff member, I want an admin dashboard to manage the library's operations efficiently. |

---

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Frontend | Svelte 4 via Inertia.js |
| Backend | Laravel 10 |
| Database | PostgreSQL |
| Auth | Laravel Sanctum (session + API tokens) |
| Asset pipeline | Vite + laravel-vite-plugin |

---

## Out of Scope

- Mobile native apps.
- Physical barcode/RFID scanning integration.
- Payment processing for fines.
- Multi-branch library support.
