# TASKS.md — RakTrek

Tasks are ordered by dependency. Complete phases in sequence; tasks within a phase can be parallelised unless noted.
Each implementation task carries its own test task. Run all tests with `php artisan test`.

Legend: ✅ = Done, 🔲 = Pending

---

## Phase 1 — Foundation

- [x] **Database migrations** — all tables created (`user`, `book`, `author`, `genre`, `book_genres`, `favourite_authors`, `borrowing`, `waitlist`, `review`, `book_image`)
  - DoD: `php artisan migrate` runs clean; all tables exist in DB.

- [x] **Eloquent models** — `User`, `Book`, `Author`, `Genre`, `Borrowing`, `WaitingList`, `Review`, `BookImage` with correct relationships
  - DoD: Relationships (`belongsTo`, `hasMany`, `belongsToMany`) return correct results via Tinker.
  - [x] **Test (Unit):** `tests/Unit/Models/` — assert each relationship method returns the correct related class; assert `Book` and `User` use `SoftDeletes`; assert `Book::genres()` uses `book_genre` pivot table.

- [x] **Inertia + Svelte wiring** — Laravel sends pages via `Inertia::render()`, Svelte receives props
  - DoD: A page renders without JS errors; shared props (`user`, `currentRouteName`, `appName`) accessible in all Svelte pages.
  - [x] **Test (Feature):** `tests/Feature/InertiaSharedPropsTest.php` — assert `GET /` response contains `appName`, `currentRouteName`, and `user` keys in Inertia shared data.

- [x] **Role middleware** — `EnsureRole` middleware to gate routes by `role` field (`member`, `staff`)
  - DoD: Accessing a `staff`-only route as a `member` returns 403; as `staff` passes through.
  - [x] **Test (Feature):** `tests/Feature/Middleware/EnsureRoleTest.php` — assert guest returns 302 to login; member on staff route returns 403; staff on staff route returns 200.

- [x] **Staff seeder** — seed one default staff account for local development
  - DoD: `php artisan db:seed` creates a staff user; login works with seeded credentials.

---

## Phase 2 — Authentication

- [x] **Register** — form with name, email, phone, address, password, password confirmation
  - DoD: Submitting valid data creates a `user` record with `role = member`; user is logged in and redirected to home.
  - [x] **Test (Feature):** `tests/Feature/Auth/RegisterTest.php` — assert valid payload creates user with `role = member` and redirects to `/`; assert missing name/email/password returns 422; assert duplicate email returns 422; assert password mismatch returns 422.

- [x] **Login** — email + password form, "remember me" option
  - DoD: Valid credentials log user in; invalid credentials return translated error from `auth.failed`; guests on auth pages redirect to home if already logged in.
  - [x] **Test (Feature):** `tests/Feature/Auth/LoginTest.php` — assert valid credentials authenticate and redirect to `/`; assert wrong password returns error bag with `auth.failed`; assert authenticated user visiting `/login` redirects to `/`.

- [x] **Logout** — POST `/logout` clears session
  - DoD: After logout, visiting a member-only route redirects to `/login`.
  - [x] **Test (Feature):** `tests/Feature/Auth/LogoutTest.php` — assert `POST /logout` as authenticated user returns redirect to `/`; assert subsequent `GET /my/borrowings` (member-only) redirects to `/login`.

- [x] **Profile page** — member views and edits name, email, phone, address
  - DoD: `GET /profile` renders `Profile.svelte` with current user data; `PUT /profile` updates and flashes success message.
  - [x] **Test (Feature):** `tests/Feature/Auth/ProfileTest.php` — assert `GET /profile` as member returns Inertia page with user props; assert `PUT /profile` with valid data updates DB record; assert guest is redirected; assert email uniqueness validated on update.

