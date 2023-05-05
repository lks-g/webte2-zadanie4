document.querySelectorAll('#D1S tr').forEach(tr => tr.onclick = e => {
    document.querySelector('#D2').classList.remove('hidden')

    const state = e.path.find(e => e.localName === 'tr').children[1].innerText
    fetch(`D2.php?state=${state}`).then(res => res.json()).then(data => {
        const tbody = document.querySelector('#D1')
        tbody.innerHTML = ''
        tbody.append(...data.map(d => {
            const tr = document.createElement('tr')
            tr.innerHTML = `<td>${d[1]}</td><td>${d[0]}</td>`
            return tr
        }))
    })
})

var map = L.map('map').setView([61.505, -10.09], 3);

L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
    maxZoom: 18,
    id: 'mapbox/streets-v11',
    tileSize: 512,
    zoomOffset: -1,
    accessToken: 'pk.eyJ1IjoiZmFzaGlvbmtpbGxhIiwiYSI6ImNsMmtwb2l0MjBmZHUzam84cTVobzVlbTIifQ.zL-QCXz6inlps2PqFLTXtw'
}).addTo(map);


fetch('map.php').then(res => res.json()).then(data => {
    data.forEach(ll => L.marker([ll[1], ll[0]]).addTo(map))
})