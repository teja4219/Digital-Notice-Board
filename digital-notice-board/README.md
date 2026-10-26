# Digital Notice Board for Institutions

A complete, professional, and secure web application for managing institutional notices digitally. Built with **PHP 8+**, **MySQL**, **Vanilla JavaScript**, and **custom CSS** — no frontend frameworks required.

---

## 📋 Project Abstract

The Digital Notice Board for Institutions is a centralized web-based platform that replaces traditional physical notice boards with a modern, accessible, and manageable digital system. It enables authorized administrators to publish, update, and manage institutional announcements in real time, while students and faculty can view categorized notices from any device without requiring an account.

---

## ❓ Problem Statement

Traditional physical notice boards suffer from:
- Limited visibility (only accessible on-campus)
- Notices getting damaged, removed, or missed
- No search or filtering capability
- No notification of urgency or expiry
- Manual and time-consuming notice management

---

## 🎯 Objectives

1. Provide a centralized, digital platform for all institutional announcements.
2. Allow administrators to securely create, edit, publish, pin, and delete notices.
3. Allow students to view, search, and filter notices from any device.
4. Support attachments (PDF documents) with notices.
5. Implement role-based access control and security best practices.
6. Deliver a professional, responsive UI for both public and admin interfaces.

---

## 💡 Proposed System

The proposed system consists of:

- **Public Notice Board**: Accessible without login; shows published, non-expired notices with search and category filters.
- **Admin Panel**: Secure dashboard for authorized administrators to manage all notices.
- **Database Backend**: MySQL stores all notices, admin accounts, and metadata.
- **Security Layer**: CSRF protection, prepared statements, session management, and secure file uploads.

---

## 🏗️ System Architecture

```
Browser (Student / Public)
        │
        ▼
   index.php / notice.php
        │
        ▼
   includes/ (auth, csrf, functions)
        │
        ▼
   config/database.php (PDO)
        │
        ▼
   MySQL — digital_notice_board
```

```
Browser (Admin)
        │
        ▼
   login.php ──► Session
        │
        ▼
   admin/ (dashboard, add, edit, manage, delete)
        │
        ▼
   PDO + MySQL
```

---

## 🗄️ Database Schema

### `admins`
| Column         | Type         | Description              |
|----------------|--------------|--------------------------|
| id             | INT PK       | Auto-increment           |
| name           | VARCHAR(100) | Admin full name          |
| email          | VARCHAR(150) | Unique login email       |
| password_hash  | VARCHAR(255) | bcrypt hash              |
| created_at     | TIMESTAMP    | Account creation time    |

### `notices`
| Column          | Type              | Description                      |
|-----------------|-------------------|----------------------------------|
| id              | INT PK            | Auto-increment                   |
| title           | VARCHAR(300)      | Notice title                     |
| description     | TEXT              | Full notice content              |
| category        | ENUM              | general/examination/academic/... |
| priority        | ENUM              | low/normal/high/urgent           |
| attachment_path | VARCHAR(500) NULL | Uploaded PDF filename            |
| is_pinned       | TINYINT(1)        | Pinned to top: 1 = yes           |
| status          | ENUM              | draft/published/archived         |
| published_at    | DATETIME NULL     | When notice goes live            |
| expires_at      | DATETIME NULL     | When notice expires              |
| created_by      | INT FK → admins   | Admin who created it             |
| created_at      | TIMESTAMP         | Creation time                    |
| updated_at      | TIMESTAMP         | Last update time                 |

---

## 📁 Project Folder Structure

```
digital-notice-board/
├── index.php                  # Public notice board
├── notice.php                 # Single notice detail
├── login.php                  # Admin login
├── logout.php                 # Session destroy
├── setup_admin.php            # One-time admin creator (delete after use!)
├── database.sql               # Full schema + sample data
├── admin/
│   ├── dashboard.php          # Stats + recent notices
│   ├── add-notice.php         # Create notice
│   ├── edit-notice.php        # Edit notice
│   ├── manage-notices.php     # CRUD list with filters
│   └── delete-notice.php      # POST-only delete/toggle handler
├── config/
│   └── database.php           # PDO connection (credentials here)
├── includes/
│   ├── auth.php               # Session auth guard
│   ├── csrf.php               # CSRF token generation/validation
│   ├── functions.php          # Shared helpers
│   ├── header.php             # HTML head + nav
│   └── footer.php             # Footer HTML
├── assets/
│   ├── css/style.css          # All custom CSS (no frameworks)
│   ├── js/script.js           # Vanilla JavaScript
│   └── images/                # Logo / assets
├── uploads/                   # PDF attachments (PHP execution blocked)
│   ├── .htaccess              # Security rules
│   └── .gitkeep
├── .gitignore
└── README.md                  ← You are here
```