- [x] **Change password** — separate form within profile; requires current password
  - DoD: Wrong current password returns validation error; correct input updates hashed password; all other sessions invalidated.
  - [x] **Test (Feature):** `tests/Feature/Auth/ChangePasswordTest.php` — assert wrong current password returns validation error; assert correct current password hashes and stores new password; assert login with old password fails after change.

- [x] **Google OAuth** — "Sign in with Google" button on login and register pages; `google_id` stored on user
  - DoD: Completing OAuth flow creates or links a user account and logs them in; existing email accounts are linked, not duplicated.
  - [x] **Test (Feature):** `tests/Feature/Auth/GoogleOAuthTest.php` — mock Socialite; assert callback with new email creates user with `google_id`; assert callback with existing email links `google_id` without creating duplicate; assert user is authenticated after callback.

---

## Phase 3 — Catalogue Browsing

*Depends on: Phase 1 foundation, Phase 4 Book Detail data layer*

- [x] **Book index — data layer** — `BookController@index` queries books with eager-loaded `author`, `genres`, primary `book_image`; supports search, filter, sort, and pagination (15 per page)
  - DoD: `GET /books?search=&genre=&year=&language=&availability=&sort=` returns paginated JSON via Inertia; all filter params are combinable.
  - [x] **Test (Feature):** `tests/Feature/Book/BookIndexTest.php` — assert `GET /books` returns paginated list (15 per page); assert `?search=title` filters by title; assert `?genre=1` filters by genre; assert `?availability=1` filters available-only; assert eager-loaded relations present in response; assert `?sort=rating` orders correctly.

- [x] **Explore page — UI** — paginated book grid with cover image, title, author, availability badge, average rating
  - DoD: Page loads books; search input triggers debounced filter; filter sidebar updates URL params; no full-page reload on filter change (Inertia visit).

- [x] **Availability badge** — "Available" / "Borrowed" / "On Waitlist" derived from `book.availability` and presence of `waitlist` records
  - DoD: Badge reflects live state; changes after a borrow/return without manual refresh (Inertia reload).
  - [x] **Test (Unit):** `tests/Unit/Models/BookAvailabilityTest.php` — assert `availability = true` with no waitlist yields "Available"; assert `availability = false` with no waitlist yields "Borrowed"; assert `availability = false` with active waitlist yields "On Waitlist".

- [x] **Home page recommendations section** — shows personalised books for logged-in members, popular books for guests (reuses book card component)
  - DoD: Logged-in member sees books by favourite authors or from previously borrowed genres first; guest sees top-rated books.
  - [x] **Test (Feature):** `tests/Feature/HomeTest.php` — assert `GET /` as guest contains top-rated books in props; assert as member with favourite authors, those authors' books appear in recommendations prop.

---

## Phase 4 — Book Detail

- [x] **Book detail — data layer** — `BookController@show` returns book with `author`, `genres`, `images`, `reviews.user`, average rating, current user's borrowing status and waitlist position
  - DoD: `GET /books/{id}` returns all required props; missing book returns 404.
  - [x] **Test (Feature):** `tests/Feature/Book/BookShowTest.php` — assert `GET /books/{id}` returns Inertia page with `author`, `genres`, `images`, `reviews`, `averageRating`, `userBorrowing`, `userWaitlistPosition` props; assert `GET /books/9999` returns 404; assert soft-deleted book returns 404.

- [x] **Book detail — UI** — image gallery, metadata table, author card, genre tags, availability badge with action button (Borrow / Join Waitlist / Renew), reviews section
  - DoD: All data from the data layer renders correctly; image gallery supports multiple images; action button reflects user's current state (no button for guests).

---

## Phase 5 — Borrowing & Returns

*Depends on: Phase 4 complete*

- [x] **Borrow a book** — `POST /borrowings` creates a `borrowing` record with `due_date = today + 14 days`; sets `book.availability = false`
  - DoD: Only available books can be borrowed; a user cannot borrow the same book twice while it is still checked out; success returns to book detail with updated badge.
  - [x] **Test (Feature):** `tests/Feature/Borrowing/BorrowTest.php` — assert `POST /borrowings` creates record with correct `due_date`; assert `book.availability` set to false; assert borrowing unavailable book returns 422; assert borrowing already-checked-out book returns 422; assert guest is redirected to login.

