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
                $_POST['reservedSeats']
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
                $_POST['reservedSeats']
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
    <link href="add.css" rel="stylesheet">
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
                              </td>";
                    }
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
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
</body>
</html>