---

## 🖥️ Module Descriptions

### 1. Public Notice Board (`index.php`)
- Lists all published, non-expired notices
- Search by title/keyword
- Filter by category (General, Examination, Academic, Holiday, Placement, Event)
- Paginated notice cards with priority badges
- Pinned notices appear at the top

### 2. Notice Detail (`notice.php`)
- Full notice content display
- Publication date and expiry date
- PDF attachment download button
- Edit link visible to logged-in admins

### 3. Admin Login (`login.php`)
- Email + password authentication
- bcrypt password verification
- CSRF token protection
- Session ID regeneration after login
- 1-second delay to slow brute-force attacks

### 4. Admin Dashboard (`admin/dashboard.php`)
- Stats: Total, Published, Expired, Urgent notices
- Quick action buttons
- Recent notices table with edit/delete

### 5. Add Notice (`admin/add-notice.php`)
- Full notice creation form
- Server-side validation
- PDF upload (5 MB max, type-validated)
- CSRF protection on submission

### 6. Edit Notice (`admin/edit-notice.php`)
- Pre-populated form from database
- Replace or remove existing attachment
- CSRF protection

### 7. Manage Notices (`admin/manage-notices.php`)
- Full paginated table of all notices
- Search, category, and status filters
- Quick publish/unpublish toggle
- Delete with confirmation modal

### 8. Delete/Toggle Handler (`admin/delete-notice.php`)
- POST-only endpoint
- Handles delete and status toggle
- Deletes associated file on notice deletion
- CSRF validated

---

## 🔐 Security Implementation

| Feature                        | Implementation                                    |
|--------------------------------|---------------------------------------------------|
| SQL Injection Prevention       | PDO prepared statements throughout                |
| Password Storage               | `password_hash()` with bcrypt (cost=12)           |
| Authentication                 | PHP session with `requireAuth()` guard            |
| Session Fixation               | `session_regenerate_id(true)` after login         |
| CSRF Protection                | Token in all state-changing forms                 |
| XSS Prevention                 | `htmlspecialchars()` on all output                |
| File Upload Security           | MIME type check, extension whitelist, 5MB limit   |
| Upload Execution Prevention    | `.htaccess` blocks PHP in uploads/                |
| Sensitive File Exposure        | `database.php` in `.gitignore`                    |
| Brute Force Mitigation         | 1-second delay on failed login                    |

---

## 🚀 Local Setup with XAMPP

