<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier</title>
</head>
<body>
<h1>Mon panier</h1>
<div id="panier"></div>

<script>
    fetch("get_cart.php")
        .then(res => res.json())
        .then(panier => {
            const div = document.getElementById("panier");
            if (panier.error) {
                div.innerHTML = "<p>" + panier.error + "</p>";
                return;
            }

            if (panier.length === 0) {
                div.innerHTML = "<p>Votre panier est vide.</p>";
                return;
            }

            let html = "<ul>";
            panier.forEach(item => {
                html += `
            <li>
              <strong>${item.product_name}</strong><br>
              Taille : ${item.size}, Couleur : ${item.color}<br>
              Quantité : ${item.quantity}, Prix : ${item.price} €<br>
              <img src="images/${item.image}" width="100"><br><br>
            </li>`;
            });
            html += "</ul>";
            div.innerHTML = html;
        });
</script>
</body>
</html>
