<?php
require_once __DIR__ . '/../model/ActivityModel.php';

class ActivityController {
    private $model;

    public function __construct() {
        $this->model = new ActivityModel();
    }

    public function index() {
        $activities = $this->model->getAllActivities();
        $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
        
        if (!empty($searchTerm)) {
            $activities = $this->model->searchActivitiesByName($searchTerm);
        }
        
        return [
            'section' => 'control_data',
            'activities' => $activities,
            'searchTerm' => $searchTerm
        ];
    }

    public function add() {
        // Si le formulaire est soumis, l'action est gérée par process_activity.php
        return [
            'section' => 'add_activity'
        ];
    }

    public function edit($id) {
        $activity = $this->model->getActivityById($id);
        
        if (!$activity) {
            return [
                'section' => 'edit_activity',
                'error' => 'Activité non trouvée'
            ];
        }
        
        return [
            'section' => 'edit_activity',
            'activity' => $activity
        ];
    }

    public function delete($id) {
        // Cette méthode sert uniquement à rediriger vers process_activity.php
        // L'opération de suppression est effectuée dans process_activity.php
        $baseUrl = dirname(dirname($_SERVER['PHP_SELF']));
        header("Location: $baseUrl/view/back office/process_activity.php?operation=delete&id=$id");
        exit;
    }

    public function notifications() {
        $data = $this->model->getNotifications();
        
        return [
            'section' => 'notifications',
            'notifications' => $data
        ];
    }

    public function calendar() {
        $activities = $this->model->getUpcomingActivities();
        
        return [
            'section' => 'calendar',
            'activities' => $activities
        ];
    }

    public function statistics() {
        $stats = $this->model->getStatistics();
        $participantsByMonth = $this->model->getParticipantsByMonth();
        $activitiesByCategory = $this->model->getActivitiesByCategory();
        
        return [
            'section' => 'statistics',
            'stats' => $stats,
            'participantsByMonth' => $participantsByMonth,
            'activitiesByCategory' => $activitiesByCategory
        ];
    }

    public function daily_activity() {
        $dailyActivity = $this->model->getDailyActivity();
        
        return [
            'section' => 'daily_activity',
            'dailyActivity' => $dailyActivity
        ];
    }

    public function history() {
        $history = $this->model->getActivityHistory();
        $upcomingActivities = $this->model->getUpcomingActivities();
        
        return [
            'section' => 'history',
            'history' => $history,
            'upcomingActivities' => $upcomingActivities
        ];
    }

    public function settings() {
        // Placeholder pour de futures fonctionnalités
        return [
            'section' => 'settings'
        ];
    }

    public function logout() {
        // Implémentation de la déconnexion
        // À adapter selon votre système d'authentification
        session_start();
        session_destroy();
        header("Location: login.php");
        exit;
    }
}
?>
