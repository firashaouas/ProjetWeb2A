<?php
require_once '../../Controller/EventController.php';

$controller = new EventController();
$editId = $_POST['edit_id'] ?? null;

// Check for server-side errors
session_start();
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);
if (isset($_SESSION['form_data'])) {
    $formData = $_SESSION['form_data'];
    unset($_SESSION['form_data']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    error_log('POST request received with action: ' . $action . ', data: ' . json_encode($_POST));

    try {
        switch ($action) {
            case 'add':
                $event = new Event(
                    $_POST['category'],
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['duration'],
                    $_POST['date'],
                    $_POST['longitude'],
                    $_POST['latitude'],
                    $_POST['place_name'],
                    $_POST['imageUrl'],
                    $_POST['totalSeats'],
                    $_POST['reservedSeats'] ?? 0
                );
                $eventId = $controller->addEvent($event);
                error_log('Event added successfully, redirecting...');
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
                    $_POST['longitude'],
                    $_POST['latitude'],
                    $_POST['place_name'],
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
    } catch (Exception $e) {
        error_log('Error handling POST request: ' . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de l'opération : " . $e->getMessage();
        $_SESSION['form_data'] = $_POST; // Save form data

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
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css' rel='stylesheet' />
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

        /* Mapbox styles */
        #map {
            height: 300px;
            width: 100%;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .mapboxgl-ctrl-geocoder {
            width: 100%;
            max-width: none;
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

        /* Error message style */
        .error-message-display {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px;
            text-align: center;
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
            <div style="display: flex; gap: 15px;">
                <div class="search">
                    <input type="text" id="searchInput" placeholder="Rechercher..." style="border: none; background: transparent; width: 100%;">
                </div>
                <select id="sortSelect" style="padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                    <option value="date_asc">Trier par date (croissant)</option>
                    <option value="date_desc">Trier par date (décroissant)</option>
                    <option value="name_asc">Trier par nom (A-Z)</option>
                    <option value="name_desc">Trier par nom (Z-A)</option>
                    <option value="price_asc">Trier par prix (croissant)</option>
                    <option value="price_desc">Trier par prix (décroissant)</option>
                </select>
                <button class="open-panel-btn" id="openPanel">+ Ajouter un événement</button>
            </div>
        </div>

        <?php if ($errorMessage): ?>
            <div class="error-message-display">
                <p><?= htmlspecialchars($errorMessage) ?></p>
            </div>
        <?php endif; ?>

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
                            
                            echo "<td><input type='text' name='category' value='".htmlspecialchars($event['category'])."' 
                                      required minlength='3'></td>";
                            echo "<td><input type='text' name='name' value='".htmlspecialchars($event['name'])."' 
                                      required minlength='3'></td>";
                            echo "<td><input type='text' name='description' value='".htmlspecialchars($event['description'])."' 
                                      required></td>";
                            echo "<td><input type='number' step='0.01' name='price' value='{$event['price']}' 
                                      required min='0'></td>";
                            echo "<td><input type='number' name='duration' value='{$event['duration']}' 
                                      required min='1' max='8'></td>";
                            echo "<td><input type='date' name='date' value='{$event['date']}' 
                                      required min='".date('Y-m-d')."'></td>";
                            echo "<td>
                                <div id='map-{$event['id']}' style='height: 200px;'></div>
                                <input type='hidden' name='longitude' id='edit_longitude_{$event['id']}' value='{$event['longitude']}'>
                                <input type='hidden' name='latitude' id='edit_latitude_{$event['id']}' value='{$event['latitude']}'>
                                <input type='hidden' name='place_name' id='edit_place_name_{$event['id']}' value='".htmlspecialchars($event['place_name'])."'>
                                <div id='edit_geocoder_{$event['id']}' class='geocoder'></div>
                                <span id='edit_locationError_{$event['id']}' class='error-message'></span>
                            </td>";
                            echo "<td>
                                <input type='text' name='imageUrl' value='".htmlspecialchars($event['imageUrl'])."' 
                                     minlength='3' pattern='.*/.*'>
                                ".(!empty($event['imageUrl']) ? 
                                   "<img src='".htmlspecialchars($event['imageUrl'])."' alt='Preview' style='max-width: 60px; margin-top: 5px;'>" 
                                   : "")."
                              </td>";
                            echo "<td><input type='number' name='totalSeats' value='{$event['totalSeats']}' 
                                      required min='20' max='50'></td>";
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
                            echo "<td>".htmlspecialchars($event['place_name'])."</td>";
                            echo "<td>";
                            if (!empty($event['imageUrl'])) {
                                echo "<img src='{$event['imageUrl']}' alt='Image événement' style='max-width: 100px; max-height: 60px; object-fit: cover;'>";
                            } else {
                                echo "Aucune image";
                            }
                            echo "</td>";
                            echo "<td>{$event['totalSeats']}</td>";
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
                <select id="eventFilter">
                    <option value="">Sélectionner un événement</option>
                    <?php
                    $events = $controller->getEvents();
                    foreach ($events as $event) {
                        echo "<option value='{$event['id']}'>" . htmlspecialchars($event['name']) . "</option>";
                    }
                    ?>
                </select>
                <select id="statusFilter">
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

        <!-- Modal for seat details -->
        <div id="seatModal" class="modal">
            <div class="modal-content">
                <span class="close">×</span>
                <h3>Détails de la chaise</h3>
                <div id="seatDetails"></div>
            </div>
        </div>

        <!-- Add event panel -->
        <div class="add-panel" id="addPanel">
            <button class="close-panel" id="closePanel">×</button>
            <h3>Ajouter un événement</h3>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="eventForm">
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
                    <div id="map" style="height: 300px;"></div>
                    <div id="geocoder" class="geocoder"></div>
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="place_name" id="place_name">
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
                <button type="submit" name="action" value="add" class="btn btn-primary" id="submitEventBtn" disabled>Enregistrer</button>
            </form>
        </div>

        <!-- Overlay -->
        <div class="overlay" id="overlay"></div>
    </div>

    <script src='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js'></script>
    <script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js'></script>
   <script>// Mapbox access token
mapboxgl.accessToken = '<?php echo "pk.eyJ1IjoiaGFvYXVzMDEiLCJhIjoiY21hOHhqcGttMWJ5NjJtczg3eGJxazM0MiJ9.lm0YeqM7TkpDT4r6_Pf6aw"; ?>';

// Utility functions
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

// Seat management variables
let currentChaises = [];
let currentChaiseId = null;
let selectedSeat = null;

// Seat management functions
function loadSeatsForEvent(eventId) {
    const grid = document.getElementById('seatGrid');
    const stats = document.getElementById('seatStats');
    if (!grid || !stats) {
        console.error('Seat grid or stats element not found');
        return;
    }
    if (!eventId) {
        grid.innerHTML = '<p class="no-seats">Sélectionnez un événement</p>';
        stats.innerHTML = '';
        console.log('No event ID provided');
        return;
    }
    showLoading(grid);
    console.log('Fetching seats for event ID:', eventId);
    fetch(`/projetWeb/mvcEvent/get_chaises.php?event_id=${eventId}`)
        .then(response => {
            console.log('Fetch response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Fetch data received:', data);
            if (data.status === 'success') {
                currentChaises = data.chaises || [];
                updateSeatGrid();
                updateSeatStats(data.stats || { total: 0, reserved: 0 });
                animateSeats();
            } else {
                throw new Error(data.message || 'Erreur serveur');
            }
        })
        .catch(error => {
            console.error('Error loading seats:', error);
            grid.innerHTML = `<p class="error-message">Erreur : ${error.message}</p>`;
            stats.innerHTML = '';
        });
}

function updateSeatGrid() {
    const grid = document.getElementById('seatGrid');
    const statusFilter = document.getElementById('statusFilter')?.value || 'all';

    grid.innerHTML = '';

    if (!currentChaises.length) {
        grid.innerHTML = '<p class="no-seats">Aucune chaise disponible pour cet événement</p>';
        console.log('No chaises available');
        return;
    }

    currentChaises.forEach(chaise => {
        if (statusFilter === 'all' || chaise.statut === statusFilter) {
            const seatEl = createSeatElement(chaise);
            grid.appendChild(seatEl);
        }
    });
    console.log('Seat grid updated with', currentChaises.length, 'chaises');
}

function createSeatElement(chaise) {
    const seatEl = document.createElement('div');
    seatEl.className = `seat ${chaise.statut}`;
    seatEl.textContent = chaise.numero;
    seatEl.dataset.id = chaise.id;

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
    const statsElement = document.getElementById('seatStats');
    if (!statsElement) {
        console.error('Seat stats element not found');
        return;
    }

    const total = stats.total || 0;
    const reserved = stats.reserved || 0;
    const free = total - reserved;
    const percentage = total > 0 ? Math.round((reserved / total) * 100) : 0;

    statsElement.innerHTML = `
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
    console.log('Seat stats updated:', { total, reserved, free, percentage });
}

function filterSeats() {
    console.log('Filtering seats by status:', document.getElementById('statusFilter').value);
    updateSeatGrid();
}

function showSeatDetails(chaise) {
    currentChaiseId = chaise.id;
    const modal = document.getElementById('seatModal');
    const details = document.getElementById('seatDetails');

    if (!modal || !details) {
        console.error('Modal or details element not found');
        return;
    }

    details.innerHTML = `
        <p><strong>ID:</strong> <span>${chaise.id}</span></p>
        <p><strong>Numéro:</strong> <span>${chaise.numero}</span></p>
        <p><strong>Statut:</strong> <span class="status-${chaise.statut}">${chaise.statut === 'libre' ? 'Disponible' : 'Réservée'}</span></p>
        <p><strong>Utilisateur:</strong> <span>${chaise.id_user || 'Aucun'}</span></p>
    `;

    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    console.log('Showing seat details for chaise:', chaise);
}

function closeModal() {
    const modal = document.getElementById('seatModal');
    if (!modal) {
        console.error('Modal element not found');
        return;
    }

    modal.style.animation = 'fadeOut 0.3s';
    setTimeout(() => {
        modal.style.display = 'none';
        modal.style.animation = '';
        document.body.style.overflow = '';
    }, 300);
    console.log('Modal closed');
}

function initializeSeatManagement() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .seat { cursor: pointer; padding: 10px; margin: 5px; border-radius: 5px; }
        .seat.libre { background: #2ecc71; color: white; }
        .seat.reserve { background: #e74c3c; color: white; }
        .seat.selected { border: 2px solid #3498db; }
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
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-10px); }
            40%, 80% { transform: translateX(10px); }
        }
    `;
    document.head.appendChild(style);
}

// Form validation and other dashboard functionality
document.addEventListener("DOMContentLoaded", function() {
    console.log('Initializing dashboard...');
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date').min = today;

    let isLocationSelected = false;

    // Initialize map for add event form
    let map, geocoder;
    try {
        map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [10.1815, 36.8065],
            zoom: 10
        });
        geocoder = new MapboxGeocoder({
            accessToken: mapboxgl.accessToken,
            mapboxgl: mapboxgl,
            placeholder: 'Rechercher un lieu...',
            marker: { color: '#C83EFC' }
        });
        document.getElementById('geocoder').appendChild(geocoder.onAdd(map));

        map.on('styleimagemissing', (e) => {
            const imageId = e.id;
            console.warn(`Missing image: ${imageId}`);
            map.addImage(imageId, {
                width: 20,
                height: 20,
                data: new Uint8Array(20 * 20 * 4)
            });
        });

        geocoder.on('result', function(e) {
            const coords = e.result.geometry.coordinates;
            document.getElementById('longitude').value = coords[0];
            document.getElementById('latitude').value = coords[1];
            document.getElementById('place_name').value = e.result.place_name;
            isLocationSelected = true;
            console.log('Geocoder result:', { longitude: coords[0], latitude: coords[1], place_name: e.result.place_name });
            validateLocation();
            updateSubmitButton();
        });

        geocoder.on('error', function(error) {
            console.error('Geocoder error:', error);
            document.getElementById('locationError').textContent = 'Erreur lors de la recherche du lieu. Veuillez réessayer.';
            isLocationSelected = false;
            updateSubmitButton();
        });
    } catch (error) {
        console.error('Mapbox initialization failed:', error);
        document.getElementById('locationError').textContent = 'Erreur de chargement de la carte. Vérifiez votre connexion réseau ou la clé API Mapbox.';
    }

    // Form validation functions
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
        
        if (isNaN(price) || price < 0) {
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
        const longitude = document.getElementById('longitude').value;
        const latitude = document.getElementById('latitude').value;
        const placeName = document.getElementById('place_name').value;
        const errorElement = document.getElementById('locationError');

        if (!isLocationSelected) {
            showError(errorElement, "Veuillez sélectionner un lieu à l'aide de la recherche.");
            return false;
        }

        if (!longitude || !latitude || !placeName) {
            showError(errorElement, "Les coordonnées du lieu sont manquantes.");
            return false;
        }

        if (isNaN(parseFloat(longitude)) || isNaN(parseFloat(latitude))) {
            showError(errorElement, "Les coordonnées GPS sont invalides.");
            return false;
        }

        if (placeName.length < 3) {
            showError(errorElement, "Le nom du lieu doit contenir au moins 3 caractères.");
            return false;
        }

        showValid(errorElement);
        return true;
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

    function validateCategory() {
        const category = document.getElementById('category').value;
        return category !== "";
    }

    function updateSubmitButton() {
        const submitButton = document.getElementById('submitEventBtn');
        const isValid = validateName() && validatePrice() && validateImageUrl() && 
                       validateDuration() && validateLocation() && validateDate() && 
                       validateTotalSeats() && validateDescription() && validateCategory();
        submitButton.disabled = !isValid;
    }

    // Add event listeners for form inputs
    document.getElementById('name').addEventListener('input', () => { validateName(); updateSubmitButton(); });
    document.getElementById('price').addEventListener('input', () => { validatePrice(); updateSubmitButton(); });
    document.getElementById('imageUrl').addEventListener('input', () => { validateImageUrl(); updateSubmitButton(); });
    document.getElementById('duration').addEventListener('input', () => { validateDuration(); updateSubmitButton(); });
    document.getElementById('date').addEventListener('change', () => { validateDate(); updateSubmitButton(); });
    document.getElementById('totalSeats').addEventListener('input', () => { validateTotalSeats(); updateSubmitButton(); });
    document.getElementById('description').addEventListener('input', () => { validateDescription(); updateSubmitButton(); });
    document.getElementById('category').addEventListener('change', () => { updateSubmitButton(); });

    // Form submission validation
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        console.log('Form submission attempt:', Object.fromEntries(new FormData(this)));
        const isValid = validateName() && validatePrice() && validateImageUrl() && 
                       validateDuration() && validateLocation() && validateDate() && 
                       validateTotalSeats() && validateDescription() && validateCategory();

        if (!isValid) {
            e.preventDefault();
            console.error('Form validation failed:', {
                name: validateName(),
                price: validatePrice(),
                imageUrl: validateImageUrl(),
                duration: validateDuration(),
                location: validateLocation(),
                date: validateDate(),
                totalSeats: validateTotalSeats(),
                description: validateDescription(),
                category: validateCategory()
            });
        }
    });

    // Initialize map and geocoder for edit forms
    document.querySelectorAll('#eventTable form').forEach(form => {
        const idInput = form.querySelector('input[name="id"], input[name="edit_id"]');
        if (!idInput) {
            console.warn('Form missing input[name="id"] or input[name="edit_id"]:', form);
            return;
        }
        const eventId = idInput.value;

        const mapContainer = document.getElementById(`map-${eventId}`);
        if (!mapContainer) {
            return;
        }

        let isEditLocationSelected = false;

        let editMap;
        try {
            editMap = new mapboxgl.Map({
                container: `map-${eventId}`,
                style: 'mapbox://styles/mapbox/light-v10',
                center: [10.1815, 36.8065],
                zoom: 10
            });
        } catch (error) {
            console.error(`Failed to initialize map for event ${eventId}:`, error);
            form.querySelector(`#edit_locationError_${eventId}`).textContent = 'Erreur de chargement de la carte.';
            return;
        }

        const editGeocoder = new MapboxGeocoder({
            accessToken: mapboxgl.accessToken,
            mapboxgl: mapboxgl,
            placeholder: 'Rechercher un lieu...',
            marker: { color: '#C83EFC' }
        });

        try {
            form.querySelector(`#edit_geocoder_${eventId}`).appendChild(editGeocoder.onAdd(editMap));
        } catch (error) {
            console.error(`Error initializing geocoder for event ${eventId}:`, error);
            form.querySelector(`#edit_locationError_${eventId}`).textContent = 'Erreur de chargement du géocodeur.';
        }

        editGeocoder.on('result', function(e) {
            const coords = e.result.geometry.coordinates;
            form.querySelector(`#edit_longitude_${eventId}`).value = coords[0];
            form.querySelector(`#edit_latitude_${eventId}`).value = coords[1];
            form.querySelector(`#edit_place_name_${eventId}`).value = e.result.place_name;
            isEditLocationSelected = true;
            console.log(`Edit geocoder result for event ${eventId}:`, {
                longitude: coords[0],
                latitude: coords[1],
                place_name: e.result.place_name
            });
            validateEditLocation(eventId);
            updateEditSubmitButton(eventId);
        });

        editGeocoder.on('error', function(error) {
            console.error(`Edit geocoder error for event ${eventId}:`, error);
            form.querySelector(`#edit_locationError_${eventId}`).textContent = 'Erreur lors de la recherche du lieu. Veuillez réessayer.';
            isEditLocationSelected = false;
            updateEditSubmitButton(eventId);
        });

        function validateEditLocation(id) {
            const longitude = form.querySelector(`#edit_longitude_${id}`).value;
            const latitude = form.querySelector(`#edit_latitude_${id}`).value;
            const placeName = form.querySelector(`#edit_place_name_${id}`).value;
            const errorElement = form.querySelector(`#edit_locationError_${id}`);

            if (!isEditLocationSelected) {
                showError(errorElement, "Veuillez sélectionner un lieu à l'aide de la recherche.");
                return false;
            }

            if (!longitude || !latitude || !placeName) {
                showError(errorElement, "Les coordonnées du lieu sont manquantes.");
                return false;
            }

            if (isNaN(parseFloat(longitude)) || isNaN(parseFloat(latitude))) {
                showError(errorElement, "Les coordonnées GPS sont invalides.");
                return false;
            }

            if (placeName.length < 3) {
                showError(errorElement, "Le nom du lieu doit contenir au moins 3 caractères.");
                return false;
            }

            showValid(errorElement);
            return true;
        }

        function validateEditForm(id) {
            const inputs = form.querySelectorAll('input, select, textarea');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.checkValidity()) {
                    isValid = false;
                }
            });

            return isValid && validateEditLocation(id);
        }

        function updateEditSubmitButton(id) {
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = !validateEditForm(id);
        }

        form.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('input', () => updateEditSubmitButton(eventId));
            input.addEventListener('change', () => updateEditSubmitButton(eventId));
        });

        form.addEventListener('submit', function(e) {
            console.log(`Edit form submission attempt for event ${eventId}:`, Object.fromEntries(new FormData(this)));
            if (!validateEditForm(eventId)) {
                e.preventDefault();
                console.error(`Edit form validation failed for event ${eventId}`);
                form.querySelectorAll('input, select, textarea').forEach(input => {
                    if (!input.checkValidity()) {
                        input.focus();
                        input.style.animation = 'shake 0.5s';
                        setTimeout(() => input.style.animation = '', 500);
                    }
                });
            }
        });
    });

    // Panel controls
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

    // Table collapse
    const toggleBtn = document.getElementById('toggleTableBtn');
    const eventTable = document.getElementById('eventTable');
    
    if (toggleBtn && eventTable) {
        toggleBtn.addEventListener('click', function() {
            eventTable.classList.toggle('collapsed');
            const icon = this.querySelector('i');
            if (eventTable.classList.contains('collapsed')) {
                icon.textContent = '▲';
                this.innerHTML = '<i>▲</i> Étendre le tableau';
            } else {
                icon.textContent = '▼';
                this.innerHTML = '<i>▼</i> Réduire le tableau';
            }
        });
    }

    // Seat management initialization
    function setupEventListeners() {
        const modalClose = document.querySelector('.modal .close');
        if (modalClose) {
            modalClose.addEventListener('click', closeModal);
        } else {
            console.error('Modal close button not found');
        }

        window.addEventListener('click', (event) => {
            if (event.target === document.getElementById('seatModal')) {
                closeModal();
            }
        });

        const eventFilter = document.getElementById('eventFilter');
        if (eventFilter) {
            eventFilter.addEventListener('change', function() {
                console.log('Event filter changed to:', this.value);
                loadSeatsForEvent(this.value);
            });
        } else {
            console.error('Event filter element not found');
        }

        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', filterSeats);
        } else {
            console.error('Status filter element not found');
        }
    }

    // Search and sort
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');

    if (searchInput) {
        searchInput.addEventListener('input', filterAndSortEvents);
    } else {
        console.error('Search input element not found');
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', filterAndSortEvents);
    } else {
        console.error('Sort select element not found');
    }

    function filterAndSortEvents() {
        const searchInput = document.getElementById('searchInput');
        const sortSelect = document.getElementById('sortSelect');
        const searchTerm = searchInput?.value.toLowerCase() || '';
        const sortValue = sortSelect?.value || '';
        const rows = document.querySelectorAll('#eventTable tbody tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (!cells || cells.length < 4) {
                console.warn('Row skipped due to missing cells:', row);
                return;
            }

            const name = cells[2].textContent.toLowerCase();
            const description = cells[3].textContent.toLowerCase();

            const matchesSearch = name.includes(searchTerm) ||
                description.includes(searchTerm) ||
                cells[1].textContent.toLowerCase().includes(searchTerm);

            row.style.display = matchesSearch ? '' : 'none';
        });

        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');

        visibleRows.sort((a, b) => {
            const aCells = a.querySelectorAll('td');
            const bCells = b.querySelectorAll('td');

            if (!aCells || !bCells || aCells.length < 7 || bCells.length < 7) {
                console.warn('Rows skipped during sorting due to missing cells:', a, b);
                return 0;
            }

            switch (sortValue) {
                case 'date_asc':
                    return new Date(aCells[6].textContent) - new Date(bCells[6].textContent);
                case 'date_desc':
                    return new Date(bCells[6].textContent) - new Date(aCells[6].textContent);
                case 'name_asc':
                    return aCells[2].textContent.localeCompare(bCells[2].textContent);
                case 'name_desc':
                    return bCells[2].textContent.localeCompare(aCells[2].textContent);
                case 'price_asc':
                    return parseFloat(aCells[4].textContent) - parseFloat(bCells[4].textContent);
                case 'price_desc':
                    return parseFloat(bCells[4].textContent) - parseFloat(aCells[4].textContent);
                default:
                    return 0;
            }
        });

        const tbody = document.querySelector('#eventTable tbody');
        visibleRows.forEach(row => tbody.appendChild(row));
        console.log('Events filtered and sorted:', { searchTerm, sortValue });
    }

    // Initialize seat management
    initializeSeatManagement();
    setupEventListeners();

    const eventFilter = document.getElementById('eventFilter');
    if (eventFilter && eventFilter.options.length > 1) {
        eventFilter.value = eventFilter.options[1].value;
        loadSeatsForEvent(eventFilter.value);
    } else {
        console.error('Event filter not found or has insufficient options');
        const seatGrid = document.getElementById('seatGrid');
        if (seatGrid) {
            seatGrid.innerHTML = '<p class="error-message">Erreur : Aucun événement disponible.</p>';
        }
    }

    // Global error handler
    window.onerror = function (message, source, lineno, colno, error) {
        console.error(`Global error: ${message} at ${source}:${lineno}:${colno}`, error);

        const existingError = document.querySelector('.error-message-display');
        if (existingError) {
            existingError.remove();
        }

        document.body.insertAdjacentHTML('beforeend', `
            <div class="error-message-display">
                <p>Une erreur s'est produite. Veuillez réessayer ou contacter le support.</p>
            </div>
        `);
    };
});
</script>
</body>
</html>