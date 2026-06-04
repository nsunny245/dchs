<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DCHS – Project Status Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; color: #1a1a2e; font-size: 11px; line-height: 1.5; }
        .page-border { border: 3px double #0f3460; padding: 22px 30px; min-height: 96vh; position: relative; }

        /* Cover Header */
        .cover-header { text-align: center; padding: 30px 0 20px; border-bottom: 3px solid #0f3460; margin-bottom: 20px; }
        .cover-header h1 { font-size: 28px; color: #0f3460; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 4px; }
        .cover-header .subtitle { font-size: 13px; color: #e94560; font-weight: bold; letter-spacing: 1px; margin-top: 4px; }
        .cover-header .tagline { font-size: 11px; color: #666; margin-top: 2px; }

        /* Report Meta */
        .report-meta { background: #f0f4ff; border: 1px solid #d0d9f0; padding: 10px 14px; margin-bottom: 18px; font-size: 10.5px; }
        .report-meta table { width: 100%; }
        .report-meta td { padding: 3px 6px; }
        .report-meta td:nth-child(2) { text-align: right; }
        .report-meta .label { color: #666; }
        .report-meta .value { font-weight: bold; color: #0f3460; }

        /* Section Titles */
        .section-title { background: #0f3460; color: #fff; padding: 8px 14px; font-size: 13px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; margin: 16px 0 10px; }

        /* Sub-section */
        .sub-title { color: #0f3460; font-size: 12px; font-weight: bold; border-bottom: 1px solid #c0c8e0; padding-bottom: 3px; margin: 12px 0 6px; }

        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .data-table th, .data-table td { border: 1px solid #c0c8e0; padding: 6px 10px; font-size: 10.5px; text-align: left; }
        .data-table th { background: #eef1fb; color: #333; font-weight: 600; }
        .data-table td { background: #fafbff; }

        /* Status Badges */
        .badge { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-done { background: #d4edda; color: #155724; }
        .badge-progress { background: #fff3cd; color: #856404; }
        .badge-planned { background: #e2e3e5; color: #383d41; }

        /* Module Card */
        .module-card { border: 1px solid #d0d9f0; padding: 8px 12px; margin-bottom: 8px; background: #fafbff; }
        .module-card .mc-header { font-weight: bold; color: #0f3460; font-size: 11.5px; margin-bottom: 3px; }
        .module-card .mc-desc { font-size: 10px; color: #555; }
        .module-card .mc-features { font-size: 10px; color: #333; margin-top: 3px; }

        /* Summary Boxes */
        .summary-row { margin: 14px 0; }
        .summary-row table { width: 100%; border-collapse: collapse; }
        .summary-box { border: 2px solid #0f3460; text-align: center; padding: 12px 8px; }
        .summary-box .s-label { font-size: 9px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
        .summary-box .s-value { font-size: 22px; font-weight: bold; color: #0f3460; margin-top: 2px; }

        /* Tech Stack */
        .tech-pill { display: inline-block; background: #0f3460; color: #fff; padding: 3px 10px; border-radius: 10px; font-size: 9px; margin: 2px 3px; font-weight: bold; }

        /* Notes */
        .note-box { background: #fffbe6; border-left: 4px solid #e94560; padding: 8px 12px; margin: 10px 0; font-size: 10px; }
        .note-box h4 { color: #e94560; margin-bottom: 4px; font-size: 11px; }
        .note-box ul { margin-left: 14px; }
        .note-box li { margin-bottom: 2px; }

        /* Footer */
        .footer { text-align: center; border-top: 1px solid #ddd; padding-top: 6px; font-size: 8px; color: #999; margin-top: 20px; }

        .watermark { position: fixed; top: 38%; left: 18%; font-size: 80px; color: rgba(15,52,96,0.03); transform: rotate(-30deg); font-weight: bold; letter-spacing: 10px; z-index: -1; }
        .text-center { text-align: center; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

<div class="watermark">DCHS</div>

<div class="page-border">

    <!-- ══════════════ COVER HEADER ══════════════ -->
    <div class="cover-header">
        <h1>Daniyal College of Health Sciences</h1>
        <div class="subtitle">College Management System – Project Status Report</div>
        <div class="tagline">Prepared for Client Review Meeting</div>
    </div>

    <!-- Report Meta -->
    <div class="report-meta">
        <table>
            <tr>
                <td><span class="label">Report Date:</span> <span class="value">{{ now()->format('d M, Y') }}</span></td>
                <td><span class="label">Version:</span> <span class="value">1.0</span></td>
            </tr>
            <tr>
                <td><span class="label">Project:</span> <span class="value">DCHS Management System</span></td>
                <td><span class="label">Status:</span> <span class="badge badge-progress">In Development</span></td>
            </tr>
            <tr>
                <td><span class="label">Platform:</span> <span class="value">Web Application (Laravel + Filament)</span></td>
                <td><span class="label">Campuses:</span> <span class="value">{{ $campusCount }} Active</span></td>
            </tr>
        </table>
    </div>

    <!-- ══════════════ EXECUTIVE SUMMARY ══════════════ -->
    <div class="section-title">1. Executive Summary</div>
    <p style="font-size:11px; text-align:justify; margin-bottom:10px;">
        The <strong>Daniyal College of Health Sciences (DCHS) Management System</strong> is a comprehensive, multi-campus
        college administration platform built to centralize and automate the day-to-day operations of all four campuses.
        The system covers the full student lifecycle — from admission applications through enrollment, fee management,
        timetable scheduling, examinations, and result generation — with role-based access control across 6 distinct user roles.
    </p>
    <p style="font-size:11px; text-align:justify; margin-bottom:10px;">
        The platform consists of two major components: a <strong>public-facing website</strong> showcasing programmes, campuses,
        and admissions information, and a <strong>secure admin dashboard</strong> for managing all internal operations.
        PDF document generation has been integrated for admission letters, fee receipts, and student report cards.
    </p>

    <!-- Summary Boxes -->
    <div class="summary-row">
        <table>
            <tr>
                <td class="summary-box" style="width:25%;">
                    <div class="s-label">Total Modules</div>
                    <div class="s-value">13</div>
                </td>
                <td style="width:1%;"></td>
                <td class="summary-box" style="width:25%;">
                    <div class="s-label">Campuses</div>
                    <div class="s-value">{{ $campusCount }}</div>
                </td>
                <td style="width:1%;"></td>
                <td class="summary-box" style="width:25%;">
                    <div class="s-label">Programmes</div>
                    <div class="s-value">{{ $courseCount }}</div>
                </td>
                <td style="width:1%;"></td>
                <td class="summary-box" style="width:25%;">
                    <div class="s-label">User Roles</div>
                    <div class="s-value">6</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ══════════════ TECHNOLOGY STACK ══════════════ -->
    <div class="section-title">2. Technology Stack</div>
    <table class="data-table">
        <tr><th style="width:30%;">Component</th><th>Technology</th><th style="width:20%;">Version</th></tr>
        <tr><td>Backend Framework</td><td>Laravel (PHP)</td><td>12.58.0</td></tr>
        <tr><td>Admin Dashboard</td><td>Filament (TALL Stack)</td><td>3.x</td></tr>
        <tr><td>Database</td><td>MySQL (XAMPP)</td><td>8.x</td></tr>
        <tr><td>PHP Runtime</td><td>PHP</td><td>8.2.4</td></tr>
        <tr><td>PDF Generation</td><td>barryvdh/laravel-dompdf</td><td>3.1</td></tr>
        <tr><td>Role & Permissions</td><td>spatie/laravel-permission</td><td>6.x</td></tr>
        <tr><td>Activity Logging</td><td>spatie/laravel-activitylog</td><td>4.x</td></tr>
        <tr><td>Frontend Assets</td><td>Vite + TailwindCSS</td><td>Latest</td></tr>
    </table>

    <!-- ══════════════ CAMPUS & PROGRAMME DATA ══════════════ -->
    <div class="section-title">3. Campus & Programme Configuration</div>

    <div class="sub-title">3.1 Registered Campuses ({{ $campusCount }})</div>
    <table class="data-table">
        <tr><th>#</th><th>Campus Name</th><th>City</th><th>Status</th></tr>
        @foreach($campuses as $i => $campus)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $campus->name }}</td>
            <td>{{ $campus->city }}</td>
            <td><span class="badge badge-done">Active</span></td>
        </tr>
        @endforeach
    </table>

    <div class="sub-title">3.2 Academic Programmes ({{ $courseCount }})</div>
    <table class="data-table">
        <tr><th>#</th><th>Code</th><th>Programme Name</th><th>Duration</th></tr>
        @foreach($courses as $i => $course)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $course->code }}</td>
            <td>{{ $course->name }}</td>
            <td>{{ $course->duration_months }} months</td>
        </tr>
        @endforeach
    </table>

</div><!-- end page-border page 1 -->

<!-- ══════════════ PAGE 2 – MODULES ══════════════ -->
<div class="page-break"></div>
<div class="page-border">

    <div class="section-title">4. System Modules – Detailed Breakdown</div>
    <p style="font-size:10.5px; margin-bottom:10px;">
        The following table provides a module-by-module overview of every feature implemented in the DCHS Management System.
        Each module includes full CRUD (Create, Read, Update, Delete) operations, campus-level data isolation,
        and role-based access control.
    </p>

    <table class="data-table">
        <tr>
            <th style="width:4%;">#</th>
            <th style="width:18%;">Module</th>
            <th style="width:14%;">Nav Group</th>
            <th>Key Features</th>
            <th style="width:10%;">Status</th>
        </tr>
        <tr>
            <td>1</td>
            <td><strong>Admissions</strong></td>
            <td>Student Relations</td>
            <td>Application form, applicant details (CNIC, DOB, father name), course & campus selection, status workflow (pending → approved/rejected/waitlisted), merit scoring, enrollment number, PDF admission letter generation</td>
            <td><span class="badge badge-done">Done</span></td>
        </tr>
        <tr>
            <td>2</td>
            <td><strong>Students</strong></td>
            <td>Student Relations</td>
            <td>Student profiles, enrollment number, programme & batch assignment, active/inactive status, linked to admission record, PDF report card download</td>
            <td><span class="badge badge-done">Done</span></td>
        </tr>
        <tr>
            <td>3</td>
            <td><strong>Staff Management</strong></td>
            <td>Administration</td>
            <td>Employee profiles linked to user accounts, employee ID, designation, phone, hire date, active/inactive toggle, campus-scoped</td>
            <td><span class="badge badge-done">Done</span></td>
        </tr>
        <tr>
            <td>4</td>
            <td><strong>Fee Structures</strong></td>
            <td>Financial</td>
            <td>Per-campus & per-course fee configuration, academic year, total fee amount, installment count, late fee charges</td>
            <td><span class="badge badge-done">Done</span></td>
        </tr>
        <tr>
            <td>5</td>
            <td><strong>Fee Payments</strong></td>
            <td>Financial</td>
            <td>Payment recording with installment tracking, amount, due date, paid date, payment method (cash/bank/cheque), transaction ID, status (paid/unpaid/overdue/partial), PDF receipt download</td>
            <td><span class="badge badge-done">Done</span></td>
        </tr>
        <tr>
            <td>6</td>
            <td><strong>Timetable</strong></td>
            <td>Academic Mgmt</td>
            <td>Weekly schedule management, day-of-week, start/end time, subject name, room number, teacher assignment (linked to staff → user), course-filtered</td>
            <td><span class="badge badge-done">Done</span></td>
        </tr>
        <tr>
            <td>7</td>
            <td><strong>Examinations</strong></td>
            <td>Academic Mgmt</td>
            <td>Exam creation with name, type (midterm/final/practical/sessional), start & end date, total marks, course-linked, campus-scoped</td>
            <td><span class="badge badge-done">Done</span></td>
        </tr>
        <tr>
            <td>8</td>
            <td><strong>Marks / Results</strong></td>
            <td>Academic Mgmt</td>
            <td>Student-exam mark entry, obtained marks, pass/fail/incomplete status, remarks, unique per student-exam combination</td>
            <td><span class="badge badge-done">Done</span></td>
        </tr>
        <tr>
            <td>9</td>
            <td><strong>Announcements</strong></td>
            <td>Administration</td>
            <td>Rich-text announcements, active/inactive toggle, campus-specific or global, creation timestamp</td>
            <td><span class="badge badge-done">Done</span></td>
        </tr>
        <tr>
            <td>10</td>
            <td><strong>User Management</strong></td>
            <td>Administration</td>
            <td>User accounts with name, email, phone, password, multi-role assignment, campus assignment, active/inactive status, permission-gated CRUD</td>
            <td><span class="badge badge-done">Done</span></td>
        </tr>
        <tr>
            <td>11</td>
            <td><strong>Campus Mgmt</strong></td>
            <td>Administration</td>
            <td>Campus CRUD (name, city, active status), data isolation foundation for all modules</td>
            <td><span class="badge badge-done">Done</span></td>
        </tr>
        <tr>
            <td>12</td>
            <td><strong>Course Mgmt</strong></td>
            <td>Administration</td>
            <td>Programme CRUD (code, name, duration), linked to admissions, students, fees, exams</td>
            <td><span class="badge badge-done">Done</span></td>
        </tr>
        <tr>
            <td>13</td>
            <td><strong>Settings</strong></td>
            <td>Administration</td>
            <td>Key-value configuration per campus, customizable system parameters</td>
            <td><span class="badge badge-done">Done</span></td>
        </tr>
    </table>

    <!-- ══════════════ PDF GENERATION ══════════════ -->
    <div class="section-title">5. PDF Document Generation</div>
    <table class="data-table">
        <tr><th>#</th><th>Document Type</th><th>Generated From</th><th>Contents</th></tr>
        <tr>
            <td>1</td>
            <td><strong>Admission Letter</strong></td>
            <td>Admissions Module</td>
            <td>Applicant details, course, campus, status-aware body text, reference number, dual signature blocks</td>
        </tr>
        <tr>
            <td>2</td>
            <td><strong>Fee Receipt</strong></td>
            <td>Fee Payments Module</td>
            <td>Student info, payment breakdown, highlighted amount, payment method, transaction ID, tri-signature block</td>
        </tr>
        <tr>
            <td>3</td>
            <td><strong>Student Report Card</strong></td>
            <td>Students Module</td>
            <td>Full exam results table, auto-calculated percentages, pass/fail, summary statistics, grading scale</td>
        </tr>
    </table>

</div><!-- end page-border page 2 -->

<!-- ══════════════ PAGE 3 – SECURITY & ROADMAP ══════════════ -->
<div class="page-break"></div>
<div class="page-border">

    <!-- ══════════════ ROLE-BASED ACCESS ══════════════ -->
    <div class="section-title">6. Security & Role-Based Access Control</div>
    <p style="font-size:10.5px; margin-bottom:8px;">
        The system implements <strong>Spatie Laravel Permission</strong> with 6 user roles and 35+ granular permissions.
        Every module enforces campus-level data isolation through a custom <code>ScopedByCampus</code> trait.
    </p>
    <table class="data-table">
        <tr><th>#</th><th>Role</th><th>Access Level</th><th>Key Permissions</th></tr>
        <tr><td>1</td><td><strong>Super Admin</strong></td><td>Full System</td><td>All modules, all campuses, all operations, settings management</td></tr>
        <tr><td>2</td><td><strong>Campus Principal</strong></td><td>Own Campus</td><td>Users, admissions, students, fees, timetable, exams, results, reports</td></tr>
        <tr><td>3</td><td><strong>Faculty</strong></td><td>Own Campus</td><td>View students, timetable, exams; create & update results</td></tr>
        <tr><td>4</td><td><strong>Admission Officer</strong></td><td>Own Campus</td><td>Full admission CRUD, create students, view dashboard</td></tr>
        <tr><td>5</td><td><strong>Finance</strong></td><td>Own Campus</td><td>Student view, full fee CRUD, reports, dashboard</td></tr>
        <tr><td>6</td><td><strong>Student</strong></td><td>Own Profile</td><td>View own profile, fees, attendance, results, timetable</td></tr>
    </table>

    <!-- Multi-Campus Architecture -->
    <div class="sub-title">6.1 Multi-Campus Data Isolation</div>
    <div class="module-card">
        <div class="mc-header">ScopedByCampus Trait</div>
        <div class="mc-desc">
            A custom Eloquent trait automatically filters all database queries based on the logged-in user's assigned campus.
            Super Admins bypass this filter to see data across all campuses. New records are automatically assigned the
            user's campus_id, ensuring complete data isolation between campuses without manual intervention.
        </div>
    </div>

    <!-- ══════════════ PUBLIC WEBSITE ══════════════ -->
    <div class="section-title">7. Public-Facing Website</div>
    <table class="data-table">
        <tr><th>#</th><th>Page</th><th>Route</th><th>Description</th></tr>
        <tr><td>1</td><td><strong>Home Page</strong></td><td>/</td><td>Hero section, programme highlights, campus showcase, dynamic data from DB</td></tr>
        <tr><td>2</td><td><strong>Programmes</strong></td><td>/courses</td><td>All 9 courses listed dynamically with details</td></tr>
        <tr><td>3</td><td><strong>Programme Detail</strong></td><td>/courses/{code}</td><td>Individual programme page with full info</td></tr>
        <tr><td>4</td><td><strong>Campuses</strong></td><td>/campuses</td><td>All 4 campus locations with details</td></tr>
        <tr><td>5</td><td><strong>Admissions</strong></td><td>/admissions</td><td>Admission information page</td></tr>
        <tr><td>6</td><td><strong>Apply</strong></td><td>/apply</td><td>Online application form</td></tr>
        <tr><td>7</td><td><strong>Contact</strong></td><td>/contact</td><td>Contact information page</td></tr>
    </table>

    <!-- ══════════════ DATA PERSISTENCE ══════════════ -->
    <div class="section-title">8. Data Persistence Strategy</div>
    <div class="note-box">
        <h4>Database Seeders (Migration-Safe)</h4>
        <ul>
            <li><strong>CampusAndCourseSeeder</strong> — Seeds all 4 campuses and 9 programmes with <code>firstOrCreate</code> to prevent duplicates</li>
            <li><strong>RoleAndPermissionSeeder</strong> — Seeds 6 roles with 35+ permissions using idempotent operations</li>
            <li><strong>AdminUserSeeder</strong> — Creates the Super Admin login credential, ensuring access is never lost during migrations</li>
        </ul>
    </div>

    <!-- ══════════════ BRANDING ══════════════ -->
    <div class="section-title">9. Branding & Design</div>
    <table class="data-table">
        <tr><th>Element</th><th>Implementation</th></tr>
        <tr><td>Primary Color</td><td>#1e3a5f (Deep Navy Blue)</td></tr>
        <tr><td>Accent Color</td><td>#d4af37 (Gold)</td></tr>
        <tr><td>Alert / Highlight</td><td>#e94560 (Coral Red)</td></tr>
        <tr><td>Logo</td><td>DCHS branded logo on dashboard & login</td></tr>
        <tr><td>Custom CSS</td><td>Admin dashboard theme with branded sidebar, stats cards, and buttons</td></tr>
        <tr><td>PDF Documents</td><td>Double-border layout, DCHS watermark, professional signature blocks</td></tr>
    </table>

    <!-- ══════════════ NEXT STEPS ══════════════ -->
    <div class="section-title">10. Upcoming / Planned Features</div>
    <table class="data-table">
        <tr><th>#</th><th>Feature</th><th>Priority</th><th>Status</th></tr>
        <tr><td>1</td><td>Student Portal (login & view own data)</td><td>High</td><td><span class="badge badge-planned">Planned</span></td></tr>
        <tr><td>2</td><td>Attendance Tracking Module</td><td>High</td><td><span class="badge badge-planned">Planned</span></td></tr>
        <tr><td>3</td><td>Dashboard Analytics & Charts</td><td>Medium</td><td><span class="badge badge-planned">Planned</span></td></tr>
        <tr><td>4</td><td>Email / SMS Notifications</td><td>Medium</td><td><span class="badge badge-planned">Planned</span></td></tr>
        <tr><td>5</td><td>Online Fee Payment Gateway</td><td>Medium</td><td><span class="badge badge-planned">Planned</span></td></tr>
        <tr><td>6</td><td>Excel Export for Reports</td><td>Low</td><td><span class="badge badge-planned">Planned</span></td></tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        CONFIDENTIAL – Daniyal College of Health Sciences &bull; Project Status Report &bull;
        Generated on {{ now()->format('d M, Y \\a\\t h:i A') }} &bull; Page 3 of 3
    </div>

</div><!-- end page-border page 3 -->

</body>
</html>
