-- =====================================================
-- Digital Notice Board — database.sql
-- Import into phpMyAdmin or run via MySQL CLI:
--   mysql -u root -p < database.sql
-- =====================================================

-- Create and select database
CREATE DATABASE IF NOT EXISTS `digital_notice_board`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `digital_notice_board`;

-- ── admins ──
CREATE TABLE IF NOT EXISTS `admins` (
    `id`            INT          NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(100) NOT NULL,
    `email`         VARCHAR(150) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_admins_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── notices ──
CREATE TABLE IF NOT EXISTS `notices` (
    `id`              INT          NOT NULL AUTO_INCREMENT,
    `title`           VARCHAR(300) NOT NULL,
    `description`     TEXT         NOT NULL,
    `category`        ENUM('general','examination','academic','holiday','placement','event')
                                   NOT NULL DEFAULT 'general',
    `priority`        ENUM('low','normal','high','urgent')
                                   NOT NULL DEFAULT 'normal',
    `attachment_path` VARCHAR(500) NULL     DEFAULT NULL,
    `is_pinned`       TINYINT(1)   NOT NULL DEFAULT 0,
    `status`          ENUM('draft','published','archived')
                                   NOT NULL DEFAULT 'draft',
    `published_at`    DATETIME     NULL     DEFAULT NULL,
    `expires_at`      DATETIME     NULL     DEFAULT NULL,
    `created_by`      INT          NOT NULL,
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notices_status`   (`status`),
    KEY `idx_notices_category` (`category`),
    KEY `idx_notices_priority` (`priority`),
    KEY `idx_notices_published`(`published_at`),
    KEY `idx_notices_pinned`   (`is_pinned`),
    CONSTRAINT `fk_notices_admin`
        FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SAMPLE DATA — remove or replace before production
-- =====================================================

-- Sample admin: email=admin@institution.edu  password=Admin@1234
-- Password hash generated with password_hash('Admin@1234', PASSWORD_BCRYPT)
INSERT IGNORE INTO `admins` (`name`, `email`, `password_hash`) VALUES
(
    'System Administrator',
    'admin@institution.edu',
    '$2y$12$YourBcryptHashHereReplaceWithRealOne0000000000000000000'
);

-- NOTE: The hash above is a placeholder. Run setup_admin.php to create
-- a real admin account with a properly hashed password.

-- Sample notices (created_by = 1 assumes the admin above has id = 1)
INSERT INTO `notices`
    (`title`, `description`, `category`, `priority`, `is_pinned`, `status`, `published_at`, `expires_at`, `created_by`)
VALUES
(
    'Welcome to the Digital Notice Board',
    'This is the official digital notice board for our institution. All academic announcements, examination schedules, events, placements, and holiday information will be published here.\n\nStudents and faculty are advised to check this board regularly for the latest updates.',
    'general', 'normal', 1, 'published', NOW(), NULL, 1
),
(
    'End Semester Examination Schedule — November 2026',
    'The End Semester Examinations for November 2026 will commence from 10th November 2026.\n\nAll students are advised to download their hall tickets from the student portal by 5th November 2026.\n\nExam timings: 9:00 AM to 12:00 PM (Morning Batch) and 2:00 PM to 5:00 PM (Afternoon Batch).\n\nPractical examinations will be held from 20th November to 25th November 2026.',
    'examination', 'urgent', 1, 'published', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 1
),
(
    'Campus Placement Drive — TechCorp Solutions',
    'TechCorp Solutions will be conducting an off-campus placement drive for final year B.Tech students.\n\nEligibility: 60% and above throughout academics. No active backlogs.\nRoles: Software Engineer, Data Analyst\nPackage: 6–9 LPA\n\nRegistration deadline: 1st November 2026\nDrive date: 8th November 2026\n\nRegister through the placement portal.',
    'placement', 'high', 0, 'published', NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY), 1
),
(
    'Diwali Holiday Notice',
    'The institution will remain closed from 30th October to 3rd November 2026 on account of Diwali holidays.\n\nClasses will resume on 4th November 2026 (Monday) as per the regular timetable.',
    'holiday', 'normal', 0, 'published', NOW(), DATE_ADD(NOW(), INTERVAL 10 DAY), 1
),
(
    'Technical Symposium — TECHFEST 2026',
    'The annual technical symposium TECHFEST 2026 is scheduled for 15th and 16th November 2026.\n\nEvents include:\n- Paper Presentation\n- Code Sprint\n- Robotics Competition\n- UI/UX Design Challenge\n- Hackathon (24 hours)\n\nRegistrations are open. Teams of 2–4 members allowed. Cash prizes worth ₹50,000.\n\nFor registration and details, contact the student union or visit the college website.',
    'event', 'high', 0, 'published', NOW(), DATE_ADD(NOW(), INTERVAL 20 DAY), 1
),
(
    'Academic Calendar Update — Odd Semester 2026–27',
    'The revised academic calendar for Odd Semester 2026–27 has been released.\n\nKey dates:\n- Last working day: 8th November 2026\n- Study leave: 9th–19th November 2026\n- End Semester Exams: 20th November – 5th December 2026\n- Result declaration: 15th December 2026\n\nStudents are advised to plan accordingly.',
    'academic', 'normal', 0, 'published', NOW(), NULL, 1
);
