<?php

namespace App\Controllers;

use App\Models\CourseModel;

/**
 * Course Controller — Handles public course listing and detail views
 */
class CourseController
{
    private CourseModel $courseModel;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
    }

    /**
     * Display course catalog
     */
    public function index(): void
    {
        $courses = $this->courseModel->getAll();
        $pageTitle = 'Explore Courses — ' . APP_NAME;
        require APP_ROOT . '/app/views/courses/index.php';
    }

    /**
     * Display single course detail
     */
    public function detail(int $id): void
    {
        $course = $this->courseModel->getById($id);

        if (!$course) {
            http_response_code(404);
            echo '<h1>Course Not Found</h1>';
            return;
        }

        $pageTitle = e($course['title']) . ' — ' . APP_NAME;
        require APP_ROOT . '/app/views/courses/detail.php';
    }

    /**
     * Display Telegram Adder Pro Product Landing Page
     */
    public function telegramAdderPage(): void
    {
        $pageTitle = 'Telegram Adder Pro — ' . APP_NAME;
        require APP_ROOT . '/app/views/courses/telegram_adder.php';
    }
}