- [x] **Return a book** — `PATCH /borrowings/{id}/return` sets `return_date = today`; sets `book.availability = true`; triggers waitlist notification if queue exists
  - DoD: `return_date` saved; availability updated; member's "My Borrowings" list reflects returned status.
  - [x] **Test (Feature):** `tests/Feature/Borrowing/ReturnTest.php` — assert `PATCH /borrowings/{id}/return` sets `return_date` to today; assert `book.availability` set to true; assert non-owner returns 403; assert already-returned borrowing returns 422; assert notification dispatched when waitlist exists (use `Notification::fake()`).

- [x] **Renew a book** — `PATCH /borrowings/{id}/renew` extends `due_date` by 14 days; blocked if book has a waitlist
  - DoD: Renewal succeeds with no waitlist; returns error with active waitlist; new `due_date` shown to user.
  - [x] **Test (Feature):** `tests/Feature/Borrowing/RenewTest.php` — assert `PATCH /borrowings/{id}/renew` extends `due_date` by 14 days when no waitlist; assert returns 422 with active waitlist; assert non-owner returns 403.

- [x] **Member borrowing history** — `GET /my/borrowings` lists all borrowings (active and past) with due dates, return dates, overdue flag
  - DoD: Active loans shown first; overdue loans (past `due_date`, no `return_date`) visually highlighted; page accessible to members only.
  - [x] **Test (Feature):** `tests/Feature/Borrowing/BorrowingHistoryTest.php` — assert `GET /my/borrowings` returns only the authenticated member's records; assert overdue loans include an `is_overdue` flag set to true; assert guest redirects to login.

- [x] **Staff: record return** — staff can process a return on behalf of any member via admin dashboard
  - DoD: `PATCH /admin/borrowings/{id}/return` works for staff role; triggers same return logic as member self-return.
  - [x] **Test (Feature):** `tests/Feature/Admin/AdminReturnTest.php` — assert staff can return any member's borrowing; assert member role on this route returns 403; assert same side effects (availability, notification) as self-return.

---

## Phase 6 — Reservations & Waitlists

*Depends on: Phase 5 complete*

- [x] **Join waitlist** — `POST /waitlists` creates a `waitlist` record with `finish_date = today + 7 days`; blocked if book is available or user already on list
  - DoD: Duplicate join returns validation error; waitlist position shown on book detail page.
  - [x] **Test (Feature):** `tests/Feature/Waitlist/JoinWaitlistTest.php` — assert `POST /waitlists` creates record with correct `finish_date`; assert joining for available book returns 422; assert duplicate join returns 422; assert guest redirects to login.

- [x] **Cancel waitlist** — `DELETE /waitlists/{id}` removes user's reservation
  - DoD: Only the owning member or staff can cancel; next member in queue is unaffected.
  - [x] **Test (Feature):** `tests/Feature/Waitlist/CancelWaitlistTest.php` — assert owner can delete own waitlist entry; assert non-owner member returns 403; assert staff can delete any entry; assert other queue positions are unaffected after deletion.

- [x] **Member waitlist view** — `GET /my/waitlists` lists all active reservations with book title and position in queue
  - DoD: Queue position calculated by `created_at` order; expired entries (`finish_date` < today) shown as expired.
  - [x] **Test (Feature):** `tests/Feature/Waitlist/MemberWaitlistTest.php` — assert `GET /my/waitlists` returns only current member's entries; assert queue position is correct relative to other entries for same book; assert expired entries have `is_expired = true`.

