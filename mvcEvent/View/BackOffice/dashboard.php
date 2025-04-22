<?php
require_once '../../Controller/EventController.php';

$controller = new eventController();
$editId = $_POST['edit_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $event = new Event(
                $_POST['category'],
                $_POST['name'],
                $_POST['description'],
                $_POST['price'],
                $_POST['duration'],
                $_POST['date'],
                $_POST['location'],
                $_POST['imageUrl'],
                $_POST['totalSeats'],
                $_POST['reservedSeats'] ?? 0
            );
            $controller->addEvent($event);
            break;

        case 'supp':
            $id = $_POST['id'];
            $controller->deleteEvent($id);
            break;

        case 'modif':
            $event = new Event(
                $_POST['category'],
                $_POST['name'],
                $_POST['description'],
                $_POST['price'],
                $_POST['duration'],
                $_POST['date'],
                $_POST['location'],
                $_POST['imageUrl'],
                $_POST['totalSeats'],
                $_POST['reservedSeats'] ?? 0
            );
            $controller->updateEvent($event, $_POST['id']);
            break;
    }

    if ($action !== 'edit') {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des événements</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="add.css">

    <style>
.seat-management {
    margin: 20px;
    padding: 25px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0;
}

.seat-management:hover {
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}

.seat-management h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.5rem;
    border-bottom: 2px solid #C83EFC;
    padding-bottom: 10px;
    display: inline-block;
}

.seat-controls {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.seat-controls select {
    padding: 10px 15px;
    border-radius: 6px;
    border: 1px solid #bdc3c7;
    background-color: #f8f9fa;
    font-size: 14px;
    color: #2c3e50;
    transition: all 0.3s;
    min-width: 200px;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 15px;
}

.seat-controls select:focus {
    outline: none;
    border-color: #C83EFC;
    box-shadow: 0 0 0 3px rgba(52,152,219,0.2);
}

.seat-stats {
    font-weight: 600;
    color: #2c3e50;
    background: #f1f8fe;
    padding: 10px 15px;
    border-radius: 6px;
    border-left: 4px solid #C83EFC;
    margin-left: auto;
}

.seat-grid-container {
    max-width: 100%;
    overflow-x: auto;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.seat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
    gap: 12px;
    padding: 15px;
    transition: transform 0.3s;
    min-height: 200px;
    align-items: center;
    justify-items: center;
}

.seat {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    font-weight: 600;
    color: white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.seat::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.1);
    transform: translateY(100%);
    transition: transform 0.3s;
}

.seat:hover::before {
    transform: translateY(0);
}

