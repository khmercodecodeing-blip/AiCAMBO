<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\InvoiceModel;

/**
 * Admin Controller — Dashboard, course CRUD, invoice management, student list
 */
class AdminController
{
    private CourseModel $courseModel;
    private InvoiceModel $invoiceModel;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
        $this->invoiceModel = new InvoiceModel();
    }

    /**
     * Show login form
     */
    public function loginForm(): void
    {
        if (is_admin()) {
            redirect('/' . ADMIN_PREFIX . '/dashboard');
            return;
        }
        $pageTitle = 'Admin Login — ' . APP_NAME;
        require APP_ROOT . '/app/views/admin/login.php';
    }

    /**
     * Process login
     */
    public function login(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            redirect('/' . ADMIN_PREFIX . '/login');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD)) {
            $_SESSION['is_admin'] = true;
            $_SESSION['admin_user'] = $username;
            flash('success', 'Welcome back, ' . e($username) . '!');
            redirect('/' . ADMIN_PREFIX . '/dashboard');
        } else {
            flash('error', 'Invalid credentials.');
            redirect('/' . ADMIN_PREFIX . '/login');
        }
    }

    /**
     * Dashboard with statistics
     */
    public function dashboard(): void
    {
        require_admin();
        $stats = $this->invoiceModel->getStats();
        $courseCount = $this->courseModel->getCount();
        $recentInvoices = $this->invoiceModel->getAll(null, 10);
        $pageTitle = 'Dashboard — Admin';
        require APP_ROOT . '/app/views/admin/dashboard.php';
    }

    /**
     * Course management list
     */
    public function courses(): void
    {
        require_admin();
        $courses = $this->courseModel->getAllAdmin();
        $pageTitle = 'Manage Courses — Admin';
        require APP_ROOT . '/app/views/admin/courses.php';
    }

    /**
     * Course add/edit form
     */
    public function courseForm(): void
    {
        require_admin();
        $id = (int) ($_GET['id'] ?? 0);
        $course = $id > 0 ? $this->courseModel->getById($id) : null;
        $pageTitle = ($course ? 'Edit' : 'Add') . ' Course — Admin';
        require APP_ROOT . '/app/views/admin/course_form.php';
    }

    /**
     * Save course (create or update)
     */
    public function saveCourse(): void
    {
        require_admin();

        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request.');
            redirect('/' . ADMIN_PREFIX . '/courses');
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        
        $originalPriceRaw = trim($_POST['original_price'] ?? '');
        $originalPrice = ($originalPriceRaw !== '') ? (float) $originalPriceRaw : null;

        $data = [
            'title'             => trim($_POST['title'] ?? ''),
            'description'       => trim($_POST['description'] ?? ''),
            'price'             => (float) ($_POST['price'] ?? 0),
            'original_price'    => $originalPrice,
            'currency'          => $_POST['currency'] ?? 'USD',
            'type'              => $_POST['type'] ?? 'course',
            'video_url'         => trim($_POST['video_url'] ?? ''),
            'telegram_group_id' => trim($_POST['telegram_group_id'] ?? ''),
            'download_link'     => trim($_POST['download_link'] ?? ''),
            'is_active'         => isset($_POST['is_active']) ? 1 : 0,
        ];

        // Validate based on product type
        $validationFailed = empty($data['title']) || $data['price'] <= 0 ||
            ($data['type'] === 'course' && empty($data['telegram_group_id'])) ||
            ($data['type'] === 'tool' && empty($data['download_link']));

        if ($validationFailed) {
            flash('error', 'Please fill in all required fields.');
            redirect('/' . ADMIN_PREFIX . '/courses/form?id=' . $id);
            return;
        }

        // Validate original price
        if ($data['original_price'] !== null) {
            if ($data['original_price'] <= $data['price']) {
                flash('error', 'Original price must be greater than selling price.');
                redirect('/' . ADMIN_PREFIX . '/courses/form?id=' . $id);
                return;
            }
        }

        // Handle thumbnail upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['thumbnail'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed)) {
                flash('error', 'Invalid image format. Allowed: jpg, png, gif, webp');
                redirect('/' . ADMIN_PREFIX . '/courses/form?id=' . $id);
                return;
            }

            if ($file['size'] > 5 * 1024 * 1024) {
                flash('error', 'Image too large. Max 5MB.');
                redirect('/' . ADMIN_PREFIX . '/courses/form?id=' . $id);
                return;
            }

            $filename = 'course_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destination = APP_ROOT . '/storage/thumbnails/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $data['thumbnail'] = $filename;
            }
        }

        if ($id > 0) {
            $this->courseModel->update($id, $data);
            flash('success', 'Course updated successfully.');
        } else {
            $this->courseModel->create($data);
            flash('success', 'Course created successfully.');
        }

        redirect('/' . ADMIN_PREFIX . '/courses');
    }

    /**
     * Delete course
     */
    public function deleteCourse(int $id): void
    {
        require_admin();
        try {
            $this->courseModel->delete($id);
            flash('success', 'Course deleted successfully.');
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1451')) {
                flash('error', 'Cannot delete this course because it has associated purchase history (invoices). You can deactivate it by turning off the "Is Active" switch in the edit form instead.');
            } else {
                flash('error', 'Database error: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            flash('error', 'Error deleting course: ' . $e->getMessage());
        }
        redirect('/' . ADMIN_PREFIX . '/courses');
    }

    /**
     * Invoice list
     */
    public function invoices(): void
    {
        require_admin();
        $status = $_GET['status'] ?? null;
        $invoices = $this->invoiceModel->getAll($status);
        $pageTitle = 'Invoices — Admin';
        require APP_ROOT . '/app/views/admin/invoices.php';
    }

    /**
     * Student list
     */
    public function students(): void
    {
        require_admin();
        $students = $this->invoiceModel->getStudents();
        $pageTitle = 'Students — Admin';
        require APP_ROOT . '/app/views/admin/students.php';
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        session_destroy();
        redirect('/' . ADMIN_PREFIX . '/login');
    }

    /**
     * Promo codes list
     */
    public function promos(): void
    {
        require_admin();
        $promoModel = new \App\Models\PromoCodeModel();
        $promos = $promoModel->getAll();
        $pageTitle = 'Promo Codes — Admin';
        require APP_ROOT . '/app/views/admin/promos.php';
    }

    /**
     * Add promo code form
     */
    public function promoForm(): void
    {
        require_admin();
        $pageTitle = 'Add Promo Code — Admin';
        require APP_ROOT . '/app/views/admin/promo_form.php';
    }

    /**
     * Save a promo code
     */
    public function savePromo(): void
    {
        require_admin();
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request. Please try again.');
            redirect('/' . ADMIN_PREFIX . '/promos');
            return;
        }

        $code = strtoupper(trim($_POST['code'] ?? ''));
        $discountType = $_POST['discount_type'] ?? 'percentage';
        $discountValue = (float)($_POST['discount_value'] ?? 0);
        $maxUses = !empty($_POST['max_uses']) ? (int)$_POST['max_uses'] : null;
        $expiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
        $isActive = (int)($_POST['is_active'] ?? 1);

        if (empty($code) || $discountValue <= 0) {
            flash('error', 'Please fill in all required fields.');
            redirect('/' . ADMIN_PREFIX . '/promos/form');
            return;
        }

        $promoModel = new \App\Models\PromoCodeModel();
        
        // Check if code already exists
        $existing = $promoModel->getByCode($code);
        if ($existing) {
            flash('error', 'Promo code already exists.');
            redirect('/' . ADMIN_PREFIX . '/promos/form');
            return;
        }

        $promoModel->create([
            'code'           => $code,
            'discount_type'  => $discountType,
            'discount_value' => $discountValue,
            'max_uses'       => $maxUses,
            'expires_at'     => $expiresAt,
            'is_active'      => $isActive
        ]);

        flash('success', 'Promo code created successfully.');
        redirect('/' . ADMIN_PREFIX . '/promos');
    }

    /**
     * Delete promo code
     */
    public function deletePromo(int $id): void
    {
        require_admin();
        $promoModel = new \App\Models\PromoCodeModel();
        $promoModel->delete($id);
        flash('success', 'Promo code deleted successfully.');
        redirect('/' . ADMIN_PREFIX . '/promos');
    }
}
