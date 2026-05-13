# TASKS.md — RakTrek

Tasks are ordered by dependency. Complete phases in sequence; tasks within a phase can be parallelised unless noted.

Legend: ✅ = Done, 🔲 = Pending

---

## Phase 1 — Foundation

- [x] **Database migrations** — all tables created (`user`, `book`, `author`, `genre`, `book_genres`, `favourite_authors`, `borrowing`, `waitlist`, `review`, `book_image`)
  - DoD: `php artisan migrate` runs clean; all tables exist in DB.

- [x] **Eloquent models** — `User`, `Book`, `Author`, `Genre`, `Borrowing`, `WaitingList`, `Review`, `BookImage` with correct relationships
  - DoD: Relationships (`belongsTo`, `hasMany`, `belongsToMany`) return correct results via Tinker.

- [x] **Inertia + Svelte wiring** — Laravel sends pages via `Inertia::render()`, Svelte receives props
  - DoD: A page renders without JS errors; shared props (`user`, `currentRouteName`, `appName`) accessible in all Svelte pages.

- [ ] **Role middleware** — `EnsureRole` middleware to gate routes by `role` field (`member`, `staff`)
  - DoD: Accessing a `staff`-only route as a `member` returns 403; as `staff` passes through.

- [ ] **Staff seeder** — seed one default staff account for local development
  - DoD: `php artisan db:seed` creates a staff user; login works with seeded credentials.

---

## Phase 2 — Authentication

- [x] **Register** — form with name, email, phone, address, password, password confirmation
  - DoD: Submitting valid data creates a `user` record with `role = member`; user is logged in and redirected to home.

- [x] **Login** — email + password form, "remember me" option
  - DoD: Valid credentials log user in; invalid credentials return translated error from `auth.failed`; guests on auth pages redirect to home if already logged in.

- [ ] **Logout** — POST `/logout` clears session
  - DoD: After logout, visiting a member-only route redirects to `/login`.

- [ ] **Profile page** — member views and edits name, email, phone, address
  - DoD: `GET /profile` renders `Profile.svelte` with current user data; `PUT /profile` updates and flashes success message.

- [ ] **Change password** — separate form within profile; requires current password
  - DoD: Wrong current password returns validation error; correct input updates hashed password; all other sessions invalidated.

- [ ] **Google OAuth** — "Sign in with Google" button on login and register pages; `google_id` stored on user
  - DoD: Completing OAuth flow creates or links a user account and logs them in; existing email accounts are linked, not duplicated.

---

## Phase 3 — Catalogue Browsing

*Depends on: Phase 1 foundation, Book Detail (Phase 4 T1)*

- [ ] **Book index — data layer** — `BookController@index` queries books with eager-loaded `author`, `genres`, primary `book_image`; supports search, filter, sort, and pagination (15 per page)
  - DoD: `GET /books?search=&genre=&year=&language=&availability=&sort=` returns paginated JSON via Inertia; all filter params are combinable.

- [ ] **Explore page — UI** — paginated book grid with cover image, title, author, availability badge, average rating
  - DoD: Page loads books; search input triggers debounced filter; filter sidebar updates URL params; no full-page reload on filter change (Inertia visit).

- [ ] **Availability badge** — "Available" / "Borrowed" / "On Waitlist" derived from `book.availability` and presence of `waitlist` records
  - DoD: Badge reflects live state; changes after a borrow/return without manual refresh (Inertia reload).

- [ ] **Home page recommendations section** — shows personalised books for logged-in members, popular books for guests (reuses book card component)
  - DoD: Logged-in member sees books by favourite authors or from previously borrowed genres first; guest sees top-rated books.

---

## Phase 4 — Book Detail

- [ ] **Book detail — data layer** — `BookController@show` returns book with `author`, `genres`, `images`, `reviews.user`, average rating, current user's borrowing status and waitlist position
  - DoD: `GET /books/{id}` returns all required props; missing book returns 404.

- [ ] **Book detail — UI** — image gallery, metadata table, author card, genre tags, availability badge with action button (Borrow / Join Waitlist / Renew), reviews section
  - DoD: All data from the data layer renders correctly; image gallery supports multiple images; action button reflects user's current state (no button for guests).

---

## Phase 5 — Borrowing & Returns

*Depends on: Phase 4 complete*

