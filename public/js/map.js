document.addEventListener("DOMContentLoaded", () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(async (position) => {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;
  
        var map = L.map('map').setView([lat, lon], 6);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        
        fetch('hotels_data.json')
        .then(response => response.json())
        .then(data => {
          data.forEach(hotel => {
            const { x, y, title } = hotel;
    
            L.marker([x, y])
              .addTo(map)
              .bindPopup(title)
              .openPopup();
          });
        })
        .catch(error => console.error('Error loading hotel data:', error));
      });
    }
  });