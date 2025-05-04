const ACCESS_KEY = "j9a9z5y6pypWDoDwlhCDGqpHzK-IY29XI1pMfKRqolM";

function displayImages(photos) {
  const results = document.getElementById("results");
  results.innerHTML = "";
  photos.forEach(photo => {
    const img = document.createElement("img");
    img.src = photo.urls.small;
    img.alt = photo.alt_description || "Unsplash";
    img.onclick = () => downloadImage(photo.urls.full);
    results.appendChild(img);
  });
}

function searchPhotos() {
  const query = document.getElementById("searchInput").value;
  fetch(`https://api.unsplash.com/search/photos?query=${query}&per_page=6&client_id=${ACCESS_KEY}`)
    .then(res => res.json())
    .then(data => displayImages(data.results));
}

function getRandom() {
  fetch(`https://api.unsplash.com/photos/random?count=6&client_id=${ACCESS_KEY}`)
    .then(res => res.json())
    .then(data => displayImages(data));
}

function getLatest() {
  fetch(`https://api.unsplash.com/photos?per_page=6&order_by=latest&client_id=${ACCESS_KEY}`)
    .then(res => res.json())
    .then(data => displayImages(data));
}

function downloadImage(url) {
  fetch("http://127.0.0.1:5000/telecharger", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ image_url: url })
  })
    .then(res => res.json())
    .then(data => {
      console.log("✅", data.message || data.error);
      alert(data.message || data.error);
    })
    .catch(err => {
      console.error("❌ Erreur de connexion au serveur Python :", err);
      alert("⚠️ Problème de connexion au serveur Python !");
    });
}


function displayImages(photos) {
  const results = document.getElementById("results");
  results.innerHTML = "";

  photos.forEach(photo => {
    const container = document.createElement("div");
    container.className = "image-container";

    const img = document.createElement("img");
    img.src = photo.urls.small;
    img.alt = photo.alt_description || "Image Unsplash";

    const button = document.createElement("button");
    button.className = "download-button";
    button.innerText = "Télécharger";
    button.onclick = () => downloadImage(photo.urls.full);

    container.appendChild(img);
    container.appendChild(button);
    results.appendChild(container);
  });
}
