document.querySelectorAll('#global-visits tr').forEach(td => td.onclick = e => {
    document.querySelector('#state-visits').classList.remove('hidden')
    const state = e.target.closest('tr').children[1].innerText;
    fetch(`./php/stateVisits.php?state=${state}`).then(res => res.json()).then(data => {
        const tbody = document.querySelector('#state-table')
        tbody.innerHTML = ''
        tbody.append(...data.map(d => {
            const tr = document.createElement('tr')
            tr.innerHTML = `<td>${d[1]}</td><td>${d[0]}</td>`
            return tr
        }))
    })
})

var map = L.map('map').setView([48.165064, 17.145673], 3);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

fetch('./php/map.php').then(res => res.json()).then(data => {
    data.forEach(ll => L.marker([ll[1], ll[0]]).addTo(map))
})