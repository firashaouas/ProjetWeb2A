<?php
session_start(); // Placez ceci au tout début du fichier

// Affichage des messages de succès ou d'erreur après la redirection
if (isset($_GET['success'])): ?>
    <p style="color: green;">Commande passée avec succès !</p>
<?php elseif (isset($_GET['error'])): ?>
    <p style="color: red;">Erreur : <?= htmlspecialchars($_GET['error']) ?></p>
<?php endif; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>click'N'go - Validation d'achat</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;500&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(145deg, #ffeaf2, #d9e4ff);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .panier-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .panier-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: #333;
        }

        .cart-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table th {
            text-align: left;
            padding: 15px;
            background: #ff8fa3;
            color: white;
            font-weight: 500;
        }

        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .cart-table tr:last-child td {
            border-bottom: none;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }

        .cart-total {
            text-align: right;
            font-size: 20px;
            margin-top: 20px;
            font-weight: 500;
            color: #ff8fa3;
        }

        .checkout-btn {
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 300px;
            margin: 30px auto 0;
            display: block;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 143, 163, 0.3);
        }

        .checkout-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #ccc;
        }

        .empty-cart {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }

        .empty-cart p {
            margin: 20px 0;
            font-size: 18px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            background: #fff;
            border: 1px solid #ddd;
            width: 30px;
            height: 30px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: #ff8fa3;
            color: white;
            border-color: #ff8fa3;
        }

        .remove-btn {
            color: #ff6b6b;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            padding: 5px;
            margin-left: 10px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .cart-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="panier-header">
            <h1>Finalisez votre achat</h1>
            <span id="itemCount">0 articles</span>
        </div>

        <div class="cart-container">
            <table class="cart-table" id="panierTable">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Prix</th>
                        <th>Quantité</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="cart-total">
                Total à payer: <span id="totalPrix">0</span> TND
            </div>
            <form action="../../Controller/commandeController.php" method="post">
                
    <input type="hidden" name="action" value="ajouter_commande">
    <input type="hidden" id="panier_data" name="panier_data" value="">
    <button type="submit" class="checkout-btn">Valider l'achat</button>
</form>
        </div>
    </div>

    <script>
        const validerBtn = document.querySelector('.checkout-btn');
        const panierDataInput = document.getElementById('panier_data');
        let panier = JSON.parse(localStorage.getItem('panier')) || [];
        const tbody = document.querySelector('#panierTable tbody');
        const totalElement = document.getElementById('totalPrix');
        const itemCount = document.getElementById('itemCount');

        function updatePanierData() {
            panierDataInput.value = JSON.stringify(panier);
        }

        function afficherPanier() {
            tbody.innerHTML = '';
            let totalGlobal = 0;
            let nombreTotalArticles = 0;

            if (panier.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center;">Votre panier est vide.</td></tr>`;
                validerBtn.disabled = true;
                validerBtn.style.opacity = 0.5;
                validerBtn.style.cursor = 'not-allowed';
                itemCount.textContent = '0 articles';
                totalElement.textContent = '0.00';
                updatePanierData(); // Update the hidden input even if the cart is empty
                return;
            }

            panier.forEach((p, index) => {
                const total = p.quantite * p.prix;
                totalGlobal += total;
                nombreTotalArticles += p.quantite;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><img src="${p.image}" alt="${p.nom}" class="product-image">${p.nom}</td>
                    <td>${p.prix.toFixed(2)} TND</td>
                    <td>
                        <div class="quantity-control">
                            <button class="quantity-btn" onclick="modifierQuantite(${index}, -1)">-</button>
                            <span>${p.quantite}</span>
                            <button class="quantity-btn" onclick="modifierQuantite(${index}, 1)">+</button>
                        </div>
                    </td>
                    <td>${total.toFixed(2)} TND</td>
                    <td><button class="remove-btn" onclick="supprimerProduit(${index})">×</button></td>
                `;
                tbody.appendChild(row);
            });

            totalElement.textContent = totalGlobal.toFixed(2);
            itemCount.textContent = `${nombreTotalArticles} article${nombreTotalArticles > 1 ? 's' : ''}`;
            validerBtn.disabled = false; // Enable the button if there are items
            validerBtn.style.opacity = 1;
            validerBtn.style.cursor = 'pointer';
            updatePanierData(); // Update the hidden input with the current cart data
        }

        function modifierQuantite(index, changement) {
            panier[index].quantite += changement;
            if (panier[index].quantite < 1) {
                panier[index].quantite = 1;
            }
            localStorage.setItem('panier', JSON.stringify(panier));
            afficherPanier();
        }

        function supprimerProduit(index) {
            if (confirm("Voulez-vous vraiment supprimer ce produit de votre panier ?")) {
                panier.splice(index, 1);
                localStorage.setItem('panier', JSON.stringify(panier));
                afficherPanier();
            }
        }

        document.addEventListener('DOMContentLoaded', afficherPanier);
    </script>
</body>
</html>