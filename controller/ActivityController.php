<?php
require_once __DIR__ . '/../model/ActivityModel.php';

class ActivityController {
    private $model;

    public function __construct() {
        $this->model = new ActivityModel();
    }

    public function index() {
        $searchTerm = $_GET['search'] ?? '';
        if (!empty($searchTerm)) {
            $activities = $this->model->searchActivitiesByName($searchTerm);
        } else {
            $activities = $this->model->getAllActivities();
        }

        return [
            'section' => 'control_data',
            'activities' => $activities,
            'searchTerm' => $searchTerm
        ];
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = floatval($_POST['price'] ?? 0);
            $location = $_POST['location'] ?? '';
            $date = $_POST['date'] ?? '';
            $category = $_POST['category'] ?? '';
            $capacity = intval($_POST['capacity'] ?? 0);
            $image = null;

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../image/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $imagePath = basename($_FILES['image']['name']);
                $image = 'image/' . $imagePath;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imagePath);
            }

            $success = $this->model->addActivity($name, $description, $price, $location, $date, $category, $capacity, $image);
            if ($success) {
                header("Location: dashboard.php");
                exit;
            } else {
                return [
                    'section' => 'add_activity',
                    'error' => 'Erreur lors de l\'ajout de l\'activité.'
                ];
            }
        }
        return ['section' => 'add_activity'];
    }

    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = floatval($_POST['price'] ?? 0);
            $location = $_POST['location'] ?? '';
            $date = $_POST['date'] ?? '';
            $category = $_POST['category'] ?? '';
            $capacity = intval($_POST['capacity'] ?? 0);
            $image = $_POST['current_image'] ?? null;

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../image/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $imagePath = basename($_FILES['image']['name']);
                $image = 'image/' . $imagePath;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imagePath);
            }

            $success = $this->model->updateActivity($id, $name, $description, $price, $location, $date, $category, $capacity, $image);
            if ($success) {
                header("Location: dashboard.php");
                exit;
            } else {
                return [
                    'section' => 'edit_activity',
                    'activity' => $this->model->getActivityById($id),
                    'error' => 'Erreur lors de la mise à jour de l\'activité.'
                ];
            }
        }

        $activity = $this->model->getActivityById($id);
        return [
            'section' => 'edit_activity',
            'activity' => $activity
        ];
    }

    public function delete($id) {
        $this->model->deleteActivity($id);
        header("Location: dashboard.php");
        exit;
    }

    public function notifications() {
        return [
            'section' => 'notifications',
            'notifications' => $this->model->getNotifications()
        ];
    }

    public function calendar() {
        return [
            'section' => 'calendar',
            'activities' => $this->model->getUpcomingActivities()
        ];
    }

    public function statistics() {
        return [
            'section' => 'statistics',
            'stats' => $this->model->getStatistics(),
            'participantsByMonth' => $this->model->getParticipantsByMonth(),
            'activitiesByCategory' => $this->model->getActivitiesByCategory()
        ];
    }

    public function daily_activity() {
        return [
            'section' => 'daily_activity',
            'dailyActivity' => $this->model->getDailyActivity()
        ];
    }

    public function history() {
        return [
            'section' => 'history',
            'history' => $this->model->getActivityHistory(),
            'upcomingActivities' => $this->model->getUpcomingActivities()
        ];
    }

    public function settings() {
        return ['section' => 'settings'];
    }

    public function logout() {
        header("Location: login.php");
        exit;
    }
}
?>