### Step 1 — Install XAMPP
Download from [https://www.apachefriends.org](https://www.apachefriends.org) and install. Choose Apache and MySQL components.

### Step 2 — Start Services
Open XAMPP Control Panel → Start **Apache** and **MySQL**.

### Step 3 — Place the Project
Copy the `digital-notice-board/` folder into:
```
C:\xampp\htdocs\
```
Result: `C:\xampp\htdocs\digital-notice-board\`

### Step 4 — Create the Database
1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click **Import** tab
3. Choose the file: `digital-notice-board/database.sql`
4. Click **Go**

### Step 5 — Configure Database Credentials
Open `config/database.php` and verify:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'digital_notice_board');
define('DB_USER', 'root');
define('DB_PASS', '');   // XAMPP default is empty password
```

### Step 6 — Create the Administrator Account
⚠️ The sample SQL contains a placeholder hash. Create a real account:

1. Open: [http://localhost/digital-notice-board/setup_admin.php](http://localhost/digital-notice-board/setup_admin.php)
2. Fill in your name, email, and password
3. Click **Create Admin Account**
4. **Delete `setup_admin.php` immediately!**

### Step 7 — Open the Application
- **Public Board**: [http://localhost/digital-notice-board/](http://localhost/digital-notice-board/)
- **Admin Login**: [http://localhost/digital-notice-board/login.php](http://localhost/digital-notice-board/login.php)

---

## ✅ Testing Checklist

- [ ] Public notice board loads and displays notices
- [ ] Search by keyword works
- [ ] Category filter buttons work
- [ ] Pagination works
- [ ] Single notice detail page opens correctly
- [ ] PDF attachment download works
- [ ] Login with valid credentials succeeds
- [ ] Login with invalid credentials shows error
- [ ] Admin dashboard shows correct statistics
- [ ] Create a new notice (Draft and Published)
- [ ] Edit an existing notice
- [ ] Upload PDF attachment
- [ ] Replace/remove attachment
- [ ] Toggle publish/unpublish
- [ ] Pin a notice (appears at the top of public board)
- [ ] Delete a notice with confirmation modal
- [ ] Expired notice is hidden from public board
- [ ] Accessing admin pages without login redirects to login page
- [ ] CSRF token mismatch is rejected
- [ ] Invalid file type upload is rejected
- [ ] Search and filter in Manage Notices
- [ ] Responsive layout on mobile (375px width)
- [ ] Flash messages appear and auto-dismiss

---

## 🌐 GitHub Setup

```bash
# 1. Initialize repository (inside project folder)
git init

# 2. Add all files
git add .

# 3. First commit
git commit -m "Initial commit: Digital Notice Board for Institutions"

# 4. Create a repository on github.com, then link it
git remote add origin https://github.com/YOUR_USERNAME/digital-notice-board.git
git branch -M main
git push -u origin main
```

> **Important**: `config/database.php` is in `.gitignore` — never commit credentials to GitHub.
> Create a `config/database.example.php` to share the structure without credentials.

---

## ☁️ Hosting Deployment

### Requirements
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `.htaccess` support (mod_rewrite)

### Recommended Free/Paid Hosts
- **InfinityFree** (free, PHP + MySQL) — [https://infinityfree.net](https://infinityfree.net)
- **000WebHost** (free tier) — [https://www.000webhost.com](https://www.000webhost.com)
- **Hostinger** (paid, reliable) — [https://www.hostinger.in](https://www.hostinger.in)
- **Railway** (for advanced deployment)

> ❌ **GitHub Pages does NOT support PHP**. Do not use it to host this application.

### Deployment Steps
1. Create a MySQL database on your host and note credentials
2. Import `database.sql` via phpMyAdmin on the host
3. Update `config/database.php` with production credentials
4. Upload all files via FTP/SFTP to the public web root (e.g., `public_html/`)
5. Set `uploads/` permissions to `755`
6. Open `yourdomain.com/setup_admin.php` to create admin, then **delete it**
7. Enable HTTPS (free SSL via Let's Encrypt on most hosts)
8. Test all features on the live URL

### Production `config/database.php`
Use environment variables on production:
```php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'your_db_name');
define('DB_USER', getenv('DB_USER') ?: 'your_db_user');
define('DB_PASS', getenv('DB_PASS') ?: 'your_db_pass');
```

---

## 🚀 Future Enhancements

1. **Email Notifications** — Notify students when urgent notices are published (PHPMailer)
2. **Student Registration/Login** — Allow students to bookmark notices
3. **Notice Approval Workflow** — Multi-level admin approval
4. **PDF Preview** — In-browser PDF viewer (PDF.js)
5. **Push Notifications** — Web push for urgent announcements
6. **Analytics Dashboard** — Notice view counts and trends
7. **Multi-language Support** — i18n for regional languages
8. **REST API** — JSON endpoints for mobile apps
9. **Bulk Operations** — Select and delete/publish multiple notices
10. **Audit Log** — Track all admin actions with timestamps

---

## 🎤 Presentation Script

> "Good morning/afternoon. I'd like to present my lab project — the Digital Notice Board for Institutions.
>
> The problem we're solving: traditional physical notice boards are limited in reach, hard to manage, and easy to miss. Our solution is a full-stack web application.
>
> **Technology Stack**: The frontend uses plain HTML5, CSS3, and Vanilla JavaScript — no external UI frameworks. The backend is PHP 8 with PDO for secure database access. The database is MySQL. Development was done using XAMPP locally.
>
> **Key Features**:
> - Students can browse, search, and filter notices by category — no account needed.
> - Administrators log in securely to create, edit, pin, publish, and delete notices.
> - PDF attachments can be uploaded and downloaded.
> - Notices can be set to expire automatically.
>
> **Security**: We use prepared statements to prevent SQL injection, bcrypt for passwords, CSRF tokens on every form, and .htaccess to prevent uploaded files from being executed.
>
> Let me demonstrate the application — starting with the public board, then logging in as admin to create and manage a notice."

---

## 👨‍💻 Author

**B.Tech Computer Science Engineering**  
Full Stack Development Laboratory Project  
Academic Year 2026–27

---

## 📄 License

This project is created for educational purposes as a B.Tech laboratory project. Free to use and modify for academic purposes.
