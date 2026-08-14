<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class TrainingController extends AdminController
{
    public function courses()
    {
        $this->requireAdmin();
        try {
            $courses = $this->db->fetchAll("SELECT tc.*, u.name as created_by_name FROM training_courses tc LEFT JOIN users u ON tc.created_by = u.id ORDER BY tc.created_at DESC") ?: [];
            $totalCourses = count($courses);
            $activeCourses = count(array_filter($courses, fn($c) => ($c['is_active'] ?? 0) == 1));
            $mandatoryCourses = count(array_filter($courses, fn($c) => ($c['is_mandatory'] ?? 0) == 1));
        } catch (\Exception $e) {
            $courses = [];
            $totalCourses = 0;
            $activeCourses = 0;
            $mandatoryCourses = 0;
        }
        return $this->render('admin/training/courses', [
            'page_title' => 'Training Courses',
            'courses' => $courses,
            'totalCourses' => $totalCourses,
            'activeCourses' => $activeCourses,
            'mandatoryCourses' => $mandatoryCourses
        ]);
    }

    public function createCourse()
    {
        $this->requireAdmin();
        return $this->render('admin/training/create-course', [
            'page_title' => 'Create Training Course'
        ]);
    }

    public function storeCourse()
    {
        $this->requireAdmin();
        try {
            $this->db->query("INSERT INTO training_courses (tenant_id, course_title, course_description, course_category, difficulty_level, course_duration_hours, course_objectives, prerequisites, target_audience, is_mandatory, is_active, max_enrollments, passing_score_percentage, points_reward, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())", [
                $this->tenantId(),
                $_POST['course_title'] ?? '',
                $_POST['course_description'] ?? '',
                $_POST['course_category'] ?? 'sales',
                $_POST['difficulty_level'] ?? 'beginner',
                (float)($_POST['course_duration_hours'] ?? 0),
                $_POST['course_objectives'] ?? '',
                $_POST['prerequisites'] ?? '',
                $_POST['target_audience'] ?? '',
                isset($_POST['is_mandatory']) ? 1 : 0,
                isset($_POST['is_active']) ? 1 : 0,
                (int)($_POST['max_enrollments'] ?? 0),
                (int)($_POST['passing_score_percentage'] ?? 0),
                (int)($_POST['points_reward'] ?? 0),
                $_SESSION['admin_id'] ?? null
            ]);
            $this->setFlash('success', 'Course created successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create course: ' . $e->getMessage());
        }
        $this->redirect('/admin/training/courses');
    }

    public function editCourse($id)
    {
        $this->requireAdmin();
        try {
            $course = $this->db->fetch("SELECT * FROM training_courses WHERE id = ?", [(int)$id]);
            if (!$course) {
                $this->setFlash('error', 'Course not found');
                $this->redirect('/admin/training/courses');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to load course');
            $this->redirect('/admin/training/courses');
        }
        return $this->render('admin/training/edit-course', [
            'page_title' => 'Edit Course',
            'course' => $course
        ]);
    }

    public function updateCourse($id)
    {
        $this->requireAdmin();
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $this->db->query("UPDATE training_courses SET course_title = ?, course_description = ?, course_category = ?, difficulty_level = ?, course_duration_hours = ?, course_objectives = ?, prerequisites = ?, target_audience = ?, is_mandatory = ?, is_active = ?, max_enrollments = ?, passing_score_percentage = ?, points_reward = ? WHERE id = ?" . $tenantSql, array_merge([
                $_POST['course_title'] ?? '',
                $_POST['course_description'] ?? '',
                $_POST['course_category'] ?? 'sales',
                $_POST['difficulty_level'] ?? 'beginner',
                (float)($_POST['course_duration_hours'] ?? 0),
                $_POST['course_objectives'] ?? '',
                $_POST['prerequisites'] ?? '',
                $_POST['target_audience'] ?? '',
                isset($_POST['is_mandatory']) ? 1 : 0,
                isset($_POST['is_active']) ? 1 : 0,
                (int)($_POST['max_enrollments'] ?? 0),
                (int)($_POST['passing_score_percentage'] ?? 0),
                (int)($_POST['points_reward'] ?? 0),
                (int)$id
            ], $tenantParams));
            $this->setFlash('success', 'Course updated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update course: ' . $e->getMessage());
        }
        $this->redirect('/admin/training/courses');
    }

    public function enrollments()
    {
        $this->requireAdmin();
        try {
            $enrollments = $this->db->fetchAll("SELECT te.*, u.name as user_name, u.email as user_email, tc.course_title FROM training_enrollments te LEFT JOIN users u ON te.user_id = u.id LEFT JOIN training_courses tc ON te.course_id = tc.id ORDER BY te.enrolled_at DESC") ?: [];
            $totalEnrollments = count($enrollments);
            $activeEnrollments = count(array_filter($enrollments, fn($e) => ($e['status'] ?? '') == 'active'));
            $completedEnrollments = count(array_filter($enrollments, fn($e) => ($e['status'] ?? '') == 'completed'));
            $droppedEnrollments = count(array_filter($enrollments, fn($e) => ($e['status'] ?? '') == 'dropped'));
        } catch (\Exception $e) {
            $enrollments = [];
            $totalEnrollments = 0;
            $activeEnrollments = 0;
            $completedEnrollments = 0;
            $droppedEnrollments = 0;
        }
        return $this->render('admin/training/enrollments', [
            'page_title' => 'Training Enrollments',
            'enrollments' => $enrollments,
            'totalEnrollments' => $totalEnrollments,
            'activeEnrollments' => $activeEnrollments,
            'completedEnrollments' => $completedEnrollments,
            'droppedEnrollments' => $droppedEnrollments
        ]);
    }

    public function showEnrollment($id)
    {
        $this->requireAdmin();
        try {
            $enrollment = $this->db->fetch("SELECT te.*, u.name as user_name, u.email as user_email, u.phone as user_phone, tc.course_title, tc.course_description, tc.course_category, tc.difficulty_level, tc.course_duration_hours, tc.passing_score_percentage, tc.is_mandatory FROM training_enrollments te LEFT JOIN users u ON te.user_id = u.id LEFT JOIN training_courses tc ON te.course_id = tc.id WHERE te.id = ?", [(int)$id]);
            if (!$enrollment) {
                $this->setFlash('error', 'Enrollment not found');
                $this->redirect('/admin/training/enrollments');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to load enrollment');
            $this->redirect('/admin/training/enrollments');
        }
        return $this->render('admin/training/enrollment-show', [
            'page_title' => 'Enrollment Details',
            'enrollment' => $enrollment
        ]);
    }

    public function certificates()
    {
        $this->requireAdmin();
        try {
            $certificates = $this->db->fetchAll("SELECT tc.*, u.name as associate_name FROM training_certificates tc LEFT JOIN users u ON tc.associate_id = u.id ORDER BY tc.issued_date DESC") ?: [];
        } catch (\Exception $e) {
            $certificates = [];
        }
        return $this->render('admin/training/certificates', [
            'page_title' => 'Training Certificates',
            'certificates' => $certificates
        ]);
    }

    public function downloadCertificate($id)
    {
        $this->requireAdmin();
        try {
            $cert = $this->db->fetch("SELECT * FROM training_certificates WHERE id = ?", [(int)$id]);
            if (!$cert) {
                $this->setFlash('error', 'Certificate not found');
                $this->redirect('/admin/training/certificates');
                return;
            }
            // Generate HTML certificate for download
            $html = '<!DOCTYPE html><html><head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="UTF-8"><title>Certificate - ' . htmlspecialchars($cert['certificate_number']) . '</title>';
            $html .= '<style>body{font-family:Georgia,serif;text-align:center;padding:60px;background:#fff;}';
            $html .= '.border{border:8px double #c9a84c;padding:40px;margin:20px;position:relative;}';
            $html .= 'h1{color:#1e3a5f;font-size:36px;margin-bottom:5px;}h2{color:#333;font-size:20px;font-weight:normal;margin-top:10px;}';
            $html .= '.name{font-size:28px;color:#0d9488;font-weight:bold;margin:20px 0;}';
            $html .= '.detail{font-size:14px;color:#555;margin:5px 0;}';
            $html .= '.seal{margin-top:30px;font-size:12px;color:#888;}';
            $html .= '.watermark{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-30deg);font-size:100px;color:rgba(201,168,76,0.08);pointer-events:none;white-space:nowrap;}</style></head><body>';
            $html .= '<div class="border"><div class="watermark">APS DREAM HOME</div>';
            $html .= '<h1>CERTIFICATE</h1><h2>' . htmlspecialchars($cert['certificate_type']) . '</h2>';
            $html .= '<p class="detail">This is to certify that</p>';
            $html .= '<div class="name">' . htmlspecialchars($cert['associate_name']) . '</div>';
            $html .= '<p class="detail">has successfully completed the course</p>';
            $html .= '<h2>"' . htmlspecialchars($cert['certificate_title']) . '"</h2>';
            if (!empty($cert['score_percentage'])) {
                $html .= '<p class="detail">with a score of <strong>' . number_format($cert['score_percentage'], 1) . '%</strong></p>';
            }
            $html .= '<p class="detail">Certificate No: <strong>' . htmlspecialchars($cert['certificate_number']) . '</strong></p>';
            $html .= '<p class="detail">Date of Issue: <strong>' . htmlspecialchars($cert['issued_date']) . '</strong></p>';
            $html .= '<div class="seal"><p>APS Dream Home | Gorakhpur, UP | apsdreamhome.com</p>';
            $html .= '<p>Authorized Signature</p></div></div></body></html>';

            header('Content-Type: text/html');
            header('Content-Disposition: attachment; filename="certificate-' . $cert['certificate_number'] . '.html"');
            echo $html;
            exit;
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to download: ' . $e->getMessage());
            $this->redirect('/admin/training/certificates');
        }
    }

    public function modules()
    {
        $this->requireAdmin();
        try {
            $modules = $this->db->fetchAll("SELECT tm.*, tc.course_title FROM training_modules tm LEFT JOIN training_courses tc ON tm.course_id = tc.id ORDER BY tm.course_id, tm.order_index") ?: [];
            $courses = $this->db->fetchAll("SELECT id, course_title FROM training_courses WHERE is_active = 1 ORDER BY course_title") ?: [];
        } catch (\Exception $e) {
            $modules = [];
            $courses = [];
        }
        return $this->render('admin/training/modules', [
            'page_title' => 'Training Modules',
            'modules' => $modules,
            'courses' => $courses
        ]);
    }

    public function storeModule()
    {
        $this->requireAdmin();
        try {
            $this->db->query("INSERT INTO training_modules (tenant_id, course_id, title, description, order_index, content_type, content_url, duration_minutes, is_required) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $this->tenantId(),
                (int)($_POST['course_id'] ?? 0),
                $_POST['title'] ?? '',
                $_POST['description'] ?? '',
                (int)($_POST['order_index'] ?? 0),
                $_POST['content_type'] ?? 'video',
                $_POST['content_url'] ?? '',
                (int)($_POST['duration_minutes'] ?? 0),
                isset($_POST['is_required']) ? 1 : 0
            ]);
            $this->setFlash('success', 'Module added successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to add module: ' . $e->getMessage());
        }
        $this->redirect('/admin/training/modules');
    }
}