- [x] **Staff: waitlist management** — staff views full queue per book; can remove any entry
  - DoD: `GET /admin/books/{id}/waitlists` returns ordered queue; staff delete cascades correctly.
  - [x] **Test (Feature):** `tests/Feature/Admin/AdminWaitlistTest.php` — assert staff sees all waitlist entries for a book ordered by `created_at`; assert `DELETE /admin/waitlists/{id}` removes entry; assert member on this route returns 403.

---

## Phase 7 — Reviews & Ratings

*Depends on: Phase 4 complete*

- [x] **Submit review** — `POST /books/{id}/reviews` creates a `review` record; one per user per book enforced at DB and application level
  - DoD: Duplicate review returns 422; `rate` validated as integer 1–5; `comment` optional; book detail page refreshes average rating after submit.
  - [x] **Test (Feature):** `tests/Feature/Review/SubmitReviewTest.php` — assert valid payload creates review; assert `rate = 0` or `rate = 6` returns 422; assert duplicate review returns 422; assert guest redirects to login; assert book's average rating is recalculated after submission.

- [x] **Edit review** — `PUT /reviews/{id}` updates `rate` and/or `comment`; restricted to review owner
  - DoD: Non-owner returns 403; updated values reflected immediately on book detail.
  - [x] **Test (Feature):** `tests/Feature/Review/EditReviewTest.php` — assert owner can update `rate` and `comment`; assert non-owner returns 403; assert updated `rate` changes book average rating.

- [x] **Delete review** — `DELETE /reviews/{id}` removes record; restricted to owner or staff
  - DoD: Average rating recalculated after deletion; review no longer appears on book detail.
  - [x] **Test (Feature):** `tests/Feature/Review/DeleteReviewTest.php` — assert owner can delete own review; assert staff can delete any review; assert non-owner member returns 403; assert book average rating recalculated after deletion.

- [x] **Reviews UI on book detail** — list of reviews with reviewer name, rating stars, comment, date; member's own review shows edit/delete controls
  - DoD: Average displayed as star visual + numeric value; empty state shown when no reviews exist.

---

## Phase 8 — Favourite Authors & Recommendations

*Depends on: Phase 3, Phase 7 complete*

- [x] **Toggle favourite author** — `POST /authors/{id}/favourite` / `DELETE /authors/{id}/favourite` manages `favourite_authors` pivot
  - DoD: Heart/toggle button on author card and book detail; state persists across page loads.
  - [x] **Test (Feature):** `tests/Feature/Author/FavouriteAuthorTest.php` — assert `POST /authors/{id}/favourite` inserts pivot record; assert duplicate insert is idempotent (no duplicate row); assert `DELETE /authors/{id}/favourite` removes pivot record; assert guest redirects to login.

- [x] **Recommendation engine** — `RecommendationService` scores books by: (1) books by favourite authors, (2) books in genres of past borrowings/reviews, (3) highest average rating; excludes already-borrowed books
  - DoD: Returns ranked list of up to 10 books; falls back to top-rated books if no history.
  - [x] **Test (Unit):** `tests/Unit/Services/RecommendationServiceTest.php` — assert books by favourite authors ranked first; assert already-borrowed books excluded; assert books in preferred genres rank above unrelated books; assert new member with no history receives top-rated fallback list; assert result count ≤ 10.

- [x] **Recommendations UI** — dedicated section on home page for logged-in members; separate "Recommended for You" section on Explore page
  - DoD: Section hidden for guests; shows fallback for new members with no borrowing/review history.
  - [x] **Test (Feature):** `tests/Feature/RecommendationTest.php` — assert `GET /` as member contains `recommendations` prop with books; assert `GET /` as guest does not contain personalised recommendations prop (or contains popular fallback).

---

## Phase 9 — Notifications

*Depends on: Phase 5, Phase 6, Phase 8 complete*

- [x] **Notification model & migration** — `notifications` table via `php artisan notifications:table`; `User` model uses `Notifiable` trait
  - DoD: `php artisan migrate` creates table; notification can be stored and retrieved via `$user->notifications`.
  - [x] **Test (Unit):** `tests/Unit/Models/UserNotifiableTest.php` — assert `User` has `notifications` relationship returning `DatabaseNotification` instances; assert `unreadNotifications` scope filters correctly.