- [ ] **Borrow a book** — `POST /borrowings` creates a `borrowing` record with `due_date = today + 14 days`; sets `book.availability = false`
  - DoD: Only available books can be borrowed; a user cannot borrow the same book twice while it is still checked out; success returns to book detail with updated badge.

- [ ] **Return a book** — `PATCH /borrowings/{id}/return` sets `return_date = today`; sets `book.availability = true`; triggers waitlist notification if queue exists
  - DoD: `return_date` saved; availability updated; member's "My Borrowings" list reflects returned status.

- [ ] **Renew a book** — `PATCH /borrowings/{id}/renew` extends `due_date` by 14 days; blocked if book has a waitlist
  - DoD: Renewal succeeds with no waitlist; returns error with active waitlist; new `due_date` shown to user.

- [ ] **Member borrowing history** — `GET /my/borrowings` lists all borrowings (active and past) with due dates, return dates, overdue flag
  - DoD: Active loans shown first; overdue loans (past `due_date`, no `return_date`) visually highlighted; page accessible to members only.

- [ ] **Staff: record return** — staff can process a return on behalf of any member via admin dashboard
  - DoD: `PATCH /admin/borrowings/{id}/return` works for staff role; triggers same return logic as member self-return.

---

## Phase 6 — Reservations & Waitlists

*Depends on: Phase 5 complete*

- [ ] **Join waitlist** — `POST /waitlists` creates a `waitlist` record with `finish_date = today + 7 days`; blocked if book is available or user already on list
  - DoD: Duplicate join returns validation error; waitlist position shown on book detail page.

- [ ] **Cancel waitlist** — `DELETE /waitlists/{id}` removes user's reservation
  - DoD: Only the owning member or staff can cancel; next member in queue is unaffected.

- [ ] **Member waitlist view** — `GET /my/waitlists` lists all active reservations with book title and position in queue
  - DoD: Queue position calculated by `created_at` order; expired entries (`finish_date` < today) shown as expired.

- [ ] **Staff: waitlist management** — staff views full queue per book; can remove any entry
  - DoD: `GET /admin/books/{id}/waitlists` returns ordered queue; staff delete cascades correctly.

---

## Phase 7 — Reviews & Ratings

*Depends on: Phase 4 complete*

- [ ] **Submit review** — `POST /books/{id}/reviews` creates a `review` record; one per user per book enforced at DB and application level
  - DoD: Duplicate review returns 422; `rate` validated as integer 1–5; `comment` optional; book detail page refreshes average rating after submit.

- [ ] **Edit review** — `PUT /reviews/{id}` updates `rate` and/or `comment`; restricted to review owner
  - DoD: Non-owner returns 403; updated values reflected immediately on book detail.

- [ ] **Delete review** — `DELETE /reviews/{id}` removes record; restricted to owner or staff
  - DoD: Average rating recalculated after deletion; review no longer appears on book detail.

- [ ] **Reviews UI on book detail** — list of reviews with reviewer name, rating stars, comment, date; member's own review shows edit/delete controls
  - DoD: Average displayed as star visual + numeric value; empty state shown when no reviews exist.

---

## Phase 8 — Favourite Authors & Recommendations

*Depends on: Phase 3, Phase 7 complete*

- [ ] **Toggle favourite author** — `POST /authors/{id}/favourite` / `DELETE /authors/{id}/favourite` manages `favourite_authors` pivot
  - DoD: Heart/toggle button on author card and book detail; state persists across page loads.

- [ ] **Recommendation engine** — `RecommendationService` scores books by: (1) books by favourite authors, (2) books in genres of past borrowings/reviews, (3) highest average rating; excludes already-borrowed books
  - DoD: Returns ranked list of up to 10 books; falls back to top-rated books if no history.

- [ ] **Recommendations UI** — dedicated section on home page for logged-in members; separate "Recommended for You" section on Explore page
  - DoD: Section hidden for guests; shows fallback for new members with no borrowing/review history.

---

## Phase 9 — Notifications

*Depends on: Phase 5, Phase 6, Phase 8 complete*

- [ ] **Notification model & migration** — `notifications` table (Laravel built-in `notifications` table or custom); stores `type`, `data`, `read_at`, `user_id`
  - DoD: `php artisan migrate` creates table; `User` model has `Notifiable` trait active.