.seat:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.seat.libre { 
    background-color: #2ecc71;
    background-image: linear-gradient(135deg, #2ecc71, #27ae60);
}

.seat.reserve { 
    background-color: #e74c3c;
    background-image: linear-gradient(135deg, #e74c3c, #c0392b);
}

.seat.selected {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 1000;
    backdrop-filter: blur(5px);
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    margin: 10% auto;
    padding: 30px;
    width: 90%;
    max-width: 500px;
    border-radius: 12px;
    position: relative;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    animation: slideDown 0.4s;
}

@keyframes slideDown {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-content h3 {
    margin-top: 0;
    color: #2c3e50;
    font-size: 1.5rem;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.modal-content .close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 28px;
    cursor: pointer;
    color: #7f8c8d;
    transition: color 0.3s;
}

.modal-content .close:hover {
    color: #e74c3c;
}

#seatDetails {
    margin: 20px 0;
    line-height: 1.6;
}

#seatDetails p {
    margin: 10px 0;
    display: flex;
    justify-content: space-between;
}

#seatDetails strong {
    color: #2c3e50;
    font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .seat-controls {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .seat-stats {
        margin-left: 0;
        width: 100%;
    }
    
    .modal-content {
        width: 95%;
        margin: 20% auto;
    }
}

/* Loading animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-spinner {
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    border-top: 4px solid #3498db;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 20px auto;
}
</style>

</head>
<body>
    <div class="sidebar">
        <div>
            <h1>Event Management</h1>
            <div class="menu-item active">Événements</div>
            <div class="menu-item">Utilisateurs</div>
            <div class="menu-item">Paramètres</div>
        </div>
        <div class="profile-container">
            <div class="profile">
                <img src="https://via.placeholder.com/40" alt="Profile">
            </div>
            <div>Admin</div>
        </div>
    </div>

    <div class="dashboard">
        <div class="header">
            <h2>Gestion des événements</h2>
            <button class="open-panel-btn" id="openPanel">+ Ajouter un événement</button>
        </div>

        <div class="cardS">
        <button id="toggleTableBtn" class="toggle-table-btn">
        <i>▼</i> Réduire le tableau
    </button>

    <div class="collapsible-table" id="eventTable">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Catégorie</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Prix</th>
                        <th>Durée</th>
                        <th>Date</th>
                        <th>Lieu</th>
                        <th>Image</th>
                        <th>Places totales</th>
                        <th>Places réservées</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $events = $controller->getEvents();
                foreach ($events as $event) {
                    echo "<tr>";
                    echo "<td>{$event['id']}</td>";

                    if ($editId == $event['id']) {
                        echo "<form method='post'>";
                        echo "<input type='hidden' name='id' value='{$event['id']}'>";
                        
                        // Catégorie
                        echo "<td><input type='text' name='category' value='".htmlspecialchars($event['category'])."' 
                                  required minlength='3'></td>";
                        
                        // Nom
                        echo "<td><input type='text' name='name' value='".htmlspecialchars($event['name'])."' 
                                  required minlength='3'></td>";
                        
                        // Description
                        echo "<td><input type='text' name='description' value='".htmlspecialchars($event['description'])."' 
                                  required></td>";
                        
                        // Prix
                        echo "<td><input type='number' step='0.01' name='price' value='{$event['price']}' 
                                  required min='0'></td>";
                        
                        // Durée
                        echo "<td><input type='number' name='duration' value='{$event['duration']}' 
                                  required min='1' max='8'></td>";
                        
                        // Date
                        echo "<td><input type='date' name='date' value='{$event['date']}' 
                                  required min='".date('Y-m-d')."'></td>";
                        
                        // Lieu
                        echo "<td><input type='text' name='location' value='".htmlspecialchars($event['location'])."' 
                                  required minlength='3' pattern='^[^\\d]+$'></td>";
                        
                        // Image
                        echo "<td>
                        <input type='text' name='imageUrl' value='".htmlspecialchars($event['imageUrl'])."' 
                             minlength='3' pattern='.*/.*'>
                        ".(!empty($event['imageUrl']) ? 
                           "<img src='".htmlspecialchars($event['imageUrl'])."' alt='Preview' style='max-width: 60px; margin-top: 5px;'>" 
                           : "")."
                      </td>";
                        
                        // Places totales
                        echo "<td><input type='number' name='totalSeats' value='{$event['totalSeats']}' 
                                  required min='20' max='50'></td>";
                        
                        // Places réservées
                        echo "<td><input type='number' name='reservedSeats' value='{$event['reservedSeats']}' 
                                  required min='0'></td>";
                        
                        echo "<td><button type='submit' name='action' value='modif' class='btn btn-primary'>Enregistrer</button></td>";
                        echo "</form>";
                    } else {
                        echo "<td>{$event['category']}</td>";
                        echo "<td>{$event['name']}</td>";
                        echo "<td>{$event['description']}</td>";
                        echo "<td>{$event['price']}</td>";
                        echo "<td>{$event['duration']}</td>";
                        echo "<td>{$event['date']}</td>";
                        echo "<td>{$event['location']}</td>";
                        echo "<td>";
                        if (!empty($event['imageUrl'])) {
                            echo "<img src='{$event['imageUrl']}' alt='Image événement' style='max-width: 100px; max-height: 60px; object-fit: cover;'>";
                        } else {
                            echo "Aucune image";
                        }
                        echo "</td>";                        echo "<td>{$event['totalSeats']}</td>";
                        echo "<td>{$event['reservedSeats']}</td>";
                        echo "<td class='action-buttons'>
                        <form method='post'>
                            <input type='hidden' name='edit_id' value='{$event['id']}'>
                            <button type='submit' name='action' value='edit' class='btn btn-primary'>Modifier</button>
                        </form>
                        <form method='post'>
                            <input type='hidden' name='id' value='{$event['id']}'>
                            <button type='submit' name='action' value='supp' class='btn btn-danger'>Supprimer</button>
                        </form>
                        <button onclick=\"document.getElementById('eventFilter').value = {$event['id']}; loadSeatsForEvent({$event['id']})\" class='btn btn-info'>Voir chaises</button>
                      </td>";
                    }
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
            </div>

        </div>
        <div class="seat-management">
    <h2>Gestion des chaises</h2>
    <div class="seat-controls">
        <select id="eventFilter" onchange="loadSeatsForEvent(this.value)">
            <option value="">Sélectionner un événement</option>
            <?php
            $events = $controller->getEvents();
            foreach ($events as $event) {
                echo "<option value='{$event['id']}'>" . htmlspecialchars($event['name']) . "</option>";
            }
            ?>
        </select>
        <select id="statusFilter" onchange="filterSeats()">
            <option value="all">Tous les statuts</option>
            <option value="libre">Libre</option>
            <option value="reserve">Réservé</option>
        </select>
        <div class="seat-stats" id="seatStats"></div>
    </div>
    <div class="seat-grid-container">
        <div class="seat-grid" id="seatGrid"></div>
    </div>