- [x] **In-app notification centre** — bell icon in navbar with unread count badge; `GET /notifications` lists recent; `POST /notifications/{id}/read` marks as read
  - DoD: Unread count updates on page load; clicking a notification marks it read and navigates to relevant page.
  - [x] **Test (Feature):** `tests/Feature/Notification/NotificationCentreTest.php` — assert `GET /notifications` returns member's notifications; assert `POST /notifications/{id}/read` sets `read_at`; assert non-owner cannot mark another user's notification as read; assert guest redirects to login.

- [x] **Due date reminder** — scheduled command finds borrowings where `due_date = today + 3` and `return_date` is null; sends `DueDateReminderNotification`
  - DoD: `php artisan schedule:run` triggers command; notification stored for borrower.
  - [x] **Test (Unit):** `tests/Unit/Console/DueDateReminderTest.php` — use `Notification::fake()`; seed borrowing due in 3 days; run command; assert `DueDateReminderNotification` sent to correct user; assert borrowing not due in 3 days does not trigger notification.

- [x] **Overdue alert** — scheduled command finds borrowings where `due_date < today` and `return_date` is null; sends `OverdueAlertNotification` once per day
  - DoD: Notification sent once per day per overdue loan; not re-sent if already notified today.
  - [x] **Test (Unit):** `tests/Unit/Console/OverdueAlertTest.php` — assert notification sent for overdue borrowing; assert notification not re-sent if `OverdueAlertNotification` already exists for that borrowing today.

- [x] **Waitlist available notification** — triggered on book return; notifies first member in waitlist queue via `BookAvailableNotification`
  - DoD: Notification created immediately on return; contains book title and link to book detail.
  - [x] **Test (Feature):** Add to `tests/Feature/Borrowing/ReturnTest.php` — use `Notification::fake()`; seed a waitlist with two members; return the book; assert `BookAvailableNotification` sent only to first-in-queue member.

- [x] **New book by favourite author** — triggered when staff creates a book; dispatches `NewBookByFavouriteAuthorNotification` as queued job to all members who favourited the author
  - DoD: Notification dispatched as queued job; members receive in-app notification.
  - [x] **Test (Feature):** `tests/Feature/Notification/NewBookNotificationTest.php` — use `Notification::fake()` and `Queue::fake()`; staff creates book; assert notification dispatched for all users who favourited the author; assert user who did not favourite the author receives nothing.

- [x] **Email notification channel** — wrap notifications with `Mail` channel alongside `database` channel; create Mailables per notification type
  - DoD: Notifications send both in-app and email; email renders with correct book/due date data.
  - [x] **Test (Unit):** `tests/Unit/Notifications/` — for each Mailable, assert `assertSeeInText` contains expected content (book title, due date); assert `via()` returns both `database` and `mail` channels.

- [x] **Notification preferences** — member toggles email notifications per type in profile settings; preference persisted on `user` table or `notification_settings`
  - DoD: Email channel skipped when preference disabled for that notification type.
  - [x] **Test (Feature):** `tests/Feature/Notification/NotificationPreferenceTest.php` — assert `PUT /profile/notification-preferences` saves preference; assert when email disabled for a type, notification `via()` returns only `database`; assert `GET /profile` includes current preferences in props.

---

## Phase 10 — Admin Dashboard

*Depends on: Phase 1 role middleware complete*

- [x] **Admin layout & route group** — `/admin` prefix with `staff` middleware; `Admin/Layout.svelte` with sidebar navigation
  - DoD: `/admin` returns 403 for members and guests; staff see dashboard shell.
  - [x] **Test (Feature):** `tests/Feature/Admin/AdminAccessTest.php` — assert `GET /admin` as guest redirects to login; assert as member returns 403; assert as staff returns 200.