- [ ] **In-app notification centre** — bell icon in navbar with unread count badge; dropdown lists recent notifications; `POST /notifications/{id}/read` marks as read
  - DoD: Unread count updates on page load; clicking a notification marks it read and navigates to relevant page.

- [ ] **Due date reminder** — scheduled job (daily) finds borrowings where `due_date = today + 3` and `return_date` is null; sends notification to borrower
  - DoD: `php artisan schedule:run` triggers job; notification appears in member's notification centre.

- [ ] **Overdue alert** — scheduled job (daily) finds borrowings where `due_date < today` and `return_date` is null; notifies borrower
  - DoD: Notification sent once per day per overdue loan; not re-sent if already notified today.

- [ ] **Waitlist available notification** — triggered on book return (Phase 5); notifies first member in waitlist queue
  - DoD: Notification created immediately on return; contains book title and link to book detail.

- [ ] **New book by favourite author** — triggered when staff adds a new book; notifies all members who have that `author_id` in `favourite_authors`
  - DoD: Notification dispatched as a queued job; members receive in-app notification.

- [ ] **Email notification channel** — wrap above notifications with `Mail` channel alongside database channel; use Laravel Mailable
  - DoD: Notifications send both in-app and email; email renders correctly with book/due date info.

- [ ] **Notification preferences** — member can toggle email notifications on/off per type in profile settings
  - DoD: Preference stored on `user` or separate `notification_settings` table; email channel skipped when disabled.

---

## Phase 10 — Admin Dashboard

*Depends on: Phase 1 role middleware complete*

- [ ] **Admin layout & route group** — `RouteServiceProvider` adds `/admin` prefix with `staff` role middleware; `Admin/Layout.svelte` with sidebar navigation
  - DoD: `/admin` returns 403 for members and guests; staff see dashboard shell.

- [ ] **Book management — CRUD** — `Admin/BookController` handles list, create, edit, soft-delete; cover image upload stored to `book_image`
  - DoD: Create requires title, author, at least one genre; edit pre-fills form; soft-delete sets `deleted_at` and preserves borrowing history; images uploadable with description.

- [ ] **Author management — CRUD** — list, create, edit, delete authors; photo upload
  - DoD: Author cannot be deleted if books reference it (return 422 with message); photo stored in `storage/app/public`.

- [ ] **Genre management — CRUD** — list, create, edit, delete genres
  - DoD: Genre cannot be deleted if books reference it; name is unique.

- [ ] **User management** — list members; view detail; activate/deactivate (soft-delete); promote to staff
  - DoD: Deactivated users cannot log in; staff cannot deactivate themselves; role change takes effect immediately.

- [ ] **Borrowing records** — paginated list of all active loans with overdue highlight; staff process return inline
  - DoD: Filter by overdue / active / returned; processing return calls same return logic as Phase 5.

- [ ] **Waitlist management** — per-book waitlist view; staff can remove any entry
  - DoD: Ordered by `created_at`; removal triggers notification to next member in queue.

- [ ] **Reports** — three read-only report pages: most borrowed books (last 30 days), overdue summary (count + list), active members (borrowed at least once in last 30 days)
  - DoD: Each report loads data server-side and renders in a table; no external BI tool required.

---

## Phase 11 — QA & Polish

- [ ] **Feature tests — Auth** — register, login, logout, profile update, password change
  - DoD: All tests pass with `php artisan test --testsuite=Feature`.

- [ ] **Feature tests — Borrowing flow** — borrow, return, renew, overdue detection, waitlist join/cancel
  - DoD: Each test uses a real DB transaction rolled back after; no mocking of DB layer.

- [ ] **Feature tests — Reviews** — submit, edit, delete, duplicate prevention
  - DoD: All tests pass; average rating calculation verified.

- [ ] **Feature tests — Admin** — access control (403 for non-staff), book CRUD, return on behalf
  - DoD: Role middleware tested for every admin route group.

- [ ] **Responsive UI audit** — test all pages on mobile (375px), tablet (768px), desktop (1280px)
  - DoD: No horizontal scroll on mobile; navigation collapses to hamburger; book grid reflows correctly.

- [ ] **Accessibility** — form labels, image alt text, keyboard navigation on modal/gallery
  - DoD: Lighthouse accessibility score ≥ 90 on Home, Book Detail, and Explore pages.