</div>

<!-- Modale pour les détails de la chaise -->
<div id="seatModal" class="modal">
    <div class="modal-content">
        <span class="close">×</span>
        <h3>Détails de la chaise</h3>
        <div id="seatDetails"></div>
    </div>
</div>
        <!-- Panneau latéral droit -->
        <div class="add-panel" id="addPanel">
            <button class="close-panel" id="closePanel">&times;</button>
            <h3>Ajouter un événement</h3>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="form-group">
    <label for="category">Catégorie :</label>
    <select id="category" name="category" required>
    <option value="">Sélectionnez...</option>
    <option value="sportif">Événements sportifs</option>
    <option value="culturel">Festivals culturels</option>
    <option value="culinaire">Festivals culinaires</option>
    <option value="musique">Festivals de musique</option>
    <option value="charite">Galas de charité</option>
</select>
</div>

<div class="form-group">
    <label for="name">Nom :</label>
    <input type="text" name="name" id="name" required minlength="3">
    <span id="nameError" class="error-message"></span>
</div>

<div class="form-group">
    <label for="description">Description :</label>
    <textarea name="description" id="description" required maxlength="3000"></textarea>
    <span id="descriptionError" class="error-message"></span>
</div>

<div class="form-group">
    <label for="price">Prix :</label>
    <input type="number" step="0.01" name="price" id="price" required min="0">
    <span id="priceError" class="error-message"></span>
</div>

<div class="form-group">
    <label for="duration">Durée :</label>
    <input type="number" name="duration" id="duration" required min="1" max="8">
    <span id="durationError" class="error-message"></span>
</div>

<div class="form-group">
    <label for="date">Date :</label>
    <input type="date" name="date" id="date" required>
    <span id="dateError" class="error-message"></span>
</div>

<div class="form-group">
    <label for="location">Lieu :</label>
    <input type="text" name="location" id="location" required minlength="3" pattern="^[^\d]+$">
    <span id="locationError" class="error-message"></span>
</div>

<div class="form-group">
    <label for="imageUrl">URL de l'image :</label>
    <input type="text" name="imageUrl" id="imageUrl" minlength="3" pattern=".*/.*">
    <span id="imageUrlError" class="error-message"></span>
</div>

<div class="form-group">
    <label for="totalSeats">Places totales :</label>
    <input type="number" name="totalSeats" id="totalSeats" required min="20" max="50">
    <span id="totalSeatsError" class="error-message"></span>