- [x] **Book management — CRUD** — list, create, edit, soft-delete books; cover image upload to `book_image`
  - DoD: Create requires title, author, at least one genre; edit pre-fills form; soft-delete preserves borrowing history; images uploadable with description.
  - [x] **Test (Feature):** `tests/Feature/Admin/AdminBookTest.php` — assert `POST /admin/books` creates book with images and genres; assert missing title returns 422; assert `DELETE /admin/books/{id}` soft-deletes (sets `deleted_at`); assert borrowing history preserved after soft-delete; assert member on any route returns 403.

- [x] **Author management — CRUD** — list, create, edit, delete authors; photo upload
  - DoD: Author with referenced books returns 422 on delete; photo stored in `storage/app/public`.
  - [x] **Test (Feature):** `tests/Feature/Admin/AdminAuthorTest.php` — assert create stores author with photo path; assert delete with referenced books returns 422; assert delete with no books succeeds.

- [x] **Genre management — CRUD** — list, create, edit, delete genres; name unique
  - DoD: Genre with referenced books returns 422 on delete; name is unique.
  - [x] **Test (Feature):** `tests/Feature/Admin/AdminGenreTest.php` — assert create enforces unique name (duplicate returns 422); assert delete with referenced books returns 422; assert delete with no books succeeds.

- [x] **User management** — list members; activate/deactivate (soft-delete); promote to staff
  - DoD: Deactivated users cannot log in; staff cannot deactivate themselves; role change takes effect immediately.
  - [x] **Test (Feature):** `tests/Feature/Admin/AdminUserTest.php` — assert deactivated user (`deleted_at` set) cannot log in; assert staff attempting to deactivate themselves returns 422; assert `PATCH /admin/users/{id}/role` changes role; assert deactivated user's session is invalidated.

- [x] **Borrowing records** — paginated list of all loans with overdue highlight; staff processes return inline
  - DoD: Filter by overdue / active / returned; return triggers same logic as Phase 5.
  - [x] **Test (Feature):** Add filter assertions to `tests/Feature/Admin/AdminReturnTest.php` — assert `GET /admin/borrowings?filter=overdue` returns only overdue records; assert `?filter=active` returns only active loans.

- [x] **Waitlist management** — per-book waitlist view ordered by `created_at`; staff can remove any entry
  - DoD: Removal triggers notification to next member in queue.
  - [x] **Test (Feature):** Already covered in `tests/Feature/Admin/AdminWaitlistTest.php` — add assertion that removing first-in-queue triggers `BookAvailableNotification` to next member.

- [x] **Reports** — three pages: most borrowed (30 days), overdue summary, active members (30 days)
  - DoD: Data loaded server-side; renders in table; no external BI tool.
  - [x] **Test (Feature):** `tests/Feature/Admin/AdminReportTest.php` — assert most-borrowed report returns books ordered by borrow count descending; assert overdue summary count matches actual overdue borrowings; assert active members count matches members with at least one borrowing in last 30 days.

---

## Phase 11 — QA & Polish

- [ ] **Responsive UI audit** — test all pages on mobile (375px), tablet (768px), desktop (1280px)
  - DoD: No horizontal scroll on mobile; navigation collapses to hamburger; book grid reflows correctly.

- [ ] **Accessibility** — form labels, image alt text, keyboard navigation on modal/gallery
  - DoD: Lighthouse accessibility score ≥ 90 on Home, Book Detail, and Explore pages.

- [ ] **N+1 query audit** — enable query logging in tests; assert no route executes more than a set query threshold
  - DoD: `GET /books`, `GET /books/{id}`, `GET /my/borrowings` each execute ≤ 5 queries (eager loading verified).

- [ ] **Test coverage gate** — ensure all feature and unit tests pass on CI
  - DoD: `php artisan test` exits 0; no skipped tests without explicit annotation.