</div>


                <button type="submit" name="action" value="add" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>

        <!-- Overlay -->
        <div class="overlay" id="overlay"></div>
    </div>

    <script>
        // Gestion de l'ouverture/fermeture du panneau
        const openPanelBtn = document.getElementById('openPanel');
        const closePanelBtn = document.getElementById('closePanel');
        const addPanel = document.getElementById('addPanel');
        const overlay = document.getElementById('overlay');

        openPanelBtn.addEventListener('click', () => {
            addPanel.classList.add('active');
            overlay.classList.add('active');
        });

        closePanelBtn.addEventListener('click', () => {
            addPanel.classList.remove('active');
            overlay.classList.remove('active');
        });

        overlay.addEventListener('click', () => {
            addPanel.classList.remove('active');
            overlay.classList.remove('active');
        });
    </script>
   <script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleTableBtn');
    const eventTable = document.getElementById('eventTable');
    
    // Vérifie si les éléments existent
    if(toggleBtn && eventTable) {
        toggleBtn.addEventListener('click', function() {
            eventTable.classList.toggle('collapsed');
            
            // Change l'icône et le texte du bouton
            const icon = this.querySelector('i');
            if(eventTable.classList.contains('collapsed')) {
                icon.textContent = '▲';
                this.innerHTML = '<i>▲</i> Étendre le tableau';
            } else {
                icon.textContent = '▼';
                this.innerHTML = '<i>▼</i> Réduire le tableau';
            }
        });
    } else {
        console.error('Éléments introuvables pour la fonctionnalité de réduction du tableau');
    }
});
document.addEventListener("DOMContentLoaded", function() {
    // Configuration de la date minimale (aujourd'hui)
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date').min = today;

    // Fonctions de validation
    function validateName() {
        const name = document.getElementById('name').value.trim();
        const errorElement = document.getElementById('nameError');
        
        if (name.length < 3) {
            showError(errorElement, "Le nom doit contenir au moins 3 caractères");
            return false;
        } else {
            showValid(errorElement);
            return true;
        }
    }

    function validatePrice() {
        const price = parseFloat(document.getElementById('price').value);
        const errorElement = document.getElementById('priceError');
        
        if (isNaN(price) || price <= 0) {
            showError(errorElement, "Le prix doit être positif");
            return false;
        } else {
            showValid(errorElement);
            return true;
        }
    }

    function validateImageUrl() {
        const imageUrl = document.getElementById('imageUrl').value.trim();
        const errorElement = document.getElementById('imageUrlError');
        
        if (imageUrl && (imageUrl.length < 3 || !imageUrl.includes('/'))) {
            showError(errorElement, "L'image doit contenir au moins 3 caractères et un /");
            return false;
        } else {
            showValid(errorElement);
            return true;
        }
    }

    function validateDuration() {
        const duration = parseInt(document.getElementById('duration').value);
        const errorElement = document.getElementById('durationError');
        
        if (isNaN(duration) || duration <= 0 || duration > 8) {
            showError(errorElement, "La durée doit être positive et ≤ 8 heures");
            return false;
        } else {
            showValid(errorElement);
            return true;
        }
    }

    function validateLocation() {
        const location = document.getElementById('location').value.trim();
        const errorElement = document.getElementById('locationError');
        const hasNumber = /\d/.test(location);
        
        if (location.length < 3 || hasNumber) {
            showError(errorElement, "Le lieu doit contenir ≥3 caractères sans chiffres");
            return false;
        } else {
            showValid(errorElement);
            return true;
        }
    }

    function validateDate() {
        const dateInput = document.getElementById('date').value;
        const selectedDate = new Date(dateInput);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const errorElement = document.getElementById('dateError');
        
        if (!dateInput || selectedDate <= today) {
            showError(errorElement, "La date doit être dans le futur");
            return false;
        } else {
            showValid(errorElement);
            return true;
        }
    }

    function validateTotalSeats() {
        const seats = parseInt(document.getElementById('totalSeats').value);
        const errorElement = document.getElementById('totalSeatsError');
        
        if (isNaN(seats) || seats < 20 || seats > 50) {
            showError(errorElement, "Le nombre de places doit être entre 20 et 50");
            return false;
        } else {
            showValid(errorElement);
            return true;
        }
    }

    function validateDescription() {
        const description = document.getElementById('description').value.trim();
        const words = description.split(/\s+/).filter(word => word.length > 0);
        const errorElement = document.getElementById('descriptionError');
        
        if (words.length > 300) {
            showError(errorElement, "La description ne doit pas dépasser 300 mots");
            return false;
        } else {
            showValid(errorElement);
            return true;
        }
    }

    // Fonctions utilitaires
    function showError(element, message) {
        element.textContent = message;
        element.classList.add("error");
        element.classList.remove("valid");
    }

    function showValid(element) {
        element.textContent = "✓ Valide";
        element.classList.add("valid");
        element.classList.remove("error");
    }

    // Écouteurs d'événements
    document.getElementById('name').addEventListener('input', validateName);
    document.getElementById('price').addEventListener('input', validatePrice);
    document.getElementById('imageUrl').addEventListener('input', validateImageUrl);
    document.getElementById('duration').addEventListener('input', validateDuration);
    document.getElementById('location').addEventListener('input', validateLocation);
    document.getElementById('date').addEventListener('change', validateDate);
    document.getElementById('totalSeats').addEventListener('input', validateTotalSeats);
    document.getElementById('description').addEventListener('input', validateDescription);

    // Validation à la soumission
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const isValid = validateName() && validatePrice() && validateImageUrl() && 
                       validateDuration() && validateLocation() && validateDate() && 
                       validateTotalSeats() && validateDescription() ;

        if (isValid) {
            this.submit();
        }
    });
});
document.addEventListener('DOMContentLoaded', function() {
    // Définir la date minimale pour tous les champs date
    const today = new Date().toISOString().split('T')[0];
    document.querySelectorAll('table input[type="date"]').forEach(dateInput => {
        dateInput.min = today;
    });

    // Validation à la soumission pour les formulaires dans le tableau
    document.querySelectorAll('table form').forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = this.querySelectorAll('input');
            
            inputs.forEach(input => {
                if (!input.checkValidity()) {
                    isValid = false;
                    // Animation pour le premier champ invalide
                    if (!isValid) {
                        input.focus();
                        input.style.animation = 'shake 0.5s';
                        setTimeout(() => input.style.animation = '', 500);
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
});
</script>
<script>
// Global variables
let currentChaises = [];
let currentChaiseId = null;
let selectedSeat = null;

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeSeatManagement();
    setupEventListeners();
    
    // Load seats for first event if available
    const eventFilter = document.getElementById('eventFilter');
    if (eventFilter.options.length > 1) {
        eventFilter.value = eventFilter.options[1].value;
        loadSeatsForEvent(eventFilter.value);
    }
});

function initializeSeatManagement() {
    // Add loading animation CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .status-libre { color: #2ecc71; font-weight: bold; }
        .status-reserve { color: #e74c3c; font-weight: bold; }
        .error-message { color: #e74c3c; text-align: center; padding: 20px; }
        .no-seats { text-align: center; color: #7f8c8d; padding: 20px; }
        .stats-container { display: flex; gap: 15px; flex-wrap: wrap; align-items: center; }
        .stat-item { text-align: center; padding: 5px 10px; }
        .stat-value { font-size: 1.2rem; font-weight: bold; display: block; }
        .stat-label { font-size: 0.8rem; color: #7f8c8d; }
        .progress-container {
            flex-grow: 1;
            height: 8px;
            background: #ecf0f1;
            border-radius: 4px;
            overflow: hidden;
            margin: 0 10px;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #e74c3c, #3498db);
            transition: width 0.5s;
        }
        .percentage { font-size: 0.9rem; color: #7f8c8d; }
    `;
    document.head.appendChild(style);
}

function setupEventListeners() {
    // Modal controls
    document.querySelector('.modal .close').addEventListener('click', closeModal);
    window.addEventListener('click', (event) => {
        if (event.target === document.getElementById('seatModal')) {
            closeModal();
        }
    });
    
    // Event filter change
    document.getElementById('eventFilter').addEventListener('change', function() {
        loadSeatsForEvent(this.value);
    });
    
    // Status filter change
    document.getElementById('statusFilter').addEventListener('change', filterSeats);
}

// Main functions
function loadSeatsForEvent(eventId) {
    const grid = document.getElementById('seatGrid');
    const stats = document.getElementById('seatStats');
    
    if (!eventId) {
        grid.innerHTML = '<p class="no-seats">Sélectionnez un événement</p>';
        stats.innerHTML = '';
        return;
    }

    showLoading(grid);
    
    fetch(`/projetWeb/mvcEvent/get_chaises.php?event_id=${eventId}`)
        .then(handleResponse)
        .then(data => {
            if (data.status === 'success') {
                currentChaises = data.chaises;
                updateSeatGrid();
                updateSeatStats(data.stats);
                animateSeats();
            } else {
                throw new Error(data.message || 'Erreur serveur');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            grid.innerHTML = `<p class="error-message">${error.message}</p>`;
        });
}

function handleResponse(response) {
    if (!response.ok) {
        if (response.status === 404) {
            throw new Error('Service indisponible. Veuillez réessayer plus tard.');
        }
        throw new Error(`Erreur HTTP: ${response.status}`);
    }
    return response.json();
}

function showLoading(element) {
    element.innerHTML = `
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100px;">
            <div class="loading-spinner" style="
                border: 4px solid rgba(52, 152, 219, 0.1);
                border-radius: 50%;
                border-top: 4px solid #3498db;
                width: 40px;
                height: 40px;
                animation: spin 1s linear infinite;
            "></div>
            <p style="margin-top: 10px; color: #3498db;">Chargement...</p>
        </div>
    `;
}

function updateSeatGrid() {
    const grid = document.getElementById('seatGrid');
    const statusFilter = document.getElementById('statusFilter').value;
    
    grid.innerHTML = '';
    
    if (currentChaises.length === 0) {
        grid.innerHTML = '<p class="no-seats">Aucune chaise disponible pour cet événement</p>';
        return;
    }

    currentChaises.forEach(chaise => {
        if (statusFilter === 'all' || chaise.statut === statusFilter) {
            const seatEl = createSeatElement(chaise);
            grid.appendChild(seatEl);
        }
    });
}

function createSeatElement(chaise) {
    const seatEl = document.createElement('div');
    seatEl.className = `seat ${chaise.statut}`;
    seatEl.textContent = chaise.numero;
    seatEl.dataset.id = chaise.id;
    
    // Add tooltip
    seatEl.title = `Chaise #${chaise.numero} - ${chaise.statut === 'libre' ? 'Disponible' : 'Réservée'}`;
    
    seatEl.addEventListener('click', () => {
        if (selectedSeat) {
            selectedSeat.classList.remove('selected');
        }
        selectedSeat = seatEl;
        selectedSeat.classList.add('selected');
        showSeatDetails(chaise);
    });
    
    return seatEl;
}

function animateSeats() {
    setTimeout(() => {
        document.querySelectorAll('.seat').forEach((seat, index) => {
            seat.style.opacity = '0';
            seat.style.transform = 'translateY(20px)';
            seat.style.transition = 'none';
            
            setTimeout(() => {
                seat.style.transition = 'opacity 0.3s, transform 0.3s';
                seat.style.transitionDelay = `${index * 0.05}s`;
                seat.style.opacity = '1';
                seat.style.transform = 'translateY(0)';
            }, 50);
        });
    }, 100);
}

function updateSeatStats(stats) {
    const total = stats.total;
    const reserved = stats.reserved;
    const free = total - reserved;
    const percentage = total > 0 ? Math.round((reserved / total) * 100) : 0;
    
    document.getElementById('seatStats').innerHTML = `
        <div class="stats-container">
            <div class="stat-item">
                <span class="stat-value">${total}</span>
                <span class="stat-label">Total</span>
            </div>
            <div class="stat-item">
                <span class="stat-value" style="color: #e74c3c">${reserved}</span>
                <span class="stat-label">Réservées</span>
            </div>
            <div class="stat-item">
                <span class="stat-value" style="color: #2ecc71">${free}</span>
                <span class="stat-label">Libres</span>
            </div>
            <div class="progress-container">
                <div class="progress-bar" style="width: ${percentage}%"></div>
            </div>
            <span class="percentage">${percentage}% réservé</span>
        </div>
    `;
}

function filterSeats() {
    updateSeatGrid();
}

// Modal functions
function showSeatDetails(chaise) {
    currentChaiseId = chaise.id;
    const modal = document.getElementById('seatModal');
    const details = document.getElementById('seatDetails');
    
    details.innerHTML = `
        <p><strong>ID:</strong> <span>${chaise.id}</span></p>
        <p><strong>Numéro:</strong> <span>${chaise.numero}</span></p>
        <p><strong>Statut:</strong> <span class="status-${chaise.statut}">${chaise.statut === 'libre' ? 'Disponible' : 'Réservée'}</span></p>
        <p><strong>Utilisateur:</strong> <span>${chaise.id_user || 'Aucun'}</span></p>
    `;
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('seatModal');
    modal.style.animation = 'fadeOut 0.3s';
    setTimeout(() => {
        modal.style.display = 'none';
        modal.style.animation = '';
        document.body.style.overflow = '';
    }, 300);
}
</script>
</body>
</html>